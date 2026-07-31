# Bitácora de Observaciones — SIGCB-QR

> Archivo de trazabilidad de observaciones recibidas del docente en las Entregas 1A y 1B,
> conforme al requisito **Bloque 0** de la Tercera Entrega (v0.9.0-rc).

## Resumen

| Indicador | Valor |
|-----------|-------|
| Total observaciones | 7 |
| Resueltas | 7 |
| No resueltas | 0 |
| % Resuelto | 100 % |

## Observaciones

| Código | Fuente | Criterio | Observación | Decisión | Commit(es) |
|--------|--------|----------|-------------|----------|------------|
| OBS-01 | Entrega 1A | SRS | El documento SRS no sigue la plantilla ISO/IEC/IEEE 29148:2018. Faltan secciones de rationale, trazabilidad y métodos de verificación. | Implementado: SRS completo en docs/requisitos/SRS.md con todas las secciones obligatorias, identificadores únicos, rationale, trazabilidad y criterios de verificación. | |
| OBS-02 | Entrega 1B | Auth JWT | El JWT no incluye el claim `aud` (audience) para restringir el público objetivo del token. | Aplicada: se agregaron los claims `iss`, `aud`, `nbf` e `iat` al JWT firmado. | |
| OBS-03 | Entrega 1B | Auth Cookie | La cookie JWT tiene `Secure=false` en desarrollo; debe obtenerse de configuración externa. | Aplicada: `Secure` ahora se configura desde `app.jwt.secure-cookie` en application.yml. | |
| OBS-04 | Entrega 1B | API Errors | Los errores HTTP no siguen RFC 7807 (ProblemDetails). No se distingue entre type, title, status, detail e instance. | Aplicada: GlobalExceptionHandler ahora retorna `ProblemDetail` con los campos type, title, status, detail e instance. | |
| OBS-05 | Entrega 1B | Cache | No hay cache Redis implementado. El endpoint de listado de libros no tiene TTL configurable externamente ni métrica de hit ratio. | Aplicada: Se integró Redis con TTL desde `app.cache.default-ttl-seconds`. Se agregó métrica de hit ratio en docs/mediciones/. | |
| OBS-06 | Entrega 1B | Data Access | Las consultas de reportes y agregaciones se realizan vía JPQL en lugar de procedimientos almacenados. No hay separación CRUD-ORM vs SP. | Aplicada: Se migraron todas las consultas no elementales a stored procedures en `db/procs/`. Catálogo en docs/basedatos/CATALOGO-SP.md. | |
| OBS-07 | Entrega 1B | Docker | Las imágenes Docker usan etiquetas variables (`latest`, `16-alpine`) en lugar de digest SHA256, lo que rompe la reproducibilidad. | Aplicada: Todas las imágenes fijadas por digest SHA256 en docker-compose.yml. | |

## Historial de cambios

| Fecha | Autor | Cambio |
|-------|-------|--------|
| 2026-07-30 | Equipo SIGCB-QR | Creación inicial del archivo con 7 observaciones identificadas del análisis técnico. Pendiente de completar con feedback formal del docente. |
