# Auditoría Lighthouse del frontend — SIGCB-QR

**Fecha de medición:** 2026-09-01
**Herramienta:** Lighthouse 12.8.2 sobre Chrome *headless*
**Página auditada:** `http://localhost:8000/login`
**Requisito asociado:** REQ-U-002
**Informe completo:** [`lighthouse-login-post-correccion.report.html`](lighthouse-login-post-correccion.report.html)

---

## 1. Cómo reproducir

```bash
make up
export CHROME_PATH="/c/Program Files/Google/Chrome/Application/chrome.exe"   # ruta local
npx -y lighthouse@12 http://localhost:8000/login \
  --output=json --output=html \
  --output-path=./docs/mediciones/frontend/lighthouse-login-post-correccion \
  --chrome-flags="--headless=new --no-sandbox --disable-gpu --disable-dev-shm-usage" \
  --only-categories=performance,accessibility,best-practices,seo --quiet
```

**Configuración de la corrida** (tomada del propio informe, no declarada a mano):
factor de forma `mobile`, estrangulamiento `simulate` (el preajuste por defecto de
Lighthouse: 4G lenta y CPU 4× más lenta). Es deliberadamente pesimista respecto a
un portátil en la red del campus.

Se audita `/login` porque es la única página que no exige sesión. Las páginas
internas comparten el mismo `layouts/app.blade.php`, de modo que las conclusiones
sobre tipografías, iconos y CSP les aplican; las de rendimiento, no
necesariamente, porque cargan tablas y datos del API.

---

## 2. Resultado

Se registran **dos corridas** del mismo día, antes y después de corregir la
política de seguridad de contenido. La comparación es el hallazgo principal de
esta medición.

| Categoría | Corrida 1 (CSP rota) | Corrida 2 (CSP corregida) |
|---|---:|---:|
| Rendimiento | 100 | **82** |
| Accesibilidad | 100 | **100** |
| Buenas prácticas | 93 | **100** |
| SEO | 91 | **91** |

**La cifra válida es la de la corrida 2.** La 1 se conserva porque explica un
defecto y porque ilustra cómo una métrica puede mejorar por el motivo equivocado.

Del informe de la corrida 1 se conservan las cifras y los mensajes de consola
transcritos en §3; el archivo JSON fue sobrescrito por la corrida 2, que es la
que se versiona.

### Métricas de la corrida 2

| Métrica | Valor | Puntuación |
|---|---:|---:|
| First Contentful Paint | 3 577 ms | 0,32 |
| Largest Contentful Paint | 3 577 ms | 0,61 |
| Speed Index | 3 577 ms | 0,87 |
| Total Blocking Time | 0 ms | 1,00 |
| Cumulative Layout Shift | 0 | 1,00 |
| Tiempo de respuesta del servidor | 523 ms | 1,00 |

---

## 3. Defecto encontrado: la CSP bloqueaba los recursos de la propia aplicación

La corrida 1 dio 93 en buenas prácticas por dos auditorías en rojo:
`errors-in-console` e `inspector-issues`. El detalle:

```
Loading the stylesheet 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css'
violates the following Content Security Policy directive: "style-src 'self' 'unsafe-inline'".
The action has been blocked.

Loading the stylesheet 'https://fonts.googleapis.com/css2?family=Poppins:...'
violates the following Content Security Policy directive: "style-src 'self' 'unsafe-inline'".
The action has been blocked.
```

La cabecera CSP añadida en `frontend/docker/000-default.conf` sólo admitía
`'self'`. Pero las plantillas cargan de fuera:

| Recurso | Origen | Usado en |
|---|---|---|
| Font Awesome 6.5.1 | `cdnjs.cloudflare.com` | `layouts/app.blade.php`, `layouts/guest.blade.php` |
| Tipografía Poppins | `fonts.googleapis.com` + `fonts.gstatic.com` | ambos layouts |
| `qrcode.min.js` 1.0.0 | `cdnjs.cloudflare.com` | `qr-codigos/index.blade.php` |

Consecuencias en el navegador, en **todas** las páginas:

1. **Ningún icono se dibujaba.** La interfaz usa `<i class="fas fa-…">` en el
   panel, la navegación lateral y todas las tablas.
2. **La tipografía Poppins no se aplicaba**; se caía a la de reserva.
3. **El módulo de códigos QR quedaba inoperativo**: `script-src 'self'` bloqueaba
   la biblioteca que dibuja el QR.

**El fallo era silencioso.** El servidor respondía 200, el HTML era correcto y
`curl` no mostraba nada anómalo: sólo la consola del navegador lo delataba. Por
eso ninguna prueba automatizada ni la auditoría OWASP lo detectaron. Hizo falta
un navegador real.

### Corrección

Se declararon los tres orígenes exactamente en las directivas donde se usan:

```apache
Header always set Content-Security-Policy "default-src 'self'; \
  script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; \
  style-src  'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com; \
  img-src    'self' data:; \
  font-src   'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com; \
  connect-src 'self'; frame-ancestors 'self'; base-uri 'self'; form-action 'self'"
```

Verificación en la corrida 2: cero errores de consola, las ocho peticiones
externas responden **200**, y buenas prácticas sube de 93 a **100**.

---

## 4. Por qué el rendimiento *bajó* de 100 a 82

Es el resultado más instructivo de esta medición y merece decirse sin rodeos:

> **El 100 de la corrida 1 no era mérito de la aplicación: era el efecto de que el
> navegador bloqueara sus propias hojas de estilo y tipografías.**

Una página que no llega a cargar Font Awesome ni Poppins pinta antes. Al corregir
la CSP, la página hace lo que siempre debió hacer —descargar dos hojas de estilo y
seis archivos de tipografía desde tres dominios externos— y el First Contentful
Paint pasa de 1,4 s a 3,6 s bajo el estrangulamiento móvil de Lighthouse.

Es decir: **la corrida 1 midió una página rota y la premió por ello.** Una métrica
de rendimiento aislada, sin comprobar que la página funciona, puede recompensar
exactamente el defecto que debería denunciar. Se recoge como amenaza a la validez
en el informe técnico.

El 82 es la línea base honesta de la que partir.

---

## 5. Deuda pendiente

| Hallazgo | Efecto | Acción propuesta |
|---|---|---|
| Tipografías e iconos desde CDN externa | Tres dominios en la ruta crítica; FCP 3,6 s; contradice la reproducibilidad que ADR-0007 busca en los contenedores | Alojar Font Awesome, Poppins y `qrcode.js` en el propio contenedor y volver a `'self'` en todas las directivas. Resolvería a la vez el rendimiento y la CSP |
| `'unsafe-inline'` en `script-src` y `style-src` | Debilita la CSP frente a XSS | Extraer los estilos y manejadores en línea de las plantillas Blade; considerar *nonces* |
| Falta `<meta name="description">` | SEO 91 | Añadir la etiqueta en ambos layouts |
| Tiempo de respuesta del servidor: 523 ms | Alto para una página de sesión sin datos | Perfilar el arranque de Laravel; cachear configuración y rutas (`config:cache`, `route:cache`) en la imagen de producción |

## 6. Alcance de esta medición

- Se auditó **una sola página** y **una sola corrida por configuración**: no hay
  intervalo de confianza ni réplicas. Las cifras de rendimiento de Lighthouse
  varían entre ejecuciones, así que el 82 debe leerse como orden de magnitud, no
  como valor exacto.
- No se auditaron las páginas autenticadas, que son las que cargan datos del API
  y donde el rendimiento real de la aplicación se decide.
- La accesibilidad de 100 es la de las comprobaciones **automáticas** de
  Lighthouse, que cubren una fracción de WCAG. No sustituye a una revisión manual
  ni a pruebas con lector de pantalla.
