# Trazado de credenciales y secretos en el repositorio

> Inventario de credenciales y secretos **versionados** en el repositorio, junto
> con el plan de remediación. Este documento **no ejecuta** la limpieza: solo
> marca para revisión, tal como se acordó al resolver la observación OBS-23
> (contraseñas de desarrollo en claro). La limpieza real se hará en una tarea
> posterior acotada (rotación + variables de entorno + escaneo en CI).

## Resumen

| Nivel de riesgo | Qué | Dónde |
|-----------------|-----|-------|
| Alto | Contraseñas semilla en claro (`admin123` / `biblio123` / `estudiante123`) | Migraciones Flyway `V3`, `V5`, `V7` |
| Alto | Secreto JWT de prueba hardcodeado (base64) | `backend/src/test/resources/application.yml` |
| Medio | Credenciales de base de datos de CI en claro (`test123`) | `.github/workflows/ci.yml` |
| Medio | Defaults débiles de Docker (`postgres`) y JWT dev | `docker-compose.yml` |
| Medio | Credencial de administrador en claro en una colección de API | `SIGCB-QR.postman_collection.json` |
| Bajo | Credenciales semilla documentadas | `README.md`, seeders, docs |

> **Aclaración importante:** la contraseña `Doctora2025` **no está versionada**.
> El archivo `application.yml` principal usa el placeholder
> `${SPRING_DATASOURCE_PASSWORD}`. `Doctora2025` solo existe en el `.env` local,
> que está en `.gitignore` y no se rastrea. No constituye un secreto expuesto en el
> repositorio, pero *sí* debería rotarse al no poder garantizar cómo se distribuye.

---

## Detalle por archivo

### 1. Migraciones Flyway — contraseñas semilla en claro (riesgo **alto**)

| Archivo | Línea | Contenido |
|---------|-------|-----------|
| `backend/src/main/resources/db/migration/V3__datos_semilla.sql` | 10 | Comentario: «(password: admin123 / biblio123 / estudiante123)» |
| `backend/src/main/resources/db/migration/V5__corregir_contraseñas.sql` | 4, 6 | Comentarios con `admin123 / biblio123 / estudiante123` |
| `backend/src/main/resources/db/migration/V7__hashes_unicos_seed.sql` | 11-13 | Contraseñas **en claro** en SQL: `crypt('admin123', ...)`, `crypt('biblio123', ...)`, `crypt('estudiante123', ...)` |

Estas contraseñas son efectivamente las de despliegue (coinciden con README).
Estando en claro en SQL de migración, cualquiera con acceso al repositorio conoce
las credenciales de los usuarios semilla.

### 2. Secreto JWT de prueba hardcodeado (riesgo **alto**)

- `backend/src/test/resources/application.yml`, línea 29:
  `dGVzdFNlY3JldEtleUZvclNpZ2NiUXJQcm9qZWN0VGVzdGluZ1B1cnBvc2VzT25seTEyMw==`
  (decodificado: `testSecretKeyForSigcbQrProjectTestingPurposesOnly123`).
- También duplicado en `backend/src/test/java/com/sigcbqr/security/JwtTokenProviderTest.java`, línea 21.
- Misma fila, línea 8: contraseña de BD de prueba por defecto `test123`.

### 3. CI — credenciales de BD en claro (riesgo **medio**)

- `.github/workflows/ci.yml`, líneas 26 y 67: `POSTGRES_PASSWORD: test123` y
  `SPRING_DATASOURCE_PASSWORD: test123`. Son credenciales de prueba de CI, no de
  producción, pero están en texto plano versionado.

### 4. Docker Compose — defaults débiles (riesgo **medio**)

`docker-compose.yml`:
- Línea 8: `POSTGRES_PASSWORD: ${POSTGRES_PASSWORD:-postgres}`
- Línea 51: `SPRING_DATASOURCE_PASSWORD: ${SPRING_DATASOURCE_PASSWORD:-postgres}`
- Línea 55: `JWT_SECRET: ${JWT_SECRET:-dev-secret-change-me-in-production}`
  (secreto JWT por defecto fijo y público).

### 5. Colección Postman — credencial de admin en claro (riesgo **medio**)

- `SIGCB-QR.postman_collection.json`, línea 31: body de login con
  `"password": "admin123"`.

### 6. Documentación y seeders — credenciales semilla (riesgo **bajo**)

- `README.md`, líneas 72-74: `admin123` / `biblio123` / `estudiante123`.
- `frontend/database/seeders/DatabaseSeeder.php`, líneas 15, 21, 27:
  `bcrypt('admin123')` y `bcrypt('123456')`.
- Docs: `docs/informe/rediseno-dashboard.md` (l. 241, 250), `docs/requisitos/historias/HU-01-autenticacion.md` (l. 15), `docs/INFORME_TECNICO_PFC_LATEX/INFORME_TECNICO_PFC.tex` (l. 1263), `docs/mediciones/cobertura/COBERTURA.md` (l. 23, 31).

### 7. Sin incidencias (por completitud)

- `backend/src/main/resources/application.yml`: usa placeholders (l. 11, 41). Sin secretos.
- `.env.example`: placeholders (`your_password_here`, `your_generated_secret_here`).
- `Makefile`, `scripts/k6-load-test.js`, `scripts/owasp-audit.sh`: credenciales vía entorno, sin secretos reales.
- `frontend/.env.example`, `frontend/.env.production.example`: sin secretos.

---

## Plan de remediación (marcado para revisión)

Este es el plan propuesto. **No se ha ejecutado** todavía: requiere decisión y,
en varios puntos, romper compatibilidad con despliegues existentes.

1. **Rotar las credenciales semilla de Flyway**:
   - Reemplazar los literales en claro de `V7` por contraseñas generadas
     aleatoriamente e inyectadas como parámetros/variables de entorno en tiempo
     de arranque (Flyway placeholders `$${...}` o scripts parametrizados).
   - Actualizar `V3`/`V5` (comentarios) y `README.md` para no documentar las
     nuevas contraseñas; exponerlas solo en el `.env` local.
2. **Mover el JWT de prueba a entorno**: sustituir el literal de
   `backend/src/test/resources/application.yml` (l. 29) y el duplicado del test
   por una variable (`${TEST_JWT_SECRET}`) alimentada desde el arnés de CI.
3. **Credenciales de CI**: pasar `test123` a secrets/tokens de GitHub (`${{ secrets.* }}`).
4. **Docker Compose**: eliminar los defaults `postgres` y `dev-secret-change-me-in-production`
   (usar lookup obligatorio `:?` como ya se hace para `PGADMIN_PASSWORD` en l. 36).
5. **Postman**: documentar que la colección es de desarrollo o parametrizar la
   contraseña con variables de entorno de Postman; no dejar `admin123` en claro.
6. **Escaneo automático**: integrar `gitleaks` o `trufflehog` en CI (`validate-docs`
   o un job nuevo) para que cualquier secreto nuevo rompa el build.

## Estado

| Código | Estado | Ejecutado en |
|--------|--------|--------------|
| Trazado de secretos versionados | Hecho | Este documento |
| Limpieza real (rotación + entorno + CI) | **Pendiente** | — |
