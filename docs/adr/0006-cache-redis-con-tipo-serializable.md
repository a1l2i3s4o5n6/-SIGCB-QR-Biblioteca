# ADR-0006: Cachear en Redis solo tipos con ida y vuelta demostrada

- **Estado:** Aceptado
- **Fecha:** 2026-09-01
- **Decisores:** Equipo SIGCB-QR
- **Requisitos afectados:** REQ-NF-001, REQ-R-004; observación OBS-05
- **Relacionado con:** ADR-0004 (los fallos de caché se manifestaban como 500)

## Contexto

La observación OBS-05 pedía una caché con TTL configurable para el listado de
libros. Se implementó con `@Cacheable` de Spring sobre Redis, usando
`GenericJackson2JsonRedisSerializer` (`CacheConfig`), y el método cacheado
devolvía `Page<LibroResponse>` —es decir, un `PageImpl` de Spring Data.

**Esa combinación estaba rota, y lo estuvo en producción sin que ninguna prueba lo
detectara.** El fallo se descubrió el 2026-09-01 auditando el sistema en marcha:

```
$ curl -s -b cookie-admin.txt http://localhost:8080/api/libros -o /dev/null -w '%{http_code}\n'
200          # primera petición: fallo de caché, se sirve de la base y se escribe en Redis
$ curl -s -b cookie-admin.txt http://localhost:8080/api/libros -o /dev/null -w '%{http_code}\n'
500          # segunda petición: acierto de caché, no se puede leer lo escrito
```

El registro de la API mostraba la causa:

```
org.springframework.data.redis.serializer.SerializationException: Could not read JSON:
Cannot construct instance of `org.springframework.data.domain.PageImpl`
(no Creators, like default constructor, exist)
```

`PageImpl` **se puede serializar pero no deserializar**: no tiene constructor sin
argumentos ni constructor anotado para Jackson. La escritura en caché funcionaba;
la lectura fallaba siempre. Es decir, el endpoint principal del catálogo
respondía 500 **en todo acierto de caché**, exactamente el caso que la caché
existía para acelerar. La primera petición tras cada expiración del TTL
funcionaba, lo que hacía el fallo intermitente y fácil de atribuir a otra cosa.

Ninguna prueba lo detectó porque las pruebas de `LibroService` usan Mockito y el
proxy de `@Cacheable` no interviene; y las de `LibroController` sustituyen el
servicio por un doble.

## Opciones consideradas

1. **Registrar un mixin de Jackson para `PageImpl`.** Deja intacta la firma del
   servicio, pero acopla la configuración de caché a una clase interna de Spring
   Data cuya forma serializada no es contrato estable.
2. **Cambiar el serializador a `JdkSerializationRedisSerializer`.** `PageImpl` es
   `Serializable`, así que funcionaría. A cambio, el contenido de Redis deja de
   ser inspeccionable, se ata al `serialVersionUID` de las clases y reintroduce
   los riesgos de la deserialización binaria de Java.
3. **Cachear un DTO propio con ida y vuelta demostrada.** El proyecto ya tenía
   `PageResponse<T>` —con constructor sin argumentos, *getters* y *setters*— que
   era además lo que el controlador devolvía al cliente.

## Decisión

Se adopta la opción 3, elevada a **regla general**:

> Solo se cachea un tipo cuya ida y vuelta por el serializador configurado esté
> demostrada por una prueba automatizada. Nunca se cachea un tipo de framework
> cuya forma serializada no controlamos.

En concreto:

- `LibroService.listar(Pageable)` pasa a devolver `PageResponse<LibroResponse>`.
- `LibroController` usa ese valor tal cual en la rama sin filtros, y solo convierte
  con `PageResponse.from(...)` la rama filtrada, que no se cachea.
- La clave de caché incluye ahora el **orden** además del número y el tamaño de
  página. La clave anterior, `#pageable.pageNumber + '-' + #pageable.pageSize`,
  hacía que dos peticiones con distinto `sort` compartieran entrada y una
  recibiera los datos ordenados de la otra: un segundo defecto, latente, que se
  corrige junto al primero.

## Consecuencias

### Positivas

- `GET /api/libros` responde 200 tanto en fallo como en acierto de caché.
- El valor cacheado es el mismo objeto que se serializa al cliente: desaparece una
  conversión y el riesgo de que ambas formas se desincronicen.
- El contenido de Redis es JSON legible con `redis-cli`, lo que hace inspeccionable
  la métrica de aciertos.
- Peticiones con distinto orden ya no se contaminan entre sí.

### Negativas

- `PageResponse` es ahora un tipo de frontera **y** un tipo persistido en caché:
  cambiarle un campo invalida las entradas escritas por la versión anterior.
  Mientras el TTL sea corto (300 s por defecto) el efecto se limita a ese lapso.
- La regla exige una prueba de serialización por cada nuevo tipo cacheado. Es
  trabajo adicional deliberado: es exactamente la prueba que faltaba.
- La entrada de caché crece al incluir el orden en la clave; con los tres órdenes
  que la interfaz usa, el efecto es despreciable.

## Verificación

- `CacheSerializationTest` (`backend/src/test/java/com/sigcbqr/config/`):
  - `pageResponseHaceRoundTripPorElSerializadorDeRedis` comprueba que
    `PageResponse<LibroResponse>` vuelve del serializador como `PageResponse` y
    que sus elementos conservan el tipo en lugar de volver como `Map`.
  - `pageImplNoSePuedeDeserializarYPorEsoNoSeCachea` fija la causa raíz: si
    `PageImpl` dejara de fallar, el motivo de este ADR habría cambiado.
- Comprobación manual sobre el sistema en marcha, documentada en
  `docs/mediciones/seguridad/OWASP-AUDIT.md` (bloque de disponibilidad) y en el
  apartado de defectos del informe técnico.
- Hit ratio de Redis: `make metrics`.
