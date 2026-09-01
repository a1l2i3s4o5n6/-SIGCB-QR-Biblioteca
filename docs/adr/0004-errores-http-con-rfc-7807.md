# ADR-0004: Devolver los errores HTTP como `ProblemDetail` (RFC 7807)

- **Estado:** Aceptado
- **Fecha:** 2026-08-29 (aplicado en el código desde 2026-07)
- **Decisores:** Equipo SIGCB-QR
- **Requisitos afectados:** REQ-NF-003; observación OBS-04

## Contexto

Hasta la Segunda Entrega, los errores de la API se devolvían con una forma propia
(`{status, message, data}`), la misma que las respuestas correctas. Eso obligaba a
cada cliente a inspeccionar el cuerpo para saber si había fallado algo, y no
distinguía la clase de error de su descripción legible. La observación OBS-04 lo
señaló explícitamente.

## Opciones consideradas

1. **Mantener el sobre propio `ApiResponse` también para los errores.** Cero
   trabajo, ningún estándar; el cliente sigue adivinando.
2. **Definir un formato de error propio pero distinto del de éxito.** Resuelve la
   ambigüedad, pero inventa un vocabulario que nadie más conoce.
3. **RFC 7807 / RFC 9457 `application/problem+json`**, con el soporte nativo de
   `ProblemDetail` que Spring Framework 6 ya incluye.

## Decisión

Se adopta la opción 3. `GlobalExceptionHandler` traduce cada excepción de dominio
a un `ProblemDetail` con `type`, `title`, `status`, `detail` e `instance`, servido
como `application/problem+json`.

Correspondencia entre excepción y respuesta:

| Excepción | Estado | `type` |
|---|---|---|
| `ResourceNotFoundException` | 404 | `.../errors/not-found` |
| `BadRequestException`, validación | 400 | `.../errors/bad-request` |
| Autenticación ausente o inválida | 401 | `.../errors/unauthorized` |
| `AuthorizationDeniedException` | 403 | `.../errors/forbidden` |
| `TooManyRequestsException` | 429 | `.../errors/too-many-requests` |
| No controlada | 500 | `.../errors/internal-error` |

El campo `instance` lleva la ruta solicitada y se añade una marca temporal como
extensión, que RFC 7807 permite.

## Consecuencias

### Positivas

- El cliente distingue la **clase** de error (`type`, estable y enlazable) de su
  **descripción** (`detail`, redactada para humanos y traducible) sin analizar
  texto.
- `Content-Type: application/problem+json` permite decidir el tratamiento antes de
  leer el cuerpo.
- Es el formato que Spring produce de serie: menos código propio que mantener.

### Negativas

- **Las respuestas correctas siguen usando el sobre `ApiResponse`**, de modo que
  la API tiene dos formas de cuerpo. Es una inconsistencia real y consciente:
  unificar también el camino feliz habría obligado a tocar todos los
  controladores y todos los consumidores del BFF a mitad del semestre.
- Las URI de `type` apuntan a `https://api.sigcbqr.com/errors/…`, un dominio que
  **no está publicado**. RFC 7807 lo admite (la URI identifica, no tiene por qué
  resolver), pero conviene no presentarlo como documentación existente.
- `detail` puede filtrar información interna si una excepción no controlada llega
  al manejador genérico. Ocurrió de hecho con el defecto de caché descrito en
  ADR-0006, donde el mensaje de Jackson se expuso al cliente.

## Verificación

- `GlobalExceptionHandlerTest` comprueba que 404, 400 y 500 se emiten como
  `ProblemDetail`.
- Auditoría OWASP, bloque API8: las respuestas 401 y 403 observadas en el sistema
  en marcha llevan `Content-Type: application/problem+json` y los cinco campos de
  la RFC.
