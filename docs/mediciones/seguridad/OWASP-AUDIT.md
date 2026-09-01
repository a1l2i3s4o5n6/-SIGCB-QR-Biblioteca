# Auditoría de seguridad OWASP — SIGCB-QR

**Fecha de ejecución:** 2026-09-01
**Versión auditada:** `v1.0.0-rc` (rama `main`)
**Marcos de referencia:** OWASP API Security Top 10 (2023) y OWASP Top 10 (2021)
**Instrumento:** `scripts/owasp-audit.sh` — 42 comprobaciones, todas peticiones
`curl` reales contra el sistema en marcha
**Salida cruda:** [`owasp-audit-raw.txt`](owasp-audit-raw.txt)

---

## 1. Cómo reproducir

```bash
make up                        # levanta postgres, redis, api, frontend y pgadmin
bash scripts/owasp-audit.sh    # 42 comprobaciones; código de salida 0 si todas pasan
```

El script no simula nada: cada comprobación es una petición HTTP contra
`http://localhost:8080` (API) o `http://localhost:8000` (frontend), y compara el
código de estado o la cabecera obtenidos con el valor esperado. Cuando una
comprobación no admite un valor esperado único —el tamaño máximo de página, la
presencia de `Secure` en una cookie sobre HTTP local— se registra como
observación (`[NOTA]`) y no puntúa.

**Entorno de la corrida:** Windows 11 Pro (26200), Docker 29.5.2 sobre WSL2,
curl 8.19.0, imágenes ancladas por digest según ADR-0007.

---

## 2. Resultado

| Indicador | Valor |
|---|---:|
| Comprobaciones ejecutadas | **42** |
| Superadas | **42** |
| Fallidas | **0** |
| Observaciones sin puntuar | 3 |

**Este resultado es el de la segunda corrida.** La primera, ejecutada el mismo
día sobre la versión anterior del código, dio **32 superadas y 6 fallidas**. De
esos seis fallos, tres eran un defecto real del sistema y tres un defecto del
propio instrumento. Los dos casos se detallan en §4 y §5, porque el valor de una
auditoría está en lo que encuentra, no en la cifra final.

### Cobertura por bloque

| Bloque | Comprobaciones | Resultado |
|---|---:|---|
| API1:2023 — Autorización a nivel de objeto (BOLA) | 4 | Superado |
| API2:2023 — Autenticación rota | 9 | Superado |
| API3:2023 — Exposición excesiva de propiedades | 2 | Superado |
| API5:2023 — Autorización a nivel de función (BFLA) | 4 | Superado |
| API8:2023 / A05:2021 — Configuración incorrecta | 16 | Superado |
| A03:2021 — Inyección | 6 | Superado |
| API4:2023 — Consumo de recursos sin restricción | 1 (+2 notas) | Superado |

---

## 3. Detalle por bloque

### API1:2023 — Autorización a nivel de objeto

Un usuario con rol `ESTUDIANTE` intenta leer recursos que no le pertenecen.

| Petición | Esperado | Obtenido |
|---|---:|---:|
| `GET /api/auditoria` como estudiante | 403 | 403 |
| `GET /api/usuarios` como estudiante | 403 | 403 |
| `GET /api/usuarios/1` como estudiante | 403 | 403 |
| `GET /api/usuarios` como admin | 200 | 200 |

La última fila es la comprobación de control: sin ella, un 403 constante por
cualquier motivo —un fallo de configuración, un servicio caído— pasaría por
seguridad correcta.

### API2:2023 — Autenticación rota

| Petición | Esperado | Obtenido |
|---|---:|---:|
| `GET /api/libros` sin token | 401 | 401 |
| `GET /api/libros` con token de formato inválido | 401 | 401 |
| `GET /api/libros` con firma manipulada | 401 | 401 |
| `GET /api/libros` con `Bearer` vacío | 401 | 401 |

Atributos de la cookie observados en la respuesta real de `POST /api/auth/login`:

```
Set-Cookie: access_token=eyJhbGciOiJIUzM4NCJ9...; Max-Age=3600;
            Path=/; HttpOnly; SameSite=Strict
```

- `HttpOnly` — presente. El token no es legible desde JavaScript (ADR-0003).
- `SameSite=Strict` — presente.
- `Secure` — **ausente**, y es el comportamiento esperado en esta corrida: el
  entorno local sirve por HTTP y `app.jwt.secure-cookie` vale `false`. En el
  perfil `prod` (`application-prod.yml`) vale `true`. La auditoría lo registra
  como observación y no como aprobado, porque **no se ha verificado sobre un
  despliegue HTTPS real**: es una carencia de la evidencia, no una virtud del
  sistema.

Revocación efectiva en el cierre de sesión (ADR-0009), sobre una sesión propia:

| Paso | Esperado | Obtenido |
|---|---:|---:|
| La cookie sirve antes del `logout` | 200 | 200 |
| `POST /api/auth/logout` | 200 | 200 |
| La misma cookie tras el `logout` | 401 | 401 |

### API3:2023 — Exposición excesiva de propiedades

- La respuesta de `POST /api/auth/login` no contiene el campo `password`.
- El listado `GET /api/usuarios` no contiene hashes bcrypt (se busca el prefijo
  `$2a$` / `$2b$` en el cuerpo).

### API5:2023 — Autorización a nivel de función

| Petición | Esperado | Obtenido |
|---|---:|---:|
| `POST /api/usuarios` como estudiante, **cuerpo válido** | 403 | 403 |
| `GET /api/reportes/prestamos-diarios` como estudiante | 403 | 403 |
| `GET /api/configuracion` como estudiante | 403 | 403 |
| Usuarios creados por el intento | 0 | 0 |

Que el cuerpo sea válido es deliberado. Con un cuerpo vacío, la API responde
**400** y no 403: la validación de *bean* se ejecuta antes que `@PreAuthorize`.
Una auditoría que enviara `{}` obtendría 400 y no habría probado nada sobre la
autorización. Se anota la ordenación validación-antes-que-autorización como
observación menor: revela que el endpoint existe y qué campos espera, aunque no
permite ejecutarlo.

La última fila importa tanto como las otras tres: un 403 sería inútil si el
usuario se hubiera creado igualmente.

### API8:2023 / A05:2021 — Configuración incorrecta

Cabeceras de la API (`GET /api/libros`, sin autenticar):

```
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
Cache-Control: no-cache, no-store, max-age=0, must-revalidate
Content-Type: application/problem+json
```

Cabeceras del frontend (`GET /login`):

```
Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline';
                         style-src 'self' 'unsafe-inline'; img-src 'self' data:;
                         font-src 'self'; connect-src 'self';
                         frame-ancestors 'self'; base-uri 'self'; form-action 'self'
X-Content-Type-Options: nosniff
Referrer-Policy: strict-origin-when-cross-origin
```

La CSP admite `'unsafe-inline'` en scripts y estilos, lo que reduce su valor
frente a XSS. Es una consecuencia del uso de Alpine.js y de atributos de estilo
en línea en las plantillas Blade. Queda registrado como deuda, no como control.

CORS:

| Petición | Esperado | Obtenido |
|---|---:|---:|
| Preflight desde `http://evil.example` | 403 | 403 |
| Preflight desde el origen del frontend | 200 | 200 |

Superficie de actuator: solo `health` e `info` están expuestos
(`management.endpoints.web.exposure.include`). `env`, `beans` y `heapdump`
responden 404.

Semántica de errores:

| Petición | Esperado | Obtenido |
|---|---:|---:|
| `GET /api/ruta-que-no-existe` (autenticado) | 404 | 404 |
| `GET /ruta/fuera/del/api` | 404 | 404 |
| `DELETE /api/auth/login` | 405 | 405 |
| El 404 no filtra el mensaje interno de Spring | sí | sí |

### A03:2021 — Inyección

Cinco cargas enviadas a `GET /api/libros/buscar?q=…`:

```
' OR 1=1--
'; DROP TABLE libros;--
1' UNION SELECT NULL,NULL,NULL--
admin'--
%' OR '1'='1
```

Las cinco devuelven 200 y se tratan como texto de búsqueda. Tras enviarlas, el
catálogo sigue operativo (`GET /api/libros` → 200), lo que descarta que la carga
destructiva llegara al motor. El acceso va por consultas parametrizadas de Spring
Data y por procedimientos sin SQL dinámico concatenado
(`V8__fix_inyeccion_sql_procedures.sql`, ADR-0005).

**Limitación:** esta comprobación demuestra que las cargas *no rompen* el sistema
y que se tratan como dato. No es una prueba de inyección ciega ni basada en
tiempo; una auditoría más exigente requeriría `sqlmap` u otra herramienta
específica. Ver §6.

### API4:2023 — Consumo de recursos sin restricción

- `GET /api/libros?size=5000` devuelve `size = 2000`: existe un tope de página y
  un cliente no puede pedir el catálogo entero en una petición. Se registra como
  observación porque 2000 es alto para una interfaz que pagina de 10 en 10.
- Límite de tasa en el login: la ráfaga de ocho intentos fallidos recibe 429. En
  esta corrida el corte aparece en el primer intento porque la ventana de 60 s
  aún contenía los intentos de la corrida anterior; el límite configurado es de
  5 peticiones por IP y minuto (`app.security.rate-limit.max-requests`).

---

## 4. Defecto encontrado por la auditoría

La primera corrida marcó como fallidas tres comprobaciones de actuator, con un
resultado que no encajaba con ninguna hipótesis previa: `GET /actuator/env`
devolvía **500**, no 404.

Sondeando una ruta cualquiera apareció la causa real, más amplia que actuator:

```
$ curl -s -i http://localhost:8080/ruta/que/no/existe | head -1
HTTP/1.1 500

{"type":"https://api.sigcbqr.com/errors/internal-error","title":"Error interno",
 "status":500,"detail":"Error interno del servidor: No static resource actuator/env.",
 "instance":"/actuator/env", ...}
```

**Toda ruta inexistente respondía 500.** `NoResourceFoundException` no tenía
manejador propio y caía en el manejador genérico `@ExceptionHandler(Exception.class)`,
que además construía el detalle como `"Error interno del servidor: " + ex.getMessage()`
y devolvía al cliente el mensaje interno del framework.

Dos consecuencias:

1. **Semántica rota.** Una URL mal escrita era indistinguible de un fallo real del
   servidor, tanto para un cliente como para la monitorización. Contradecía el
   propósito de ADR-0004.
2. **Divulgación de información.** El manejador genérico devolvía sin filtrar el
   mensaje de cualquier excepción no controlada, a cualquier cliente. Se comprobó
   que ese mismo camino había expuesto trazas internas de Jackson y de Redis
   durante el diagnóstico del defecto de caché (ADR-0006).

**Corrección aplicada:**

- Manejador propio para `NoResourceFoundException` y `NoHandlerFoundException`
  → 404 con detalle genérico.
- Manejador para `HttpRequestMethodNotSupportedException` → 405.
- El manejador genérico registra la excepción completa en el servidor
  (`log.error`) y devuelve al cliente únicamente `"Error interno del servidor"`.

**Pruebas de regresión** en `GlobalExceptionHandlerTest`:
`rutaInexistenteEs404YNo500`, `metodoNoSoportadoEs405` y
`handleGeneralNoFiltraElMensajeInternoAlCliente`, que envía una excepción cuyo
mensaje contiene una cadena de conexión con credenciales y verifica que no
aparece en la respuesta.

---

## 5. Defecto del instrumento

Las otras tres comprobaciones fallidas de la primera corrida —los tres 401 donde
se esperaba 403 en el bloque API5— **no eran un fallo del sistema, sino del
script de auditoría**.

El script creaba la sesión del bloque de cierre de sesión copiando el archivo de
cookies del estudiante (`cp "$EST" "$LOGOUT"`). Ambos archivos contenían entonces
el **mismo token**. Al revocar ese token en el bloque API2, la lista negra de
`jti` (ADR-0009) invalidaba también la sesión del estudiante, y todo el bloque
API5 —ejecutado después— medía 401 en lugar de 403.

Es decir: **la revocación funcionaba tan bien que rompió la propia auditoría.**

Se corrigió iniciando una tercera sesión independiente para la víctima del
`logout`. Se registra aquí por dos motivos: porque un instrumento de medición
tiene sus propias amenazas a la validez, y porque interpretar aquellos tres 401
como un fallo de autorización habría sido un error de diagnóstico con
consecuencias —se habría "arreglado" algo que no estaba roto.

---

## 6. Lo que esta auditoría **no** cubre

Declararlo es parte del resultado:

- **No es una prueba de penetración.** No hay fuzzing, ni inyección ciega o
  temporal, ni análisis de dependencias (`OWASP Dependency-Check`), ni escaneo
  con ZAP o Burp.
- **No se auditó HTTPS.** Todo se midió sobre HTTP local, por lo que el
  comportamiento de `Secure`, HSTS y la negociación TLS quedan sin verificar.
- **API6 (flujos de negocio sensibles), API7 (SSRF), API9 (gestión de inventario)
  y API10 (consumo inseguro de API de terceros)** no se comprobaron: los tres
  últimos no aplican a esta arquitectura, y API6 requiere modelar el abuso de los
  flujos de préstamo, que no se hizo.
- **La CSP admite `'unsafe-inline'`**, lo que limita su eficacia real frente a XSS.
- **El límite de tasa es por instancia y en memoria** (`RateLimitService` usa un
  `ConcurrentHashMap`). Con varias instancias de la API, el límite efectivo se
  multiplica por el número de réplicas.
- **El límite de tasa distingue por `getRemoteAddr()`**, de modo que tras un proxy
  inverso todas las peticiones compartirían IP.

## 7. Trazabilidad

| Bloque de la auditoría | Requisito | ADR |
|---|---|---|
| API1, API5 | REQ-NF-006 | ADR-0002 |
| API2 (cookie, token) | REQ-NF-002, REQ-F-001 | ADR-0003 |
| API2 (revocación) | REQ-NF-007, REQ-F-003 | ADR-0009 |
| API8 (formato de error) | — | ADR-0004 |
| A03 | REQ-NF-003 | ADR-0005 |
| API8 (CORS) | REQ-NF-002 | ADR-0002 |
