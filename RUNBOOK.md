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
