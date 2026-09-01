# ADR-0003: Guardar el JWT en cookie HttpOnly y en la sesión del BFF, no en `localStorage`

- **Estado:** Aceptado
- **Fecha:** 2026-08-28 (aplicado en el código desde 2026-05)
- **Decisores:** Equipo SIGCB-QR
- **Requisitos afectados:** REQ-F-001, REQ-F-003, REQ-NF-002

## Contexto

La API emite un JWT firmado (HS384) tras un inicio de sesión correcto. Hay que
decidir dónde lo custodia el cliente. La elección determina qué clase de ataque
basta para robar la sesión de un usuario.

El sistema tiene dos clientes distintos y el almacenamiento debe resolverse para
ambos:

- el **navegador**, que habla con Laravel;
- el **BFF de Laravel**, que habla con la API (ver ADR-0002).

## Opciones consideradas

1. **`localStorage` en el navegador + cabecera `Authorization: Bearer`.** Es la
   opción más común en tutoriales. Cualquier XSS —una dependencia comprometida,
   un campo sin escapar— basta para leer el token y usarlo desde fuera hasta que
   expire.
2. **Cookie sin `HttpOnly` + `Bearer`.** Reúne las desventajas de ambas: legible
   por JavaScript *y* enviada automáticamente.
3. **Cookie `HttpOnly` + `SameSite=Strict`, y el token también en la sesión del
   servidor de Laravel.** El navegador nunca puede leer el token; el BFF lo
   reenvía a la API en cada petición.

## Decisión

Se adopta la opción 3, con este reparto:

- La API emite `access_token` como cookie **`HttpOnly`, `SameSite=Strict`,
  `Path=/`, `Max-Age=3600`**, y `Secure` gobernado por `app.jwt.secure-cookie`
  (`false` en desarrollo por HTTP local, `true` en producción vía
  `application-prod.yml`).
- El BFF de Laravel guarda además el token en la **sesión de servidor**
  (`session('api_token')`) y lo reenvía a la API como `Authorization: Bearer` en
  cada llamada de `ApiClient`. La cookie de sesión de Laravel también es
  `HttpOnly`.
- El filtro `JwtAuthenticationFilter` acepta el token por cualquiera de las dos
  vías: `JwtTokenProvider.extractTokenFromRequest` prueba primero la cabecera
  `Authorization` y, si no hay, cae a la cookie.

`SameSite=Strict` se elige en lugar de `Lax` porque la aplicación no necesita que
la sesión sobreviva a la navegación desde un sitio externo, y ese ajuste, junto
con el CORS restringido de ADR-0002, cubre el CSRF que introduce el uso de
cookies —la API es además `STATELESS` y no confía en la sesión del contenedor.

## Consecuencias

### Positivas

- Un XSS en la interfaz **no puede leer el JWT**: no está en `localStorage` ni es
  accesible por `document.cookie`. Como el token vive en la sesión de PHP, un XSS
  tampoco lo obtiene desde el lado del navegador.
- El token no aparece en el historial, en los `Referer` ni en los registros de
  proxys intermedios, como sí ocurriría en la URL.
- La caducidad la impone el navegador (`Max-Age`) además del propio `exp` del
  token.

### Negativas

- **El uso de cookies reintroduce la superficie de CSRF.** Se contrarresta con
  `SameSite=Strict`, CORS por lista blanca y el hecho de que la API no mantiene
  estado de sesión; queda registrado aquí como riesgo asumido y no como problema
  resuelto por completo.
- **Dos rutas de autenticación que mantener** (cabecera y cookie) en
  `extractTokenFromRequest`. Es deuda deliberada: la cookie sirve al acceso
  directo a la API y a Swagger, la cabecera sirve al BFF.
- **`Secure=false` en desarrollo.** Es correcto para HTTP local, pero significa
  que el ajuste de producción depende de que el perfil `prod` esté activo. Si
  alguien despliega con el perfil por defecto, la cookie viaja sin `Secure`.
- Un JWT en cookie sigue siendo un *bearer token*: quien lo obtiene, lo usa. La
  revocación no es gratis; se resuelve en ADR-0009.

## Verificación

- `JwtTokenProviderTest` comprueba la emisión y los claims (`iss`, `aud`, `nbf`,
  `iat`, `jti`).
- `JwtAuthenticationFilterTest` cubre las rutas sin token y con token inválido.
- Auditoría OWASP, bloque API2 (`docs/mediciones/seguridad/OWASP-AUDIT.md`): la
  cabecera `Set-Cookie` de `POST /api/auth/login` observada en el sistema en
  marcha contiene `HttpOnly` y `SameSite=Strict`.
