# Changelog de Requisitos — SIGCB-QR

> Formato: [Keep a Changelog](https://keepachangelog.com/) adaptado a requisitos.

## [v0.9.0-rc] — 2026-07-30

### Added
- REQ-NF-001: Rendimiento de listado de libros con p95 ≤ 200ms
- REQ-NF-005: Cobertura de pruebas ≥ 30% (JaCoCo)
- REQ-F-008: Reporte de préstamos diarios
- REQ-F-006: Préstamo de ejemplares (stored procedure)
- REQ-F-007: Devolución de préstamo con generación de multa (stored procedure)
- Procedure catalog: `docs/basedatos/CATALOGO-SP.md`

### Modified
- REQ-F-001: Claims JWT ampliados (iss, aud, nbf, iat agregados)
- REQ-F-001: Cookie JWT ahora usa Secure desde configuración externa
- REQ-F-003: Logout ahora usa blacklist Redis vía stored procedure
- REQ-NF-002: Endurecido con SameSite=Strict + Secure configurable
- Error handling: migrado de ApiResponse a RFC 7807 ProblemDetails

## [v0.7.0] — 2026-07-14 (Entrega 1B)

### Added
- REQ-F-001: Autenticación stateless JWT
- REQ-F-002: Registro de usuarios
- REQ-F-003: Cierre de sesión con JTI blacklist
- REQ-F-004: CRUD de usuarios (Admin)
- REQ-F-005: CRUD de material bibliográfico
- REQ-NF-002: Seguridad de autenticación
- REQ-NF-003: Protección contra inyección SQL
- REQ-NF-004: Disponibilidad mediante Docker Compose

## [v0.3.0] — 2026-06-04 (Entrega 1A)

### Added
- SRS inicial con requisitos funcionales y no funcionales base
- Casos de uso y arquitectura C4 nivel 1 y 2
