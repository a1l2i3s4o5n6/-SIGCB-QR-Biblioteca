# DATA-PROVENANCE.md — Procedencia de los datos de SIGCB-QR

Documento de trazabilidad de los datos empleados en el proyecto, de acuerdo con
el criterio R2 (datos con DOI, diccionario y provenance) de la Entrega Final.

## Volumen de datos

El modelo de datos está documentado íntegramente en
[`docs/basedatos/DICCIONARIO-DATOS.md`](docs/basedatos/DICCIONARIO-DATOS.md):
**19 tablas y 129 columnas**, generado automáticamente desde el esquema real
(script `scripts/generar-diccionario-datos.py`) con modo de verificación.

## Naturaleza de los datos

Todos los datos cargados son **sintéticos** (semilla en
`backend/src/main/resources/db/migration/V3__datos_semilla.sql`). No contienen
información de personas reales. Ver [`ETHICS.md`](ETHICS.md).

## Fuentes y formación

| Capa | Fuente | Generación |
|---|---|---|
| Esquema relacional | Migraciones de Flyway (`V1__schema.sql`, `V2__procedimientos.sql`, `V3__datos_semilla.sql`) | Gestionado por Flyway al arrancar la API |
| Procedimientos/funciones | `db/procs/` (13 archivos) | Instalados por Flyway/V2 |
| Diccionario de datos | Generado desde el esquema real | `make docs-check` valida que no quede desfasado |
| Datos de prueba | Semilla sintética | `V3__datos_semilla.sql` |

## Datos derivados de mediciones (evidencia empírica)

Los datos de las mediciones empíricas se conservan como **crudos** en
`docs/mediciones/` (nunca se ejemplarizan):

| Bloque | Rutas de los crudos |
|---|---|
| Seguridad | `docs/mediciones/seguridad/owasp-audit-raw.txt` y `audit-sql-dynamic.json` |
| Cobertura | `docs/mediciones/cobertura/jacoco.xml` y `jacoco.csv` — **crudo versionado**, de modo que las cifras son recalculables por un tercero |
| Rendimiento | `docs/mediciones/perf/k6-*.txt` |
| Frontend | `docs/mediciones/frontend/*.json` / `*.html` |
| Usabilidad | `docs/mediciones/usabilidad/sus-respuestas.csv` |

## Cadena de custodia de cada crudo

| Crudo | Instrumento y versión | Comando que lo regenera |
|---|---|---|
| `owasp-audit-raw.txt` | curl 8.19.0 | `bash scripts/owasp-audit.sh` |
| `audit-sql-dynamic.json` | Analizador propio, con autotest de 3/3 detecciones y 0 falsos positivos | `bash scripts/audit-sql-dynamic.sh --json` |
| `jacoco.xml` / `jacoco.csv` | JaCoCo 0.8.12 | `make test`, y copiar desde `backend/target/site/jacoco/` |
| `k6-*.txt` | k6 | `k6 run scripts/k6-load-test.js` |
| `*.report.json` | Lighthouse 12.8.2 | `lighthouse <url> --output=json` |
| `sus-respuestas.csv` | Instrumento SUS propio | Recogida manual; **cero respuestas a la fecha** |

## Licencia de los datos

Los datos de medición de `docs/mediciones/` y la documentación de `docs/` se
publican bajo **Creative Commons Attribution 4.0 International (CC BY 4.0)**.
El código fuente se publica por separado bajo licencia MIT (ver `LICENSE`).

Atribución sugerida:

> Equipo SIGCB-QR (Arias Moreira, M. G.; Romero Méndez, B. S.; Zambrano
> Moreira, A. A.), *Datos de medición de SIGCB-QR*, 2026. CC BY 4.0.

## Identificador persistente

**No hay DOI todavía.** El depósito en Zenodo se publica al etiquetar `v1.0.0`
(procedimiento en [`docs/PUBLICACION-ZENODO.md`](docs/PUBLICACION-ZENODO.md)).
No se declara ningún identificador provisional: un DOI que no resuelve
convierte la cita en falsa.

## Integridad de la entrega

El digest SHA-256 de la entrega resume el manifiesto de todas las fuentes
versionadas y se comprueba con:

```bash
python scripts/entrega-digest.py --check
```

Los PDF generados (`informe-tecnico.pdf`, `caratula.pdf`) quedan fuera del
manifiesto a propósito: ambos imprimen el digest en su portada, de modo que
incluirlos lo haría referirse a sí mismo.

## Trazabilidad requisito → dato

La matriz de trazabilidad ([`docs/trazabilidad/`](docs/trazabilidad/)) vincula
cada requisito con su evidencia empírica. Se valida automáticamente con
`make verify` (55 pruebas, 35 filas, 0 errores).

## Curators

| Dato | Curador | Última actualización |
|---|---|---|
| Diccionario de datos | Generador automático | 2026-09-01 |
| Evidencia empírica | Equipo SIGCB-QR | 2026-09-01 |

## Declaración de ausencia de DOI de dataset

Este repositorio aún no cuenta con un DOI de dataset **ni un depósito de datos
por separado**. Siguiendo la misma lógica que para el DOI de software (ver
`CITATION.cff`), se prefiere omitir un identificador a inventar uno que no
resuelva. El procedimiento para obtener el DOI se detalla en
[`docs/PUBLICACION-ZENODO.md`](docs/PUBLICACION-ZENODO.md) y la licencia de
datos a adoptar será **CC BY 4.0** (no MIT) una vez se publique el depósito de
datos por separado.
