# Lighthouse — seis corridas (tres de escritorio, tres de móvil)

**Fecha de medición:** 2026-09-03
**Requisito asociado:** REQ-U-002
**Herramienta:** Lighthouse 13.4.1 (imagen `femtopixel/google-lighthouse`)
**Página auditada:** `/login`, servida por el contenedor del frontend
**Crudos:** `lh-desktop-run{1,2,3}` y `lh-mobile-run{1,2,3}`, en `.report.json`
y `.report.html`, en este mismo directorio.

---

## 1. Qué corrige esta medición

La medición anterior tenía **una sola corrida y solo de móvil**, y su propio
documento admitía que el JSON de la primera se había sobrescrito. La guía exige
**tres corridas por perfil**, escritorio incluido. Aquí están las seis, con su
crudo cada una.

Reproducción de una corrida:

```bash
docker run --rm --shm-size=1g --network sigcb-qr-biblioteca_default \
  -v "$PWD/docs/mediciones/frontend/lh":/home/chrome/reports \
  --entrypoint lighthouse femtopixel/google-lighthouse \
  http://frontend/login --preset=desktop \
  --output=json --output=html --output-path=/home/chrome/reports/lh-desktop-run1 \
  --chrome-flags="--headless=new --no-sandbox --disable-gpu --disable-dev-shm-usage"
```

`--shm-size=1g` no es decorativo: sin él Chrome se queda sin memoria compartida,
aborta con `TARGET_CRASHED` y **Lighthouse escribe igualmente un informe con
todas las puntuaciones a `null`**. Un informe así parece válido a simple vista.
Conviene comprobar siempre el campo `runtimeError` antes de dar por buena una
corrida.

## 2. Resultados

| Corrida | Rendimiento | Accesibilidad | Buenas prácticas | SEO |
|---|---:|---:|---:|---:|
| desktop-run1 | 96 | 98 | **78** | 91 |
| desktop-run2 | 97 | 98 | **78** | 91 |
| desktop-run3 | 97 | 98 | **78** | 91 |
| mobile-run1 | 89 | 98 | **78** | 91 |
| mobile-run2 | 85 | 98 | **78** | 91 |
| mobile-run3 | 89 | 98 | **78** | 91 |
| **Mediana escritorio** | **97** | **98** | **78** | **91** |
| **Mediana móvil** | **89** | **98** | **78** | **91** |

## 3. Un umbral que NO se cumple

`REQ-U-002` exige al menos 80 en rendimiento, 90 en accesibilidad, 90 en buenas
prácticas y 85 en SEO. **Buenas prácticas da 78 en las seis corridas y por tanto
el requisito no se cumple.**

La causa está identificada y es única. Las dos auditorías que fallan son:

| Auditoría | Resultado |
|---|---|
| `is-on-https` | Falla: la página se sirve por HTTP |
| `redirects-http` | Falla: no hay redirección de HTTP a HTTPS |

**Ninguna otra auditoría de la categoría falla.** Las dos son consecuencia de
medir contra un contenedor local sin TLS, no de un defecto de la aplicación.

Ahora bien, esto no permite declarar el requisito cumplido, y no se declara.
Sin un despliegue con TLS no hay forma de comprobar que la puntuación subiría, y
afirmarlo sería sustituir una medición por una conjetura. El requisito queda
como **no cumplido**, con la causa documentada. Se cerrará cuando exista
despliegue con certificado, que es la misma condición que cierra la carencia de
HSTS señalada en la auditoría de seguridad.

## 4. Diferencias con la medición anterior

La corrida anterior daba 82 de rendimiento, 100 de accesibilidad, 100 de buenas
prácticas y 91 de SEO, con Lighthouse 12.8.2 contra `localhost`.

Las diferencias respecto de aquellas cifras son reales y tienen explicación:

- **Buenas prácticas, 100 → 78.** La medición anterior se hizo contra
  `http://localhost`, y Lighthouse **exime a `localhost`** de la comprobación de
  HTTPS. Aquí se audita `http://frontend`, un nombre de host de la red Docker,
  que no está exento. El 100 anterior era, en este punto, un artefacto de haber
  medido contra `localhost`.
- **Accesibilidad, 100 → 98.** Lighthouse 13 incorpora comprobaciones que la 12
  no tenía. No es una regresión de la aplicación, sino un instrumento más
  exigente.
- **Rendimiento, 82 → 97 (escritorio) y 89 (móvil).** La corrida anterior no
  declaraba perfil; era móvil. La cifra móvil actual, 89, es superior a la
  anterior, 82.

La lectura honesta es que **la medición anterior era más favorable en parte
porque el entorno la favorecía**, no solo porque la aplicación fuese mejor. Se
sustituyen las cifras publicadas por estas, que son menos halagüeñas y más
comparables con un despliegue real.
