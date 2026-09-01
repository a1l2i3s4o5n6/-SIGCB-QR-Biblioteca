# Bitácora de Observaciones — SIGCB-QR

> Archivo de trazabilidad de las observaciones recibidas del docente en las
> Entregas 1A, 1B y Tercera, con la decisión tomada y dónde verificarla.

## Resumen

| Indicador | Valor |
|-----------|-------|
| Total observaciones | 21 |
| Resueltas | 19 |
| Resueltas parcialmente | 2 |
| No resueltas | 0 |
| % Resuelto | 90 % completo, 10 % parcial |

---

## Observaciones de las Entregas 1A y 1B

| Código | Fuente | Criterio | Observación | Decisión | Verificación |
|--------|--------|----------|-------------|----------|--------------|
| OBS-01 | Entrega 1A | SRS | El SRS no sigue la plantilla ISO/IEC/IEEE 29148:2018. Faltan rationale, trazabilidad y métodos de verificación. | Resuelta: SRS completo con identificadores únicos, rationale, trazabilidad y criterios de verificación. | `docs/requisitos/SRS.md` |
| OBS-02 | Entrega 1B | Auth JWT | El JWT no incluye el claim `aud`. | Resuelta: se añaden `iss`, `aud`, `nbf`, `iat` y `jti`. | `JwtTokenProviderTest.generaTokenConTodosLosClaimsEstandar` |
| OBS-03 | Entrega 1B | Auth Cookie | `Secure=false` fijo; debe venir de configuración. | Resuelta: gobernado por `app.jwt.secure-cookie`; `true` en el perfil prod. | ADR-0003; `application-prod.yml` |
| OBS-04 | Entrega 1B | API Errors | Los errores no siguen RFC 7807. | Resuelta: `ProblemDetail` con `type`, `title`, `status`, `detail`, `instance`. | ADR-0004; auditoría OWASP, bloque API8 |
| OBS-05 | Entrega 1B | Cache | No hay caché Redis con TTL configurable ni métrica de hit ratio. | Resuelta: Redis con TTL desde `app.cache.default-ttl-seconds`; hit ratio medido al 99,7 %. | ADR-0006; `docs/mediciones/perf/REPORT.md` |
| OBS-06 | Entrega 1B | Data Access | Agregaciones por JPQL en vez de procedimientos; sin separación CRUD-ORM/SP. | Resuelta: 13 procedimientos y funciones en `db/procs/`; criterio de reparto documentado. | ADR-0005; `docs/basedatos/CATALOGO-SP.md` |
| OBS-07 | Entrega 1B | Docker | Imágenes por etiqueta móvil en vez de digest. | Resuelta: cinco imágenes ancladas por digest, verificados contra el registro y comprobados en CI. | ADR-0007; `scripts/validate-digests.py` |

---

## Observaciones de la Tercera Entrega

### Bloque A — Defectos verificados por el docente

| Código | Observación | Decisión | Verificación |
|--------|-------------|----------|--------------|
| OBS-08 | Los tres digest SHA256 de `docker-compose.yml` son inválidos: tienen 62 y 63 caracteres en lugar de 64, por lo que `make up` fallaría al descargar las imágenes. | **Resuelta.** Digest reales, contrastados el 2026-09-01 contra `registry-1.docker.io`. Y, sobre todo, la comprobación es ahora automática: `make verify` y CI rechazan cualquier digest que no tenga 64 hexadecimales. | `scripts/validate-digests.py`; ADR-0007 |
| OBS-09 | La matriz de trazabilidad marca como «verificadas» cuatro clases de prueba que no existen en el repositorio. | **Resuelta.** Matriz reescrita contra el inventario real. El validador extrae ahora los métodos de prueba del repositorio y **falla si la matriz cita una prueba o una evidencia inexistente**. | `scripts/validate-traceability.sh`; CI, trabajo `validate-docs` |
| OBS-10 | El frontend Laravel es un esqueleto de plantilla sin modificar; su tablero muestra cifras inventadas («24 libros prestados hoy, +12 % vs ayer») y no consume el API. | **Resuelta** (ya en el código antes de esta entrega, no verificada entonces). `DashboardController` consume `getEstadisticas()`, `getReservas()` y `getPrestamos()`; la vista muestra `$stats[...]` con `?? 0`. No queda ninguna cifra fija en las plantillas. Nueve módulos consumen el API a través de `ApiClient`. | `frontend/app/Http/Controllers/DashboardController.php`; ADR-0002 |
| OBS-11 | Faltan los ADR. | **Resuelta.** Nueve ADR en formato MADR, con consecuencias negativas obligatorias, más índice y validador. | `docs/adr/`; `scripts/validate-adr.sh` |
| OBS-12 | Falta el archivo LICENSE. | **Resuelta.** MIT. | `LICENSE` |
| OBS-13 | Falta ETHICS.md. | **Resuelta.** Datos tratados, principios, controles verificables, carencias declaradas, uso de asistentes de IA y reglas de honestidad en la evidencia. | `ETHICS.md` |
| OBS-14 | Falta el diccionario de datos. | **Resuelta.** 19 tablas y 129 columnas, **generado** desde el esquema real, con modo `--check` para detectar desfase. | `docs/basedatos/DICCIONARIO-DATOS.md`; `scripts/generar-diccionario-datos.py` |
| OBS-15 | Falta CHANGELOG. | **Resuelta.** Formato Keep a Changelog. | `CHANGELOG.md` |
| OBS-16 | El informe no tiene ni una cita ni sección de amenazas a la validez. | **Resuelta.** Informe nuevo de 13 páginas con 29 referencias citadas (0 sin definir) y un apartado de validez con las cuatro categorías de Wohlin et al. | `docs/informe/informe-tecnico.pdf` |
| OBS-17 | El repositorio mezcla Laravel/PHP con Spring Boot sin un ADR que justifique la pila, lo que dificulta evaluar la coherencia arquitectónica. | **Resuelta.** ADR-0002 documenta el patrón *Backend for Frontend*: restricciones que lo forzaron, cuatro alternativas evaluadas, tres reglas auditables de frontera y el coste asumido. Las tres reglas se verificaron sobre el repositorio. | ADR-0002; §2.2 del informe |

### Bloque B — Evidencia empírica ausente

| Código | Observación | Decisión | Verificación |
|--------|-------------|----------|--------------|
| OBS-18 | No hay auditoría OWASP con curl. | **Resuelta.** 42 comprobaciones reales; 42 superadas. Se documentan también la corrida previa (32/42) y el defecto del propio instrumento. | `scripts/owasp-audit.sh`; `docs/mediciones/seguridad/` |
| OBS-19 | No hay medición de cobertura. | **Resuelta.** JaCoCo: 39,04 % de líneas y 13,39 % de ramas sobre 41 pruebas en verde. Se documenta que la suite **no estaba en verde** al inicio de esta entrega (4 errores sobre 36). | `docs/mediciones/cobertura/COBERTURA.md` |
| OBS-20 | No hay medición con Lighthouse. | **Resuelta.** Rendimiento 82, accesibilidad 100, buenas prácticas 100, SEO 91, con informes HTML y JSON. Descubrió un defecto real: la CSP bloqueaba los recursos de la propia aplicación. | `docs/mediciones/frontend/` |
| OBS-21 | No hay prueba SUS. | **Resuelta parcialmente.** Instrumento, protocolo de 12 participantes, tareas por perfil y herramienta de cálculo verificada contra los casos canónicos. **No se ha administrado el cuestionario y no se publica ninguna puntuación**, porque inventarla sería repetir el problema que esta entrega corrige. REQ-U-001 figura como `pendiente`. | `docs/mediciones/usabilidad/SUS.md`; `scripts/sus-score.py` |
| OBS-22 | No hay archivo permanente en Zenodo ni DOI. | **Resuelta parcialmente.** `.zenodo.json`, `CITATION.cff` y el procedimiento completo están listos. **El DOI no se declara porque el depósito aún no se ha publicado**: un identificador inventado no resolvería y convertiría la cita en falsa. Se obtiene publicando la etiqueta `v1.0.0`. | `.zenodo.json`; `docs/PUBLICACION-ZENODO.md` |

---

## Hallazgos propios de esta entrega

Defectos que **no** estaban en las observaciones del docente y que aparecieron al
auditar el sistema en marcha. Se registran aquí porque su origen es el mismo:
afirmar como verificado lo que no se había comprobado.

| Código | Hallazgo | Cómo se encontró | Corrección |
|--------|----------|------------------|------------|
| HAL-01 | `GET /api/libros` devolvía **500 en todo acierto de caché**: `PageImpl` se serializa a Redis pero no se puede deserializar. | Petición manual repetida durante la auditoría OWASP | ADR-0006; `CacheSerializationTest` |
| HAL-02 | La clave de caché ignoraba el criterio de orden: dos peticiones con distinto `sort` compartían entrada. | Al corregir HAL-01 | Clave con orden incluido |
| HAL-03 | **Toda ruta inexistente devolvía 500** en lugar de 404, y el manejador genérico filtraba al cliente el mensaje interno del framework. | Auditoría OWASP, bloque API8 | `GlobalExceptionHandlerTest.rutaInexistenteEs404YNo500` y dos pruebas más |
| HAL-04 | La **CSP bloqueaba los recursos de la propia aplicación**: sin iconos, sin la tipografía y con el módulo de códigos QR inoperativo, en todas las páginas. | Auditoría Lighthouse | `frontend/docker/000-default.conf`; `docs/mediciones/frontend/LIGHTHOUSE.md` |
| HAL-05 | La suite de pruebas **no estaba en verde**: 4 errores sobre 36. | Primera ejecución de `mvn verify` | `docs/mediciones/cobertura/COBERTURA.md`, §5 |
| HAL-06 | El arnés de carga de k6 iniciaba sesión en cada iteración y chocaba con el límite de tasa, de modo que habría medido el limitador y no el catálogo. | Al preparar la medición de rendimiento | `scripts/k6-load-test.js`; `docs/mediciones/perf/REPORT.md`, §3 |
| HAL-07 | El propio script de auditoría producía tres falsos positivos: copiaba el archivo de cookies en vez de abrir una sesión nueva, y la revocación por `jti` invalidaba ambas. | Al analizar por qué fallaban tres comprobaciones | `docs/mediciones/seguridad/OWASP-AUDIT.md`, §5 |

---

## Historial de cambios

| Fecha | Autor | Cambio |
|-------|-------|--------|
| 2026-07-30 | Equipo SIGCB-QR | Creación inicial con las 7 observaciones de las Entregas 1A y 1B. |
| 2026-09-01 | Equipo SIGCB-QR | Añadidas las 15 observaciones de la Tercera Entrega (OBS-08 a OBS-22) y los 7 hallazgos propios (HAL-01 a HAL-07). Añadida la columna de verificación: toda observación resuelta apunta al artefacto donde comprobarlo. |
