# Registros de Decisiones de Arquitectura (ADR)

Este directorio contiene los ADR del proyecto SIGCB-QR. Un ADR documenta **una**
decisión de arquitectura con consecuencias duraderas: el contexto que la forzó,
las opciones consideradas, lo que se decidió y el precio que se paga por ello.

Formato: [MADR](https://adr.github.io/madr/) simplificado.
Numeración: correlativa e inmutable. Un ADR nunca se borra ni se reescribe; si
deja de valer se marca como `Reemplazado por ADR-NNNN` y se escribe uno nuevo.

## Estados

| Estado | Significado |
|---|---|
| Propuesto | Redactado, aún sin aplicar en el código |
| Aceptado | Aplicado y vigente |
| Reemplazado | Sustituido por un ADR posterior |
| Rechazado | Se evaluó y se descartó; se conserva por su valor de contexto |

## Índice

| ADR | Título | Estado | Fecha |
|---|---|---|---|
| [0001](0001-usar-adr-para-registrar-decisiones.md) | Usar ADR para registrar las decisiones de arquitectura | Aceptado | 2026-08-28 |
| [0002](0002-separar-api-spring-boot-de-frontend-laravel.md) | Separar la API en Spring Boot del frontend en Laravel (patrón BFF) | Aceptado | 2026-08-28 |
| [0003](0003-jwt-en-cookie-httponly-y-sesion-servidor.md) | Guardar el JWT en cookie HttpOnly y en la sesión del BFF, no en `localStorage` | Aceptado | 2026-08-28 |
| [0004](0004-errores-http-con-rfc-7807.md) | Devolver los errores HTTP como `ProblemDetail` (RFC 7807) | Aceptado | 2026-08-29 |
| [0005](0005-crud-por-orm-y-agregaciones-por-procedimientos.md) | CRUD por ORM y agregaciones por procedimientos almacenados | Aceptado | 2026-08-29 |
| [0006](0006-cache-redis-con-tipo-serializable.md) | Cachear en Redis solo tipos con ida y vuelta demostrada | Aceptado | 2026-09-01 |
| [0007](0007-anclar-imagenes-por-digest-sha256.md) | Anclar las imágenes de contenedor por digest SHA256 | Aceptado | 2026-08-30 |
| [0008](0008-flyway-como-fuente-unica-del-esquema.md) | Flyway como fuente única del esquema de base de datos | Aceptado | 2026-08-30 |
| [0009](0009-revocacion-de-jwt-por-lista-negra-de-jti.md) | Revocar JWT mediante lista negra de `jti` en base de datos | Aceptado | 2026-08-31 |

## Cómo añadir un ADR

1. Copiar `plantilla.md` a `NNNN-titulo-en-kebab-case.md` con el siguiente número libre.
2. Rellenarlo. La sección de consecuencias negativas no es opcional: si una
   decisión no tiene coste, probablemente no era una decisión.
3. Añadir la fila al índice de este archivo.
