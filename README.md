# SIGCB-QR — Sistema Integral de Gestión de Biblioteca con Códigos QR

[![Licencia: MIT](https://img.shields.io/badge/licencia-MIT-blue.svg)](LICENSE)

Sistema de gestión bibliotecaria universitaria que automatiza autenticación,
catálogo, préstamos, devoluciones, reservas, multas, códigos QR, auditoría y
reportes. Proyecto académico de la Universidad Técnica Estatal de Quevedo (UTEQ).

**Versión:** 1.0.0-rc — Entrega Final

---

## Arquitectura

Dos aplicaciones de servidor con una frontera HTTP entre ellas:

```
Navegador ──HTTP──> Laravel 13 (BFF)  ──HTTP/JWT──> Spring Boot 3.5 (API) ──> PostgreSQL 16
                    Blade + Alpine                   JPA + procedimientos     Redis 7 (caché)
```

Laravel actúa como *Backend for Frontend*: renderiza las vistas y **no habla con
la base de datos**; todo el acceso a datos pasa por `App\Services\ApiClient`
contra la API. El porqué de esta decisión, con sus alternativas descartadas y su
coste, está en **[ADR-0002](docs/adr/0002-separar-api-spring-boot-de-frontend-laravel.md)**.

### Pila

| Capa | Tecnología |
|---|---|
| API | Java 21, Spring Boot 3.5.16, Spring Security, JPA, Flyway |
| Frontend | PHP 8.3, Laravel 13, Blade, Tailwind, Alpine.js |
| Datos | PostgreSQL 16 (13 procedimientos y funciones), Redis 7 |
| Infraestructura | Docker Compose, imágenes ancladas por digest SHA256 |
| Calidad | JUnit 5, Mockito, JaCoCo, k6, Lighthouse, GitHub Actions |

---

## Requisitos

Solo **Docker** con el plugin **Docker Compose**.

## Puesta en marcha

```bash
git clone <url-del-repositorio>
cd SIGCB-QR-Biblioteca

# Variables de entorno. NO es opcional: hay dos secretos sin valor por
# defecto, y compose se detiene con un mensaje si faltan.
cp .env.example .env

# Generar la clave de firma JWT (base64 de 64 bytes) y anotarla en .env
python -c "import secrets,base64; print(base64.b64encode(secrets.token_bytes(64)).decode())"

# Definir también PGADMIN_PASSWORD en .env (cualquier valor local)

# Levantar todos los servicios (la primera vez construye las imágenes)
make up
# o equivalentemente:
docker compose up -d --build

# La API tarda ~60 segundos en arrancar
curl http://localhost:8080/actuator/health   # → {"status":"UP",...}
```

| Servicio | URL |
|---|---|
| Frontend | http://localhost:8000 |
| API | http://localhost:8080 |
| Swagger UI | http://localhost:8080/swagger-ui.html |
| pgAdmin | http://localhost:5050 |

Credenciales de la semilla (datos **sintéticos**; ver [`ETHICS.md`](ETHICS.md)):

| Rol | Correo | Contraseña |
|---|---|---|
| Administrador | `admin@biblioteca.com` | `admin123` |
| Bibliotecario | `biblio@biblioteca.com` | `biblio123` |
| Estudiante | `carlos.garcia@estudiante.com` | `estudiante123` |

Otros estudiantes: `ana.martinez@estudiante.com`, `pedro.ramirez@estudiante.com`, `laura.sanchez@estudiante.com`.

> El token de sesión expira a la 1 hora; al agotarse hay que volver a iniciar sesión.

## Objetivos del Makefile

| Objetivo | Qué hace |
|---|---|
| `make up` / `make down` | Levanta o detiene toda la infraestructura |
| `make test` | Ejecuta la suite del backend con cobertura (`make test DB_PASSWORD=MiClave` para otra BD) |
| `make verify` | Valida digest de imágenes, matriz de trazabilidad y ADR |
| `make audit` | Ejecuta la auditoría de seguridad OWASP contra el sistema en marcha |
| `make metrics` | Hit ratio de Redis y estado de los contenedores |
| `make logs` / `make clean` | Registros; parada con borrado de volúmenes |

---

## Estructura del repositorio

| Ruta | Contenido |
|---|---|
| `backend/` | API Spring Boot: controladores, servicios, seguridad, entidades, migraciones Flyway y pruebas |
| `frontend/` | Aplicación Laravel (BFF): controladores, `ApiClient`, vistas Blade |
| `db/` | Esquema, semilla y los 13 procedimientos y funciones almacenados |
| `docs/adr/` | Nueve registros de decisiones de arquitectura |
| `docs/basedatos/` | Diccionario de datos (generado) y catálogo de procedimientos |
| `docs/mediciones/` | Evidencia empírica: seguridad, cobertura, rendimiento, frontend, usabilidad |
| `docs/requisitos/` | SRS (ISO/IEC/IEEE 29148), casos de uso e historias de usuario |
| `docs/trazabilidad/` | Matriz requisito → código → prueba → evidencia |
| `docs/informe/` | Informe técnico en LaTeX con bibliografía |
| `scripts/` | Auditoría OWASP, carga con k6, validadores y generadores |

---

## Evidencia empírica

Toda cifra publicada va acompañada de la orden que la produjo, la fecha, el
entorno y la salida cruda. Lo que no se ha medido se declara como no medido.

| Bloque | Resultado | Documento |
|---|---|---|
| Seguridad (OWASP) | 51/51 comprobaciones superadas | [OWASP-AUDIT.md](docs/mediciones/seguridad/OWASP-AUDIT.md) |
| Pruebas y cobertura | 55 pruebas, 0 fallos; 38,85 % de líneas, 16,31 % de ramas | [COBERTURA.md](docs/mediciones/cobertura/COBERTURA.md) |
| Rendimiento (k6) | 5 corridas a 50 VU: p95 5,55 ms, IC 95 % [4,07, 7,03]; 0 % de error | [REPORT-50VU.md](docs/mediciones/perf/50vu/REPORT-50VU.md) |
| Frontend (Lighthouse) | 6 corridas: rendimiento 97/89, accesibilidad 98, SEO 91; **buenas prácticas 78, por debajo del umbral** | [LIGHTHOUSE-6-CORRIDAS.md](docs/mediciones/frontend/lh/LIGHTHOUSE-6-CORRIDAS.md) |
| Usabilidad (SUS) | **Sin datos recogidos** — instrumento y protocolo listos | [SUS.md](docs/mediciones/usabilidad/SUS.md) |

Las auditorías de esta entrega encontraron **tres defectos reales** que la suite
de pruebas no detectaba: el catálogo devolvía 500 en todo acierto de caché, toda
ruta inexistente devolvía 500 filtrando mensajes internos, y la política de
seguridad de contenido bloqueaba los iconos, la tipografía y el módulo QR de la
propia aplicación. Los tres están corregidos, con prueba de regresión, y
documentados en [`CHANGELOG.md`](CHANGELOG.md).

---

## Compilar el informe técnico

El informe de la Entrega Final está en
[`docs/informe/informe-tecnico.tex`](docs/informe/informe-tecnico.tex), con su
bibliografía en `referencias.bib`. Para regenerar el PDF:

Antes de compilar, genera el commit y el digest que la portada incluye (se
escriben en `docs/caratula/`; no se editan a mano):

```bash
python scripts/entrega-digest.py
```

Con TeX Live / MiKTeX instalado:

```bash
cd docs/informe
latexmk -pdf informe-tecnico.tex
```

o manualmente (pdflatex + bibtex + dos pasadas):

```bash
cd docs/informe
pdflatex -interaction=nonstopmode informe-tecnico.tex
bibtex informe-tecnico
pdflatex -interaction=nonstopmode informe-tecnico.tex
pdflatex -interaction=nonstopmode informe-tecnico.tex
```

La **carátula** (criterio de piso: una página, URL en una sola línea) es un
documento aparte y se compila igual:

```bash
cd docs/caratula
pdflatex -interaction=nonstopmode caratula.tex
```

Sin instalar nada, en contenedor:

```bash
# Desde la RAIZ del repositorio. Se monta docs/ entero, no solo docs/informe:
# el informe incluye los diagramas C4 de ../diagrams/ y la portada lee el
# commit y el digest generados en ../caratula/ por scripts/entrega-digest.py.
python scripts/entrega-digest.py
docker run --rm -v "$PWD/docs":/docs -w /docs/informe texlive/texlive:latest-small \
  latexmk -pdf -interaction=nonstopmode -halt-on-error informe-tecnico.tex
```

Detalles y relación con el resto de la documentación en
[`docs/informe/README.md`](docs/informe/README.md).

## Documentación

- [`CHANGELOG.md`](CHANGELOG.md) — historial de versiones
- [`ETHICS.md`](ETHICS.md) — tratamiento de datos, privacidad y honestidad de la evidencia
- [`LICENSE`](LICENSE) — licencia MIT
- [`DEPLOYMENT.md`](DEPLOYMENT.md) — despliegue del sistema
- [`RUNBOOK.md`](RUNBOOK.md) — procedimientos operativos
- [`BACKUP.md`](BACKUP.md) — estrategia de respaldo
- [`DATA-PROVENANCE.md`](DATA-PROVENANCE.md) — procedencia de los datos
- [`CONTRIBUTORS.md`](CONTRIBUTORS.md) — integrantes, ORCID y roles CRediT
- [`docs/adr/`](docs/adr/) — decisiones de arquitectura
- [`docs/requisitos/SRS.md`](docs/requisitos/SRS.md) — especificación de requisitos
- [`docs/basedatos/DICCIONARIO-DATOS.md`](docs/basedatos/DICCIONARIO-DATOS.md) — modelo de datos
- [`docs/observaciones/OBSERVACIONES.md`](docs/observaciones/OBSERVACIONES.md) — bitácora de observaciones del docente, con el commit que resuelve cada una
- [`docs/caratula/`](docs/caratula/) — carátula de una página con la URL del repositorio
- [`docs/checklists/`](docs/checklists/) — listas de comprobación Ralph, FAIR, PRISMA e INCOSE
- [`docs/mediciones/cobertura/jacoco.csv`](docs/mediciones/cobertura/jacoco.csv) — crudo de cobertura, para recalcular las cifras
- [`SIGCB-QR.postman_collection.json`](SIGCB-QR.postman_collection.json) — colección de la API

## Historial de entregas

| Versión | Entrega | Contenido |
|---|---|---|
| 0.3.0 | Primera | SRS, casos de uso, diagramas C4 |
| 0.7.0 | Segunda | Autenticación JWT, CRUD, préstamos, Docker Compose |
| 0.9.0-rc | Tercera | RFC 7807, caché Redis, procedimientos, trazabilidad, CI |
| 1.0.0-rc | **Final** | Licencia, ADR, ética, diccionario de datos, evidencia empírica de los cinco bloques, corrección de tres defectos |

## Citar este trabajo

Ver [`CITATION.cff`](CITATION.cff). El archivo permanente y el DOI se publican en
Zenodo a partir de la etiqueta `v1.0.0`; los metadatos del depósito están en
[`.zenodo.json`](.zenodo.json).

## Licencia

MIT — ver [`LICENSE`](LICENSE).