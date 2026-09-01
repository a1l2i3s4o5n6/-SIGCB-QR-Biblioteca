# ADR-0009: Revocar JWT mediante lista negra de `jti` en base de datos

- **Estado:** Aceptado
- **Fecha:** 2026-08-31
- **Decisores:** Equipo SIGCB-QR
- **Requisitos afectados:** REQ-F-003, REQ-NF-007
- **Relacionado con:** ADR-0003

## Contexto

Un JWT firmado es válido para cualquiera que lo posea hasta que expira: el
servidor no guarda estado de sesión y no tiene forma natural de invalidarlo. Con
`expiration-ms = 3600000` (una hora), «cerrar sesión» borrando la cookie deja el
token operativo durante hasta sesenta minutos para quien lo hubiera copiado. El
requisito REQ-F-003 exige que el cierre de sesión sea efectivo, no cosmético.

## Opciones consideradas

1. **No revocar; acortar la vida del token.** Reduce la ventana pero no la cierra,
   y con tokens muy cortos hace falta un mecanismo de refresco, que es más
   maquinaria de la que se pretendía evitar.
2. **Sesiones con estado en servidor.** Revocación inmediata y trivial, pero
   renuncia al JWT y a la ausencia de estado que la asignatura pide demostrar.
3. **Lista negra de identificadores de token (`jti`) en Redis.** Consulta rápida y
   expiración automática por TTL. Ata la seguridad a un componente pensado como
   caché: si Redis se vacía —reinicio, `FLUSHALL`, desalojo por memoria— los
   tokens revocados vuelven a valer, en silencio.
4. **Lista negra de `jti` en PostgreSQL.** Persistente y transaccional; cuesta una
   consulta a la base por petición autenticada.

## Decisión

Se adopta la opción 4.

- Cada token lleva un `jti` único (UUID) entre sus claims.
- `POST /api/auth/logout` inserta el `jti` y su `exp` en la tabla `jwt_blacklist`.
- `JwtTokenProvider.validateToken` consulta la lista en cada petición; un `jti`
  presente invalida el token aunque la firma y la fecha sean correctas.
- La inserción usa `INSERT … ON CONFLICT DO NOTHING`
  (`JwtBlacklistRepository.insertIfAbsent`) en lugar de «comprobar y luego
  insertar». Dos peticiones de cierre de sesión simultáneas con el mismo token
  entraban en carrera y una fallaba con violación de unicidad; delegar el
  desempate en la base elimina la ventana.
- Una tarea programada (`cleanExpiredEntries`, cada seis horas) borra las entradas
  ya expiradas: un token vencido no necesita seguir en la lista, porque la
  comprobación de `exp` ya lo rechaza.

Se elige PostgreSQL sobre Redis porque **una decisión de seguridad no debe
depender de un componente cuyo contrato es poder perder datos**. La caché de
libros puede vaciarse sin consecuencias; la lista de tokens revocados no.

## Consecuencias

### Positivas

- El cierre de sesión es efectivo de inmediato: verificado sobre el sistema en
  marcha (bloque API2 de la auditoría OWASP), la misma cookie que devolvía 200
  antes del `logout` devuelve 401 después.
- La revocación sobrevive a reinicios de la API, de Redis y de la propia base.
- La tabla es auditable: se puede saber qué se revocó y cuándo.

### Negativas

- **Una consulta a la base por cada petición autenticada.** Es el precio directo:
  se recupera parte de la latencia que la ausencia de estado pretendía ahorrar.
  Está mitigado por el índice único sobre `jti`, pero no eliminado.
- **La ausencia de estado deja de ser real.** La API es `STATELESS` en cuanto a
  sesión HTTP, pero ya no en cuanto a autorización; conviene no describirla como
  totalmente sin estado.
- La tabla crece hasta que corre la limpieza; con seis horas de intervalo el
  volumen es despreciable en este sistema, pero es un parámetro que habría que
  revisar a otra escala.
- Solo se revocan tokens **de uno en uno**: no existe «cerrar todas las sesiones»
  ni revocación masiva por usuario.

## Verificación

- `JwtBlacklistServiceTest`: cuatro casos —registro del `jti`, consulta positiva,
  consulta negativa y limpieza de entradas vencidas.
- `JwtTokenProviderTest`: emisión con `jti` y validación.
- Comprobación de extremo a extremo sobre el sistema en marcha:

```
$ curl -s -o /dev/null -w '%{http_code}\n' -b cookie.txt  http://localhost:8080/api/libros
200
$ curl -s -o /dev/null -w '%{http_code}\n' -b cookie.txt -X POST http://localhost:8080/api/auth/logout
200
$ curl -s -o /dev/null -w '%{http_code}\n' -b cookie.txt  http://localhost:8080/api/libros
401
```
