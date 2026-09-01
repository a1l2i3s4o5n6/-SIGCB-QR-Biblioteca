# SIGCB-QR — Sistema Integral de Gestión de Biblioteca con Códigos QR

Sistema de gestión bibliotecaria que automatiza la autenticación, catálogo, préstamos, reservas, multas, sanciones, notificaciones y auditoría. Aplicación web con arquitectura cliente-servidor.

## Stack

- **Backend:** Java 21 + Spring Boot 3.4 (API REST, JWT, PostgreSQL, Redis, Flyway)
- **Frontend:** Laravel 13 + Vite (Blade, Alpine.js, Bootstrap 5)
- **Base de datos:** PostgreSQL 16 + Redis 7
- **Infraestructura:** Docker Compose (PostgreSQL, Redis, API, Frontend, pgAdmin)

## Estructura del Repositorio

| Ruta | Contenido |
|---|---|
| `backend/` | API Spring Boot (código, migraciones Flyway, tests JUnit) |
| `frontend/` | Aplicación Laravel |
| `db/` | Schema, seed y stored procedures |
| `docs/` | Documentación (SRS, casos de uso, matriz de trazabilidad, informe) |
| `docker-compose.yml` | Infraestructura completa |

## Requisitos

Solo **Docker** con el plugin **Docker Compose**.

## Cómo Ejecutar

```bash
git clone <url-del-repositorio>
cd SIGCB-QR-Biblioteca

# Opcional: variables de entorno (docker compose trae valores por defecto)
cp .env.example .env

# Levantar todos los servicios (la primera vez construye las imágenes)
make up
# o equivalentemente:
docker compose up -d --build

# La API tarda ~60 segundos en arrancar
curl http://localhost:8080/actuator/health   # → {"status":"UP",...}
```

## Acceso

| Servicio | URL |
|---|---|
| **Aplicación web (frontend)** | http://localhost:8000 |
| **API REST** | http://localhost:8080 |
| **Swagger UI** | http://localhost:8080/swagger-ui.html |
| **pgAdmin** | http://localhost:5050 (admin@sigcbqr.com / admin123) |

## Credenciales de la aplicación

Los usuarios se crean automáticamente con las migraciones Flyway.

| Rol | Email | Contraseña |
|---|---|---|
| **ADMIN** | admin@biblioteca.com | admin123 |
| **BIBLIOTECARIO** | biblio@biblioteca.com | biblio123 |
| **ESTUDIANTE** | carlos.garcia@estudiante.com | estudiante123 |

Otros estudiantes: `ana.martinez@estudiante.com`, `pedro.ramirez@estudiante.com`, `laura.sanchez@estudiante.com`.

> El token de sesión expira a la 1 hora; al agotarse hay que volver a iniciar sesión.

## Comandos útiles

```bash
make up       # construir y levantar los servicios
make down     # detener los contenedores
make logs     # logs en vivo
make clean    # detener, borrar volúmenes e imágenes
```