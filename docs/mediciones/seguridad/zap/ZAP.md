# Análisis dinámico de seguridad con OWASP ZAP — SIGCB-QR

**Fecha de ejecución:** 2026-09-03
**Versión analizada:** `1.0.0-rc` (rama `correcciones-rubrica-entrega-final`)
**Instrumento:** OWASP ZAP en modo *baseline*, dirigido por el plan
[`zap.yaml`](zap.yaml)
**Salida cruda:** [`zap-api.json`](zap-api.json), [`zap-api.html`](zap-api.html),
[`zap-frontend.json`](zap-frontend.json), [`zap-frontend.html`](zap-frontend.html)

Este documento cierra la limitación (ii) que declaraban las entregas anteriores:
«no se ejecutó un análisis dinámico de seguridad con OWASP ZAP». Ya se ejecutó, y
aquí están sus resultados con sus límites declarados.

---

## 1. Qué se ejecutó exactamente, y qué no

El plan de [`zap.yaml`](zap.yaml) encadena cuatro trabajos: `passiveScan-config`,
`spider` (máximo 1 minuto), `passiveScan-wait` y la generación de informes. **No
incluye `activeScan`.**

La distinción no es cosmética y determina cómo deben leerse las cifras:

- El **escaneo pasivo** observa el tráfico que genera la araña y analiza las
  respuestas: cabeceras ausentes, cookies mal marcadas, fugas de versión,
  políticas CSP permisivas. No inyecta cargas.
- El **escaneo activo**, que aquí **no se ejecutó**, sí inyecta cargas para
  provocar SQLi, XSS o recorrido de rutas.

Por tanto, la ausencia de alertas altas **no es prueba de ausencia de
inyección**: es prueba de que nada de lo observable pasivamente la delata. La
defensa frente a SQL dinámico se sustenta en otro instrumento distinto
(`scripts/audit-sql-dynamic.sh`, 0 hallazgos, con autotest del propio
instrumento).

Un segundo límite: la araña recorrió la API **sin autenticar**. De ahí que el
99 % de las respuestas sean 4xx y que ZAP levante la observación «Percentage of
responses with status code 4xx». Eso es exactamente lo que debe ocurrir cuando
un anónimo toca 217 extremos protegidos, y confirma el control de acceso; pero
significa que la superficie autenticada no se analizó.

## 2. Cómo reproducir

```bash
make up      # la API queda en http://localhost:8080 y el frontend en :8000

docker run --rm --network sigcb-qr-biblioteca_default \
  -v "$PWD/docs/mediciones/seguridad/zap":/zap/wrk \
  ghcr.io/zaproxy/zaproxy:stable \
  zap.sh -cmd -autorun /zap/wrk/zap.yaml
```

Los informes se escriben en el directorio montado, en los tres formatos que pide
el plan: HTML para leer, JSON para recalcular y Markdown para citar.

## 3. Resultado — API (`http://api:8080`)

| Nivel de riesgo | Alertas |
|---|---:|
| Alto | **0** |
| Medio | 1 |
| Bajo | 2 |
| Informativo | 3 |

| Alerta | Riesgo | Instancias | Lectura |
|---|---|---:|---|
| Buffer Overflow | Medio | 1 | **Defecto real, ya corregido.** No hay desbordamiento —la JVM no expone memoria manual—, pero la sonda destapó algo cierto: un campo más largo que su columna superaba la validación y reventaba al insertar, con `PSQLException` convertida en 500. Ahora `RegisterRequest` declara los `@Size` que corresponden a los anchos de `V1__schema.sql` y responde 400 |
| A Server Error response code was returned | Bajo | 1 | **Defecto real, ya corregido.** `GET /api/auth/me` caía en el `permitAll` de `/api/auth/**`, de modo que sin token llegaba al controlador, `@AuthenticationPrincipal` valía `null` y el 401 salía como 500. Resuelto en `SecurityConfig`, con `AuthMeEndpointTest` como regresión |
| Cross-Origin-Resource-Policy ausente | Bajo | 2 | Real y corregible: falta la cabecera `Cross-Origin-Resource-Policy` en las respuestas de la API |
| A Client Error response code (4xx) | Informativo | 219 | Esperado: la araña no se autentica y los extremos están protegidos |
| Authentication Request Identified | Informativo | 2 | ZAP reconoce `/api/auth/login` y `/api/auth/register` |
| Non-Storable Content | Informativo | 5 | Las cabeceras de caché que Spring Security añade por defecto marcan las respuestas como no almacenables |

## 4. Resultado — Frontend (`http://frontend`)

| Nivel de riesgo | Alertas |
|---|---:|
| Alto | **0** |
| Medio | 4 |
| Bajo | 8 |
| Informativo | 5 |

Las cuatro alertas de riesgo medio son las que importan, y ninguna es un falso
positivo:

| Alerta | Instancias | Origen |
|---|---:|---|
| CSP: `script-src unsafe-eval` | 3 | Alpine.js evalúa las expresiones de sus directivas (`x-data`, `x-show`) en tiempo de ejecución |
| CSP: `script-src unsafe-inline` | 3 | Mismo origen: los atributos de Alpine son código en línea |
| CSP: `style-src unsafe-inline` | 3 | Estilos en línea generados por Tailwind y por las propias vistas |
| Sub Resource Integrity ausente | 4 | Los recursos servidos desde CDN no llevan atributo `integrity` |

Las tres primeras son el **coste declarado de usar Alpine.js**: eliminarlas exige
sustituir el framework o precompilar sus expresiones, y esa decisión excede el
alcance de esta entrega. Se declara como deuda, no como algo resuelto. La cuarta
—SRI ausente— sí es subsanable sin cambiar de tecnología: basta añadir el
atributo `integrity` a las etiquetas que cargan desde CDN.

De las ocho alertas bajas, dos son fugas de información triviales pero reales
(`X-Powered-By` y `Server` revelan versiones), y `Cookie No HttpOnly Flag`
corresponde a cookies de sesión de Laravel que no transportan el JWT: el token
de autenticación sí viaja en cookie `HttpOnly`, como verifica
`CsrfDefenseTest.cookieDeAccesoLlevaHttpOnly()`.

## 5. Qué se corrigió a raíz de este análisis

Los crudos versionados son **anteriores** a las correcciones: se conservan tal
como los emitió ZAP, y por eso siguen mostrando las dos alertas de la API. Ambas
se investigaron y resultaron ser defectos reales, corregidos en esta misma
entrega con su prueba de regresión:

| Hallazgo | Corrección | Regresión |
|---|---|---|
| 500 en `GET /api/auth/me` sin token | Regla `authenticated()` antes del `permitAll` de `/api/auth/**` en `SecurityConfig` | `AuthMeEndpointTest` |
| 500 al registrar con campos más largos que la columna | `@Size` en `RegisterRequest` alineados con los anchos de `V1__schema.sql` | `RegisterValidationTest` |

Merece subrayarse **cuál de los dos instrumentos encontró esto**: la auditoría
propia (`scripts/owasp-audit.sh`, 51 comprobaciones superadas) no lo vio, porque
sondea rutas cuyo comportamiento correcto ya conoce. ZAP sí, porque recorre lo
que hay y no lo que se espera que haya. Es el argumento a favor de mantener el
análisis dinámico como instrumento distinto y no redundante.

## 6. Qué queda pendiente

1. Ejecutar un **escaneo activo** autenticado, que es el que puede evidenciar
   inyección y recorrido de rutas.
2. Añadir `Cross-Origin-Resource-Policy` en la API y `integrity` en los recursos
   de CDN del frontend.
3. Volver a pasar ZAP tras las correcciones, para archivar un crudo que las
   refleje.

Ninguna de las tres se ha hecho en esta entrega, y por eso figuran aquí en lugar
de en la lista de correcciones.
