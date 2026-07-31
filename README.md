# SIGCB-QR — Sistema Integral de Gestión de Biblioteca con Códigos QR

## Tercer Avance del Proyecto — App Web

Sistema de gestión bibliotecaria que automatiza los procesos de autenticación, catálogo, préstamos, reservas, multas y reportes. Aplicación web con arquitectura cliente-servidor.

## Stack Tecnológico

- **Backend:** Java 21 + Spring Boot 3.4 (API REST, JWT en cookie HttpOnly)
- **Frontend:** Laravel 13 (Bootstrap 5, Blade)
- **Base de datos:** PostgreSQL 16 + Redis 7 (caché)
- **Infraestructura:** Docker Compose (PostgreSQL, Redis, API, Frontend, pgAdmin)

## Avances del Proyecto

- **Primer avance (v0.3.0):** SRS, casos de uso, arquitectura C4
- **Segundo avance (v0.7.0):** Autenticación JWT, CRUD de usuarios y libros, préstamos y devoluciones, Docker Compose
- **Tercer avance (v0.9.0-rc):** Claims JWT estándar, errores RFC 7807 (ProblemDetail), caché Redis, stored procedures, ingeniería de requisitos (ISO/IEC/IEEE 29148), historias de usuario, matriz de trazabilidad, pruebas automatizadas (JUnit 5, JaCoCo), CI/CD (GitHub Actions), pruebas de carga (k6), reproducibilidad (Makefile, CITATION.cff, digests de imágenes)

## Estructura del Repositorio

| Ruta | Contenido |
|---|---|
| `sigcb-qr-api/` | Backend Spring Boot (controllers, services, security, entities, tests) |
| `SIGCB-QR/` | Frontend Laravel |
| `db/` | Schema, seed y stored procedures |
| `docs/` | SRS, casos de uso, historias, matriz, observaciones |
| `scripts/` | Pruebas de carga (k6), análisis de rendimiento, validación CI |
| `docker-compose.yml` | Infraestructura completa |

## Cómo Ejecutar

1. Clonar el repositorio
2. Copiar `.env.example` a `.env`
3. `make up` (o `docker compose up -d`)
4. Documentación API en `sigcb-qr-api` (OpenAPI)
