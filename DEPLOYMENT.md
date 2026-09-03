# DEPLOYMENT.md — Despliegue de SIGCB-QR

Este documento describe los pasos para desplegar el Sistema Integral de Gestión
Bibliotecaria con Códigos QR (SIGCB-QR) en un entorno de producción.

## Requisitos previos

- Docker 29.5.2 o superior, con el plugin Docker Compose.
- Imágenes de contenedor ancladas por digest SHA256 (ver ADR-0007).
- Variables de entorno configuradas (ver `.env.example`).

## Arquitectura de despliegue

```
Navegador ──HTTPS──> Frontend (BFF Laravel:8000) ──HTTP──> API (Spring Boot:8080) ──> PostgreSQL 16
                                                                    │
                                                                    └──> Redis 7 (caché)
```

Los cinco servicios se orquestan a través de `docker-compose.yml`:
`postgres`, `redis`, `pgadmin` (solo útil en desarrollo), `api` y `frontend`.

## Pasos para el despliegue

### 1. Clonar el repositorio y configurar variables

```bash
git clone <url-del-repositorio>
cd SIGCB-QR-Biblioteca

# Configurar variables de entorno (NUNCA versionar secretos reales)
cp .env.example .env
# editar .env con los valores de producción
```

Variables críticas de producción:

| Variable | Descripción |
|---|---|
| `POSTGRES_PASSWORD` | Contraseña del usuario `postgres` de la base de datos |
| `SPRING_DATASOURCE_PASSWORD` | La misma contraseña usada por la API |
| `JWT_SECRET` | Secreto de firma de tokens JWT (generar aleatorio, 64+ bytes) |
| `JWT_SECURE_COOKIE` | `true` para forzar cookies solo por HTTPS |
| `CORS_ALLOWED_ORIGINS` | Orígenes permitidos (restringir a los dominios reales) |

### 2. Construir y levantar

```bash
docker compose up -d --build
```

### 3. Verificar el estado de salud

```bash
# Healthcheck de la API
curl http://<host>:8080/actuator/health

# Estado de los contenedores
docker compose ps
```

La API tarda aproximadamente 60 segundos en arrancar porque aplica las
migraciones de Flyway y valida el esquema (JPA `ddl-auto: validate`).

### 4. Verificar el frontend

```bash
curl -I http://<host>:8000
```

## Variables de producción recomendadas

| Variable | Valor recomendado |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `JWT_SECURE_COOKIE` | `true` |
| `APP_URL` | URL pública del frontend |

## Consideraciones de seguridad

- **Nunca** exponer `pgadmin` en producción.
- Restringir `CORS_ALLOWED_ORIGINS` a los dominios reales.
- Servir el frontend detrás de un proxy TLS (Nginx/Caddy) para HTTPS.
- Rotar el `JWT_SECRET` periódicamente.
- Quitar del repositorio cualquier credencial de desarrollo (ver `RUNBOOK.md`).

## Compatibilidad

- **API (Spring Boot):** requiere Java 21. Usa las migraciones de Flyway
  versionadas en `backend/src/main/resources/db/migration`.
- **Frontend (Laravel):** requiere PHP 8.3. Compila assets con Vite/Blade.
- **Base de datos:** PostgreSQL 16 con los procedimientos de `db/procs/` instalados.
- **Caché:** Redis 7.

## Referencias

- [`docker-compose.yml`](docker-compose.yml) — orquestación de servicios
- [`Makefile`](Makefile) — objetivos `up`, `down`, `test`, `verify`, `audit`
- [`RUNBOOK.md`](RUNBOOK.md) — procedimientos operativos
- [`BACKUP.md`](BACKUP.md) — estrategia de respaldo
- [`docs/adr/0007-...`](docs/adr/) — anclaje por digest de imágenes
