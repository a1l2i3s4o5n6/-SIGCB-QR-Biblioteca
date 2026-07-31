# Changelog de Requisitos — SIGCB-QR

> Formato: [Keep a Changelog](https://keepachangelog.com/) adaptado a requisitos.

## [v0.9.0-rc] — 2026-07-30

### Added
- REQ-F-008: Renovacion de prestamo
- REQ-F-009: Reporte de prestamos diarios
- REQ-F-010: Dashboard de estadisticas
- REQ-F-011: Gestion de reservas
- REQ-NF-005: Cobertura de pruebas >= 30% (JaCoCo)
- REQ-NF-006: Control de acceso por roles (RBAC)
- REQ-NF-007: Cache Redis para blacklist JWT
- REQ-R-001: Tiempo de respuesta de autenticacion (p95 <= 500ms)
- REQ-R-002: Tiempo de respuesta de CRUD (p95 <= 300ms)
- REQ-R-003: Tiempo de respuesta de stored procedures (< 1s)
- REQ-R-004: Hit ratio de cache Redis >= 80%
- HU-06: Prestamo de ejemplares
- HU-07: Devolucion de prestamo
- HU-08: Renovacion de prestamo
- HU-09: Reportes bibliotecarios
- HU-10: Dashboard de estadisticas
- CU-03: Prestamo de ejemplares
- CU-04: Devolucion de prestamo
- CU-05: Generacion de reportes
- SRS estructurado completo segun ISO/IEC/IEEE 29148:2018
- Matriz de trazabilidad con 23 filas
- SRS.pdf generado

### Modified
- REQ-F-001: Claims JWT ampliados (iss, aud, nbf, iat agregados)
- REQ-F-001: Cookie JWT ahora usa Secure desde configuracion externa
- REQ-F-003: Logout ahora usa blacklist JWT
- REQ-F-005: Cache Redis agregado con @Cacheable
- REQ-F-006: Migrado a stored procedure (sp_crear_prestamo)
- REQ-F-007: Migrado a stored procedure (sp_devolver_prestamo)
- REQ-NF-002: Endurecido con SameSite=Strict + Secure configurable
- Error handling: migrado de ApiResponse a RFC 7807 ProblemDetails

## [v0.7.0] — 2026-07-14 (Entrega 1B)

### Added
- REQ-F-001: Autenticacion stateless JWT
- REQ-F-002: Registro de usuarios
- REQ-F-003: Cierre de sesion con JTI blacklist
- REQ-F-004: CRUD de usuarios (Admin)
- REQ-F-005: CRUD de material bibliografico
- REQ-F-006: Prestamo de ejemplares (via JPA)
- REQ-F-007: Devolucion de prestamo (via JPA)
- REQ-NF-001: Rendimiento de listado de libros
- REQ-NF-002: Seguridad de autenticacion
- REQ-NF-003: Proteccion contra inyeccion SQL
- REQ-NF-004: Disponibilidad mediante Docker Compose

## [v0.3.0] — 2026-06-04 (Entrega 1A)

### Added
- SRS inicial con requisitos funcionales y no funcionales base
- Casos de uso y arquitectura C4 nivel 1 y 2
