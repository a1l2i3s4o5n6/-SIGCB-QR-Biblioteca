# Lista de comprobación — Estándares de reporte empírico (Ralph et al.)

**Referencia:** Ralph, P. et al. *Empirical Standards for Software Engineering
Research*, ACM SIGSOFT, 2021.
**Ámbito:** informe técnico `docs/informe/informe-tecnico.tex`.
**Fecha de cumplimentación:** 3 de septiembre de 2026.
**Estado:** 21 cumplidos, 3 parciales, 4 no cumplidos, sobre 28 ítems.

---

## A. Estándar general (aplica a todo estudio)

| # | Ítem | Estado | Evidencia / qué falta |
|---|---|---|---|
| A1 | Se declara el objetivo del estudio | `[x]` | §1.2 Objetivos |
| A2 | Se formulan preguntas de investigación explícitas | `[x]` | §3.1, RQ1–RQ4 |
| A3 | Se describe la metodología de investigación | `[x]` | §3.2, Design Science Research de Peffers |
| A4 | Se justifica la elección metodológica | `[x]` | §3.2: el artefacto precede a la evaluación |
| A5 | Se describe el objeto de estudio con detalle suficiente | `[x]` | §5 Arquitectura, con los tres diagramas C4 |
| A6 | Se declara el entorno de ejecución de forma reproducible | `[x]` | §3.5: SO, versiones y cinco imágenes ancladas por digest |
| A7 | Los datos crudos se publican | `[x]` | `docs/mediciones/`, incluidos jacoco.csv y los crudos de k6 |
| A8 | Los procedimientos de análisis se publican | `[x]` | `scripts/perf-analysis.py`, `scripts/sus-score.py`, §3.1 de COBERTURA.md |
| A9 | Se declaran las amenazas a la validez en las cuatro categorías | `[x]` | §11, con marco de Wohlin, Cook y Runeson |
| A10 | Se declaran las limitaciones del estudio | `[x]` | §13.7 Limitaciones declaradas |
| A11 | Se declara el conflicto de intereses | `[x]` | §13.2 |
| A12 | Se declara la financiación | `[x]` | §13.3 |
| A13 | Se declara el uso de IA | `[x]` | §13.6 |
| A14 | Se declaran las contribuciones de los autores | `[x]` | §13.1, con roles CRediT |
| A15 | Se declara la disponibilidad de datos y código | `[x]` | §13.4 |
| A16 | Los resultados negativos se informan igual que los positivos | `[x]` | §8.3: la corrida con caché fría fue la más rápida; se concluye contra la hipótesis |

## B. Estándar de experimento / medición

| # | Ítem | Estado | Evidencia / qué falta |
|---|---|---|---|
| B1 | Se describe el diseño experimental | `[~]` | El protocolo de carga está descrito, pero no hay diseño experimental con factores y niveles |
| B2 | Se justifica el tamaño de muestra | `[ ]` | **No cumplido.** No hay cálculo de potencia estadística ni justificación del número de corridas |
| B3 | Se informan estadísticos descriptivos | `[x]` | §8.2: media, p95 e iteraciones de las tres corridas |
| B4 | Se informan intervalos de confianza | `[x]` | IC 95 % del p95 sobre cinco corridas a 50 VU: [4,07, 7,03] ms (t de Student, 4 g.l.). Recalculable con `scripts/analisis-k6-50vu.py` |
| B5 | Se aplica una prueba inferencial adecuada | `[ ]` | **No cumplido**, y por una razón de diseño, no de tamaño muestral: las cinco corridas son del mismo tratamiento, no hay dos condiciones que contrastar |
| B6 | Se informa el tamaño del efecto | `[ ]` | **No cumplido.** No hay efecto entre condiciones que cuantificar; véase B5 |
| B7 | Se declara la semilla aleatoria o el determinismo | `[~]` | El arnés de k6 no usa aleatoriedad, pero no se declara formalmente |
| B8 | Se audita el instrumento de medida antes de usarlo | `[x]` | §8.1: se detectó que el arnés medía el limitador de tasa; el auditor de SQL dinámico lleva autotest de 3/3 y 0 falsos positivos |
| B9 | Se repite la medición un número suficiente de veces | `[x]` | **Cinco** corridas a 50 VU con los cinco crudos conservados, y seis corridas de Lighthouse (3 por perfil) |

## C. Estándar de estudio con participantes

| # | Ítem | Estado | Evidencia / qué falta |
|---|---|---|---|
| C1 | Se describe el instrumento de recogida | `[x]` | `docs/mediciones/usabilidad/SUS.md`; instrumento SUS con cinco casos canónicos de autotest en CI |
| C2 | Se informa el número de participantes | `[~]` | Se informa, y es **cero**. Se declara sin adornos en tres lugares, en vez de inventar una puntuación |
| C3 | Se describe el muestreo | `[ ]` | **No cumplido.** Sin participantes no hubo muestreo que describir. Se cita el marco de Baltes y Ralph pero no se aplica |

---

## Lectura de conjunto

El bloque A se cumple casi por completo: el trabajo está bien declarado, el
entorno es reproducible y los datos crudos se publican.

El bloque B mejoró al repetir las mediciones con el perfil exigido: cinco
corridas a 50 VU permiten ya dar un intervalo de confianza (B4) y satisfacen la
repetición (B9). Lo que sigue sin cumplirse es de **diseño experimental**, no de
tamaño muestral: no hay prueba inferencial (B5) ni tamaño de efecto (B6) porque
no se midió ninguna condición alternativa. Cinco corridas del mismo tratamiento
no se contrastan con nada.

El bloque C sigue vacío por una razón única y declarada: cero participantes.

Ningún ítem no cumplido o parcial se ha marcado como cumplido para mejorar el
recuento.
