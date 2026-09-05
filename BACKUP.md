# BACKUP.md — Estrategia de Respaldo de SIGCB-QR

Definición de la estrategia de copias de seguridad para el sistema SIGCB-QR.
Incluye las políticas de retención, los procedimientos de copia y de restauración.

## Alcance

Los datos a respaldar son:

1. **Base de datos PostgreSQL 16** — datos de negocio (usuarios, libros,
   préstamos, reservas, multas, auditoría).
2. **Configuración** — `.env`, `docker-compose.yml`, migraciones de Flyway.
3. **Repositorio** — el código fuente y la documentación (se respaldan con Git).

Redis **no** requiere respaldo: es una caché desechable que se reconstruye
automáticamente desde la base de datos (patrón cache-aside).

## Política de retención

| Tipo de copia | Frecuencia | Retención |
|---|---|---|
| Copia lógica PostgreSQL (`pg_dump`) | Diaria | 30 días (rotación) |
| Volcado base (`pg_dumpall`) | Semanal | 12 semanas |
| Snapshot del repositorio + `.env` | En cada entrega/release | Indefinido |
| Tarjeta de liquidación | Mensual (mes de exámenes) | 1 ciclo académico |

## Procedimiento de copia

### Respaldo de PostgreSQL (copia lógica)

```bash
# Respaldo de todo el esquema (datos + estructura + procedimientos)
docker exec sigcbqr-postgres pg_dump -U postgres -Fc sigcbqr > backup_$(date +%Y%m%d).dump

# Respaldo en texto plano (portable)
docker exec sigcbqr-postgres pg_dump -U postgres -Fp sigcbqr > backup_$(date +%Y%m%d).sql
```

Guardar los archivos en un sistema de almacenamiento redundante (nube, disco
externo) junto con la fecha y el entorno en un manifiesto.

### Respaldo de configuración

```bash
# Guardar variables de entorno (sanitizadas, sin secretos reales)
cp .env `.env.backup.$(date +%Y%m%d)`   # reemplazar secretos por placeholders
```

**Nunca** subir `.env` con secretos reales al repositorio.

## Procedimiento de restauración

### Restaurar la base de datos desde `pg_dump`

```bash
# 1. Detener los servicios que escriben
docker compose stop api

# 2. Restaurar (volcado binario)
cat backup_YYYYMMDD.dump | docker exec -i sigcbqr-postgres pg_restore -U postgres -d sigcbqr --clean --if-exists

# 3. Volver a levantar
docker compose start api
```

### Verificación del respaldo

Tras cada copia, verificar la integridad:

```bash
# Listar el contenido del volcado (sin restaurar)
docker exec sigcbqr-postgres pg_restore -l /backup.dump | head -20
```

## Plan de pruebas de restauración

Se recomienda ejecutar una **prueba de restauración mensual** en un entorno
aislado para garantizar que los respaldos son recuperables:

1. Levantar un PostgreSQL temporal con la misma imagen digest anclada.
2. Restaurar el último volcado.
3. Verificar integridad: `SELECT count(*) FROM usuarios;` y comparar con el
   manifiesto.

### Estado de ejecución

> **Este plan no se ha ejecutado nunca.** A fecha de 3 de septiembre de 2026 no
> se ha realizado ninguna prueba de restauración, ni en entorno aislado ni en
> ningún otro. En consecuencia, **no puede afirmarse que los respaldos de este
> sistema sean recuperables**: el procedimiento está escrito y es plausible,
> pero no está verificado.
>
> Se declara así en lugar de omitirlo, porque un respaldo que nunca se ha
> restaurado es una intención de respaldo, no un respaldo. La primera ejecución
> del plan debe registrarse aquí con su fecha y su resultado.

| Fecha | Entorno | Resultado | Observaciones |
|---|---|---|---|
| — | — | **Sin ejecutar** | Ninguna prueba realizada hasta la fecha |

## Referencias

- [`DEPLOYMENT.md`](DEPLOYMENT.md)
- [`RUNBOOK.md`](RUNBOOK.md)
- [`docker-compose.yml`](docker-compose.yml)
- [`ETHICS.md`](ETHICS.md) — tratamiento de datos (los datos son sintéticos)
