# Trazado de credenciales y secretos en el repositorio

> Inventario de credenciales y secretos **versionados** del repositorio, con su
> **remediación ejecutada** (OBS-23). Tras la limpieza, no queda ninguna
> credencial de despliegue en claro en el árbol de trabajo: el único lugar donde
> figuran los valores de desarrollo es `.env.example` (marcado SOLO-dev) y los
> inventarios que los documentan por diseño.

## Resumen

| Nivel de riesgo | Qué | Dónde | Estado |
|-----------------|-----|-------|--------|
| ~~Alto~~ | ~~Contraseñas semilla en claro (`admin123` / `biblio123` / `estudiante123`)~~ | ~~Migraciones Flyway `V3`, `V5`, `V7`~~ | **Remediado**: V7 usa placeholders de Flyway alimentados por entorno (`SEED_*_PASSWORD`); V3/V5 sin referencias |
| ~~Alto~~ | ~~Secreto JWT de prueba hardcodeado (base64)~~ | ~~`backend/src/test/resources/application.yml`~~ | **Remediado**: `${TEST_JWT_SECRET}` sin valor por defecto; el arnés lo genera por corrida |
| ~~Medio~~ | ~~Credenciales de BD de CI en claro (`test123`)~~ | ~~`.github/workflows/ci.yml`~~ | **Remediado**: CI ejecuta `scripts/run-tests.sh` (contraseñas efímeras por corrida) |
| ~~Medio~~ | ~~Defaults débiles de Docker (`postgres`)~~ | `docker-compose.yml` | **Remediado**: `:?` para `POSTGRES_PASSWORD`, `SPRING_DATASOURCE_PASSWORD` y `SEED_*` |
| ~~Medio~~ | ~~Credencial de admin en claro en la colección de API~~ | `SIGCB-QR.postman_collection.json` | **Remediado**: variables `{{seedAdminEmail}}`/`{{seedAdminPassword}}` |
| Bajo | Credenciales semilla de desarrollo documentadas | `.env.example` (SOLO-dev), `SECRETOS.md`, `OBSERVACIONES.md`, historias, `COBERTURA.md`, informe LaTeX | **Reducido**: solo `.env.example` + inventarios; README sin tabla de credenciales |
| — | Riesgo residual: histórico de Git | Historia de los commits | **Documentado** (no reescrito a propósito) |

> **Aclaración vigente:** la contraseña `Doctora2025` **no está versionada**.
> El `application.yml` principal usa el placeholder
> `${SPRING_DATASOURCE_PASSWORD}`. `Doctora2025` solo existe en el `.env` local,
> que está en `.gitignore` y no se rastrea. No constituye un secreto expuesto en el
> repositorio, pero *sí* debería rotarse al no poder garantizar cómo se distribuye.

---

## Detalle por archivo

### 1. Migraciones Flyway — contraseñas semilla (remediado)

| Archivo | Antes | Ahora |
|---------|-------|-------|
| `backend/src/main/resources/db/migration/V3__datos_semilla.sql` | Comentario: «(password: admin123 / biblio123 / estudiante123)» | Comentario genérico; los hashes de la INSERT quedan como valores intermedios |
| `backend/src/main/resources/db/migration/V5__corregir_contraseñas.sql` | Comentarios con `admin123 / biblio123 / estudiante123` | Comentarios sin contraseñas; UPDATE intermedios intactos |
| `backend/src/main/resources/db/migration/V7__hashes_unicos_seed.sql` | `crypt('admin123', ...)` en claro (11-13) | `crypt('${seed_admin_password}', ...)` con guarda `<> ''` |

Las contraseñas efectivas llegan por los placeholders de Flyway
(`spring.flyway.placeholders` en `application.yml`), resueltos desde
`SEED_ADMIN_PASSWORD` / `SEED_BIBLIO_PASSWORD` / `SEED_STUDENT_PASSWORD`.
Valores:
- **Desarrollo**: `.env.example` (marcado SOLO-dev) y `docker-compose.yml`
  los pasa con `:?`.
- **Pruebas (CI)**: `scripts/run-tests.sh` genera valores aleatorios por
  corrida.
- **Producción**: los valores quedan bajo control del operador; los usuarios
  semilla se **desactivan** igualmente por `db/migration-prod/V13`.

> **Checksum de Flyway.** Al editar V3/V5/V7, los volúmenes existentes
> tendrán un `Migration checksum mismatch` y requieren una única ejecución de
> `flyway repair` (procedimiento en `RUNBOOK.md §3.5`). Un clon nuevo aplica
> las migraciones sin fricción.

### 2. Secreto JWT de prueba (remediado)

- `backend/src/test/resources/application.yml`: el literal base64 se sustituyó
  por `${TEST_JWT_SECRET}` **sin valor por defecto**.
- `backend/src/test/java/com/sigcbqr/security/JwtTokenProviderTest.java`:
  lee `TEST_JWT_SECRET` del entorno (falla con mensaje claro si falta).
- `scripts/run-tests.sh` lo genera aleatorio por corrida, igual que `JWT_SECRET`.

### 3. CI — credenciales de BD (remediado)

- `.github/workflows/ci.yml`: el trabajo `build-and-test` ya no usa *service
  containers* con `POSTGRES_PASSWORD: test123`: ejecuta
  `bash scripts/run-tests.sh`, que levanta PostgreSQL/Redis de prueba y genera
  contraseñas efímeras por corrida. No hay secretos que almacenar (funciona
  también en *pull requests* desde forks).
- Añadido el trabajo **`secret-scan`** con gitleaks (config en `.gitleaks.toml`):
  cualquier secreto nuevo en un commit posterior rompe la compilación.

### 4. Docker Compose — defaults débiles (remediado)

`docker-compose.yml`:
- `POSTGRES_PASSWORD: ${POSTGRES_PASSWORD:?...}` (antes `:-postgres`).
- `SPRING_DATASOURCE_PASSWORD: ${SPRING_DATASOURCE_PASSWORD:?...}` (antes `:-postgres`).
- `SEED_ADMIN_PASSWORD/BIBLIO/STUDENT: '${...:?...}'` (nuevos, obligatorios).
- `JWT_SECRET` y `PGADMIN_PASSWORD` ya eran obligatorios (`:?`).

### 5. Colección Postman (remediado)

- `SIGCB-QR.postman_collection.json`: el body de login usa las variables
  `{{seedAdminEmail}}` / `{{seedAdminPassword}}`, declaradas con valor de
  desarrollo marcado SOLO-dev. Un despliegue real debe sobrescribirlas.

### 6. Documentación y seeders (reducido)

- `README.md`: eliminada la tabla de credenciales; se remite a `.env.example` y
  a este documento.
- `frontend/database/seeders/DatabaseSeeder.php`: vaciado a propósito — el BFF
  no habla con la base de datos (ADR-0002); los usuarios se crean en el backend
  vía Flyway.
- Quedan referencias *descriptivas* a los valores de desarrollo (sin que sean
  configuración efectiva) en: este inventario, `OBSERVACIONES.md`,
  `docs/requisitos/historias/HU-01-autenticacion.md`,
  `docs/informe/rediseno-dashboard.md`, `docs/mediciones/cobertura/COBERTURA.md`
  y `docs/INFORME_TECNICO_PFC_LATEX/INFORME_TECNICO_PFC.tex`. Están permitidas
  en `.gitleaks.toml` por ser documentación del propio trazado.

### 7. Sin incidencias (por completitud)

- `backend/src/main/resources/application.yml`: placeholders de entorno, sin
  secretos (incluidos los nuevos `spring.flyway.placeholders` con default vacío).
- `Makefile`, `scripts/k6-load-test.js`, `scripts/owasp-audit.sh`: credenciales
  vía entorno, sin secretos reales.
- `frontend/.env.example`, `frontend/.env.production.example`: sin secretos.

---

## Riesgo residual: histórico de Git

Los secretos eliminados del árbol de trabajo **siguen en la historia de los
commits** (los archivos antiguos `V3`/`V5`/`V7`, `ci.yml`, `application.yml` de
test y la colección Postman los contenían). Borrarlos del histórico exigiría
reescribir **toda** la historia con `filter-repo`/`filter-branch`, lo que:

- cambia los 123 SHA de los commits,
- invalida los 22 hashes citados como verificación en `OBSERVACIONES.md` y la
  documentación, y
- obliga a un `push --force` coordinado (el remoto es público).

**Decisión:** se conserva el histórico y el riesgo se declara. Si la institución
lo exigiera, la reescritura es un procedimiento aparte y posterior, congelando
primero la entrega evaluada. El CI (gitleaks) evita que se añadan **nuevos**
secretos a partir de este punto.

---

## Plan de remediación (ejecutado)

| # | Acción | Estado |
|---|--------|--------|
| 1 | Migraciones semilla desde variables de entorno (placeholders de Flyway) + V3/V5 sin referencias | **Hecho** |
| 2 | JWT de prueba a entorno (`TEST_JWT_SECRET`) | **Hecho** |
| 3 | CI sin `test123`: arnés autocontenido `scripts/run-tests.sh` | **Hecho** |
| 4 | Docker Compose sin defaults (`:?`, igual que `JWT_SECRET`) | **Hecho** |
| 5 | Postman parametrizado con variables de colección | **Hecho** |
| 6 | Escaneo automático: gitleaks en CI (`secret-scan`) | **Hecho** |

## Estado

| Código | Estado | Ejecutado en |
|--------|--------|--------------|
| Trazado de secretos versionados | Hecho | Este documento |
| Limpieza real (rotación + entorno + CI) | **Hecho** | CHANGELOG «No publicada» (remediación OBS-23); commit `da4df7a` (`fix(seguridad)`) |
| Riesgo residual (histórico de Git) | Documentado (no reescrito) | Sección «Riesgo residual» de este documento |