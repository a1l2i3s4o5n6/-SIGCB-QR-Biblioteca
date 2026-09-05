# RUNBOOK.md — Procedimientos Operativos de SIGCB-QR

Runbook con los procedimientos rutinarios y de respuesta a incidentes del
Sistema SIGCB-QR. Va dirigido al equipo de operaciones.

## 1. Operaciones diarias

### 1.1 Verificación de estado

```bash
# Estado de todos los servicios
docker compose ps

# Healthcheck de la API
curl -fsS http://localhost:8080/actuator/health

# Healthcheck HTTP del frontend
curl -fsS -o /dev/null -w "%{http_code}\n" http://localhost:8000

# Hit ratio de Redis (objetivo >= 80 %)
docker exec sigcbqr-redis redis-cli INFO stats | grep -i hits
```

### 1.2 Registros

```bash
# Registros en tiempo real
docker compose logs -f

# Registros de un servicio concreto
docker compose logs api
docker compose logs frontend
```

## 2. Operaciones rutinarias

### 2.1 Reiniciar un servicio

```bash
docker compose restart api
```

### 2.2 Aplicar cambios de código (reconstruir)

```bash
git pull
docker compose up -d --build
```

### 2.3 Ejecutar la suite de pruebas

```bash
make test
```

### 2.4 Validaciones de integridad

```bash
# Valida digests de imágenes, matriz de trazabilidad y ADR
make verify

# Verifica que el diccionario de datos esté al día (requiere BD en marcha)
make docs-check
```

## 3. Incidentes

### 3.1 La API no responde en `/actuator/health`

1. Comprobar contenedores: `docker compose ps`
2. Revisar registros: `docker compose logs api`
3. Verificar conectividad con PostgreSQL y Redis
4. Si el esquema no cuadra (JPA `validate` falla), asegurar que las migraciones
   de Flyway se aplicaron: `docker compose exec api` y revisar el log de arranque.

### 3.2 Base de datos llena o colapsada

1. Monitorear espacio: consultar `pg_database_size()` en PostgreSQL.
2. Revisar que Redis absorbe la carga de catálogo (hit ratio alto).
3. Si la BD responde lento, validar índices y `ANALYZE`.

### 3.3 Caché Redis degradada

1. Revisar hit ratio: `docker exec sigcbqr-redis redis-cli INFO stats`
2. Si el hit ratio cae por debajo del 80 %, revisar política de expulsión de
   memoria y TTL configurado.

### 3.4 Contraseñas/secreto comprometidos

1. **Rotar inmediatamente** `POSTGRES_PASSWORD`, `SPRING_DATASOURCE_PASSWORD`
   y `JWT_SECRET`.
2. Actualizar el `.env` (y las variables del entorno de despliegue).
3. Reiniciar servicios: `docker compose up -d`.
4. Eliminar el secreto de versiones históricas de Git (reescribir historia si
   es necesario) y **nunca** versionar secretos reales; usar placeholders en
   `.env.example`.

### 3.5 BD con historial de Flyway desfasado (checksum mismatch)

**Síntoma:** el contenedor `api` queda en `Exited (1)` y en
`docker compose logs api` aparece:

```
Validate failed: Migrations have failed validation
Migration checksum mismatch for migration version 11
```

**Causa:** el volumen `pgdata` fue creado con una versión anterior de las
migraciones, de modo que su historial de Flyway no coincide con el árbol local.
No ocurre en un clon limpio: ahí las migraciones se aplican desde cero.

**Remedio (una sola vez por volumen; NO borra datos):**

```bash
# 1. Aplicar la migración V11 al esquema existente (es idempotente:
#    usa ADD COLUMN IF NOT EXISTS / CREATE INDEX IF NOT EXISTS)
cat backend/src/main/resources/db/migration/V11__add_student_module_fields.sql \
  | docker exec -i sigcbqr-postgres psql -U postgres -d sigcbqr -v ON_ERROR_STOP=1

# 2. Alinear el historial de Flyway con el árbol de migraciones local
#    (toma la contraseña del .env; no pide teclear secretos)
PASSWORD=$(grep '^SPRING_DATASOURCE_PASSWORD=' .env | cut -d= -f2)
docker run --rm --network sigcb-qr-biblioteca_default \
  -v "$PWD/backend/src/main/resources/db/migration":/flyway/sql:ro \
  flyway/flyway:11.7.2 \
  repair -url=jdbc:postgresql://postgres:5432/sigcbqr \
    -user=postgres -password="$PASSWORD"

# 3. Comprobar que la fila 11 quedó como 'add student module fields'
docker exec sigcbqr-postgres psql -U postgres -d sigcbqr \
  -c "SELECT installed_rank, version, description, success FROM flyway_schema_history ORDER BY installed_rank;"

# 4. Verificar que el puerto 8080 del host esté libre
#    (si otro contenedor lo ocupa, p. ej. 'new-php-1': docker stop new-php-1)
#    y levantar de nuevo
docker compose up -d --build
curl -fsS http://localhost:8080/actuator/health
```

**Nota:** si el frontend reporta `cURL error 6: Could not resolve host:
sigcbqr-api`, no es un problema de DNS, sino que el contenedor `api` está
detenido; al arrancarlo el nombre se resuelve dentro de la red de Compose.

### 3.6 BD con checksum desfasado por la remediación de credenciales (V3/V5/V7)

**Síntoma:** tras la remediación de OBS-23 (las migraciones V3/V5/V7 cambiaron
para dejar de versionar contraseñas en claro), el contenedor `api` de una BD
cuya `pgdata` se creó **antes** de ese cambio queda en `Exited (1)` con:

```
Validate failed: Migrations have failed validation
Migration checksum mismatch for migration version 7
```

Esto **no** ocurre en un clon limpio. Tampoco borra datos.

**Remedio (una sola vez por volumen):**

```bash
# 1. Alinear el historial de Flyway con el árbol de migraciones local
PASSWORD=$(grep '^SPRING_DATASOURCE_PASSWORD=' .env | cut -d= -f2)
docker run --rm --network sigcb-qr-biblioteca_default \
  -v "$PWD/backend/src/main/resources/db/migration":/flyway/sql:ro \
  flyway/flyway:11.7.2 \
  repair -url=jdbc:postgresql://postgres:5432/sigcbqr \
    -user=postgres -password="$PASSWORD"

# 2. Comprobar que no hay más desfases
docker exec sigcbqr-postgres psql -U postgres -d sigcbqr \
  -c "SELECT installed_rank, version, description, success FROM flyway_schema_history ORDER BY installed_rank;"

# 3. Levantar de nuevo
docker compose up -d --build
curl -fsS http://localhost:8080/actuator/health
```

Tras el `repair`, la siguiente arrancada vuelve a ejecutar V7 con los
placeholders del entorno (`.env`): los usuarios semilla quedan re-hasheados con
las variables `SEED_*_PASSWORD` definidas en `.env`.

## 4. Puesta en marcha / detención

```bash
# Levantar todo
make up

# Detener (conservando volúmenes)
docker compose down

# Detener y borrar volúmenes (pérdida de datos)
make clean
```

## 5. Referencias

- [`DEPLOYMENT.md`](DEPLOYMENT.md) — cómo desplegar
- [`BACKUP.md`](BACKUP.md) — plan de respaldo
- [`Makefile`](Makefile) — objetivos disponibles
