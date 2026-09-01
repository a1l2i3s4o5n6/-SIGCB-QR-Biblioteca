# Changelog

Todos los cambios notables de SIGCB-QR se documentan en este archivo.

El formato sigue [Keep a Changelog](https://keepachangelog.com/es-ES/1.1.0/) y el
versionado sigue [Versionado Semántico](https://semver.org/lang/es/).

---

## [1.0.0-rc] — 2026-09-01 — Entrega Final

Esta versión responde a las observaciones formales de la Tercera Entrega. Los
cambios se agrupan en tres bloques: **defectos encontrados y corregidos**,
**evidencia empírica producida** y **documentación de gobernanza**.

### Corregido — defectos del sistema

Tres defectos reales, ninguno detectado por la suite de pruebas y los tres
descubiertos al ejercitar el sistema en marcha.

- **`GET /api/libros` devolvía 500 en todo acierto de caché.** El método cacheado
  devolvía `Page`/`PageImpl`, que `GenericJackson2JsonRedisSerializer` escribe en
  Redis pero no puede volver a leer (`PageImpl` carece de constructor sin
  argumentos). La primera petición tras cada expiración del TTL funcionaba y todas
  las siguientes fallaban, de modo que el fallo parecía intermitente. El endpoint
  del catálogo pasa a devolver `PageResponse<LibroResponse>`, un DTO con ida y
  vuelta demostrada por prueba. Ver ADR-0006.
- **La clave de caché ignoraba el criterio de orden.** Dos peticiones con el mismo
  número y tamaño de página pero distinto `sort` compartían entrada, y una recibía
  los datos ordenados de la otra. La clave incluye ahora el orden.
- **Toda ruta inexistente devolvía 500 en lugar de 404**, y el manejador genérico
  devolvía al cliente el mensaje interno de la excepción
  (`"Error interno del servidor: No static resource actuator/env."`). Se añaden
  manejadores para `NoResourceFoundException`, `NoHandlerFoundException` (404) y
  `HttpRequestMethodNotSupportedException` (405); el manejador genérico registra la
  traza en el servidor y devuelve un detalle genérico. Descubierto por la auditoría
  OWASP.
- **La CSP del frontend bloqueaba los recursos de la propia aplicación.** La
  política sólo admitía `'self'`, mientras que ambos *layouts* cargan Font Awesome
  y la tipografía Poppins desde CDN y el módulo de códigos QR carga `qrcode.min.js`
  desde cdnjs. En el navegador no se dibujaba ningún icono, la tipografía caía a la
  de reserva y el módulo QR quedaba inoperativo. El servidor respondía 200, así que
  el fallo era silencioso. Descubierto por la auditoría Lighthouse.

### Corregido — la suite de pruebas no estaba en verde

Al inicio de esta entrega, `mvn verify` terminaba en `BUILD FAILURE` con 4 errores
sobre 36 pruebas, pese a que la Tercera Entrega declaraba la suite como correcta.

- `AuthControllerTest` (2 errores): el contexto de `@WebMvcTest` no arrancaba desde
  que `AuthController` pasó a depender de `RateLimitService` sin añadir el doble
  correspondiente. Añadido `@MockBean RateLimitService`.
- `JwtAuthenticationFilterTest` (2 errores): `UnnecessaryStubbing`. Las pruebas
  simulaban `extractTokenFromCookie`, pero el filtro llama a
  `extractTokenFromRequest` desde que se admitió también la cabecera
  `Authorization`; no ejercitaban el camino real.
- `LibroControllerTest`: construía un `PageResponse` y luego pasaba un `PageImpl` al
  doble. Corregido y reforzado con aserciones sobre el cuerpo de la respuesta.

Estado actual: **41 pruebas, 0 fallos, 0 errores**, con los umbrales de JaCoCo
cumplidos.

### Corregido — trazabilidad y reproducibilidad

- **La matriz de trazabilidad declaraba como «verificadas» pruebas inexistentes.**
  `UsuarioControllerTest`, `LibroPerfTest` y varios métodos cuyos nombres no
  coincidían con los reales. Reescrita contra el inventario real de pruebas.
- **`scripts/validate-traceability.sh` reescrito**: ahora extrae el inventario real
  de métodos de prueba del repositorio y **falla si la matriz cita una prueba o una
  evidencia que no existe**. Es la comprobación que impide que el problema anterior
  se repita.
- **Los tres digest SHA256 de `docker-compose.yml` eran inválidos** (62 y 63
  caracteres en lugar de 64), por lo que `make up` fallaba al descargar las
  imágenes. Sustituidos por digest reales, verificados contra
  `registry-1.docker.io` el 2026-09-01, y protegidos por
  `scripts/validate-digests.py` (`make verify`) y por CI.
- **`make test` y el flujo de CI apuntaban a `sigcb-qr-api/`**, un directorio que ya
  no existe; corregido a `backend/`.

### Corregido — el arnés de carga producía datos inválidos

- `scripts/k6-load-test.js` **iniciaba sesión en cada iteración de cada usuario
  virtual**. Con el límite de 5 intentos por IP y minuto, a partir del quinto login
  todo eran respuestas 429, que se responden sin tocar la base de datos: la tasa de
  error se disparaba y el p95 salía artificialmente bajo. La prueba medía el
  limitador de tasa y lo presentaba como velocidad del catálogo. El inicio de sesión
  pasa a `setup()`, fuera de la ventana de medición.

### Añadido — evidencia empírica

- **Auditoría OWASP** (`docs/mediciones/seguridad/`): `scripts/owasp-audit.sh`, 42
  comprobaciones con `curl` contra el sistema en marcha sobre API1, API2, API3,
  API5, API8 y A03. Resultado: 42/42. Se documentan también la corrida previa
  (32/42) y el defecto del propio instrumento que explicaba tres de sus fallos.
- **Cobertura** (`docs/mediciones/cobertura/`): JaCoCo sobre las 41 pruebas —
  39,04 % de líneas, 13,39 % de ramas, con análisis de por qué la cobertura de rama
  es el dato relevante y por qué no detectó ninguno de los tres defectos.
- **Rendimiento** (`docs/mediciones/perf/`): cuatro corridas de k6 con salida cruda.
  p95 entre 26 y 83 ms frente al umbral de 200 ms; 0 % de error sobre 1 474
  peticiones; hit ratio de Redis del 99,7 %. Se concluye, contra la hipótesis de
  partida, que a esta escala el p95 lo domina el calentamiento de la JVM y no el
  estado de la caché.
- **Lighthouse** (`docs/mediciones/frontend/`): rendimiento 82, accesibilidad 100,
  buenas prácticas 100, SEO 91, con informe HTML y JSON completos. Se documenta que
  el 100 de rendimiento de la corrida previa era un artefacto de la CSP rota.
- **SUS** (`docs/mediciones/usabilidad/`): instrumento, protocolo de 12
  participantes, tareas y `scripts/sus-score.py` con autocomprobación de la fórmula.
  **Sin datos recogidos**, y así se declara: no se publica ninguna puntuación.

### Añadido — gobernanza y documentación

- `LICENSE` — MIT.
- `ETHICS.md` — datos tratados, principios, controles verificables, carencias
  conocidas, uso de asistentes de IA y reglas de honestidad en la evidencia.
- `CHANGELOG.md` — este archivo.
- **Nueve ADR** en `docs/adr/`, incluido ADR-0002, que justifica la convivencia de
  Spring Boot y Laravel como patrón *Backend for Frontend* con tres reglas
  auditables, y que responde a la observación sobre coherencia arquitectónica.
- `scripts/validate-adr.sh` — comprueba que el índice de ADR y los archivos no se
  desincronicen.
- **Diccionario de datos** (`docs/basedatos/DICCIONARIO-DATOS.md`) — 19 tablas, 129
  columnas, **generado** desde el esquema real por
  `scripts/generar-diccionario-datos.py`, con modo `--check` para detectar que se
  ha quedado desfasado.
- `.zenodo.json` y `CITATION.cff` actualizado, para el archivo permanente y el DOI.
- `README.md` reescrito: la estructura documentada coincide ahora con la real.

### Cambiado

- `LibroService.listar` devuelve `PageResponse<LibroResponse>` en lugar de
  `Page<LibroResponse>` (ver ADR-0006). Cambio de firma interno; el contrato HTTP
  del endpoint no varía.
- La CSP del frontend declara los tres orígenes externos que la aplicación usa
  realmente.
- Nuevos objetivos en el `Makefile`: `verify`, `audit`, `docs-check`.
- CI ejecuta además `validate-adr.sh` y la validación de digest.

### Requisitos nuevos en la matriz

Se añaden cuatro requisitos no funcionales que ya estaban implementados pero no
trazados, todos con prueba real: REQ-NF-008 (errores conformes a RFC 7807),
REQ-NF-009 (los errores no divulgan información interna), REQ-NF-010 (los valores
cacheados admiten ida y vuelta) y REQ-U-002 (calidad del frontend medida con
Lighthouse). REQ-U-001 (SUS) se añade como `pendiente`.

---

## [0.9.0-rc] — 2026-07-30 — Tercera Entrega

### Añadido

- Claims JWT estándar (`iss`, `aud`, `nbf`, `iat`, `jti`).
- Errores HTTP conformes a RFC 7807 (`ProblemDetail`).
- Caché Redis con TTL configurable para el listado de libros.
- Migración de agregaciones y reportes a procedimientos almacenados.
- Ingeniería de requisitos según ISO/IEC/IEEE 29148: SRS, casos de uso, historias.
- Matriz de trazabilidad y pruebas automatizadas con JUnit 5 y JaCoCo.
- CI con GitHub Actions.
- Reproducibilidad: `Makefile`, `CITATION.cff`, anclaje de imágenes por digest.
- Módulo de códigos QR (backend y frontend).
- Módulos de auditoría, configuración, multas, reportes y devoluciones.

### Corregido

- C1: secreto JWT tomado del entorno.
- C2: hash bcrypt único por usuario en la semilla.
- C3: límite de tasa en inicio de sesión y registro.
- C4: cookie `Secure` en el perfil de producción.
- H1, H2: `@PreAuthorize` en reservas; `isEnabled` respeta la columna `activo`.
- H3–H5: excepciones tipadas, comprobación de propiedad en reservas y eliminación
  del SQL dinámico en los procedimientos.
- H6, H7: auditoría en el registro y lista negra de JWT sin condición de carrera
  (`ON CONFLICT`).
- M1, M2: `@EntityGraph` en reservas y multas para evitar el problema N+1.

---

## [0.7.0] — Segunda Entrega

### Añadido

- Autenticación con JWT y CRUD de usuarios y libros.
- Préstamos y devoluciones.
- Infraestructura con Docker Compose.

---

## [0.3.0] — Primera Entrega

### Añadido

- SRS inicial, casos de uso y diagramas C4 (contexto, contenedores, componentes).
