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

# Opcional: variables de entorno (docker compose trae valores por defecto)
cp .env.example .env

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
| Seguridad (OWASP) | 42/42 comprobaciones superadas | [OWASP-AUDIT.md](docs/mediciones/seguridad/OWASP-AUDIT.md) |
| Pruebas y cobertura | 41 pruebas, 0 fallos; 39,04 % de líneas, 13,39 % de ramas | [COBERTURA.md](docs/mediciones/cobertura/COBERTURA.md) |
| Rendimiento (k6) | p95 26–83 ms (objetivo ≤ 200 ms); 0 % de error | [REPORT.md](docs/mediciones/perf/REPORT.md) |
| Frontend (Lighthouse) | Rendimiento 82, Accesibilidad 100, Buenas prácticas 100, SEO 91 | [LIGHTHOUSE.md](docs/mediciones/frontend/LIGHTHOUSE.md) |
| Usabilidad (SUS) | **Sin datos recogidos** — instrumento y protocolo listos | [SUS.md](docs/mediciones/usabilidad/SUS.md) |

Las auditorías de esta entrega encontraron **tres defectos reales** que la suite
de pruebas no detectaba: el catálogo devolvía 500 en todo acierto de caché, toda
ruta inexistente devolvía 500 filtrando mensajes internos, y la política de
seguridad de contenido bloqueaba los iconos, la tipografía y el módulo QR de la
propia aplicación. Los tres están corregidos, con prueba de regresión, y
documentados en [`CHANGELOG.md`](CHANGELOG.md).

---

## Documentación

- [`CHANGELOG.md`](CHANGELOG.md) — historial de versiones
- [`ETHICS.md`](ETHICS.md) — tratamiento de datos, privacidad y honestidad de la evidencia
- [`LICENSE`](LICENSE) — licencia MIT
- [`docs/adr/`](docs/adr/) — decisiones de arquitectura
- [`docs/requisitos/SRS.md`](docs/requisitos/SRS.md) — especificación de requisitos
- [`docs/basedatos/DICCIONARIO-DATOS.md`](docs/basedatos/DICCIONARIO-DATOS.md) — modelo de datos
- [`docs/observaciones/OBSERVACIONES.md`](docs/observaciones/OBSERVACIONES.md) — bitácora de observaciones del docente
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