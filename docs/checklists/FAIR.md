# Lista de comprobación — Principios FAIR

**Referencia:** Wilkinson, M. D. et al. *The FAIR Guiding Principles for
scientific data management and stewardship*. Scientific Data 3, 160018 (2016).
DOI: 10.1038/sdata.2016.18
**Ámbito:** datos de medición de `docs/mediciones/` y metadatos del depósito.
**Fecha de cumplimentación:** 3 de septiembre de 2026.
**Estado:** 10 cumplidos, 2 parciales, 3 no cumplidos, sobre 15 ítems.

---

## F — Findable (hallable)

| # | Principio | Estado | Evidencia / qué falta |
|---|---|---|---|
| F1 | Los datos tienen un identificador único y persistente | `[ ]` | **No cumplido.** No hay DOI: el depósito en Zenodo no se ha publicado. Se declara la ausencia en `CITATION.cff` en vez de inventar un identificador |
| F2 | Los datos se describen con metadatos ricos | `[x]` | `.zenodo.json` y `CITATION.cff` con título, autores, resumen, versión, licencia y 15 palabras clave; `DATA-PROVENANCE.md` describe cada conjunto |
| F3 | Los metadatos incluyen el identificador del dato | `[~]` | Los metadatos apuntan al repositorio, que sí es localizable; falta el DOI |
| F4 | Los datos están indexados en un recurso consultable | `[~]` | El repositorio es público en GitHub y por tanto indexable; no hay depósito en un archivo de datos |

## A — Accessible (accesible)

| # | Principio | Estado | Evidencia / qué falta |
|---|---|---|---|
| A1 | Se recuperan por su identificador mediante un protocolo estándar | `[x]` | HTTPS sobre GitHub; clonado con Git |
| A1.1 | El protocolo es abierto, libre y de implementación universal | `[x]` | Git y HTTPS |
| A1.2 | El protocolo permite autenticación cuando hace falta | `[x]` | No hace falta: el repositorio es público |
| A2 | Los metadatos siguen accesibles aunque el dato desaparezca | `[ ]` | **No cumplido.** Depende de un depósito con metadatos persistentes, que no existe todavía |

## I — Interoperable

| # | Principio | Estado | Evidencia / qué falta |
|---|---|---|---|
| I1 | Se usan formatos y vocabularios formales y compartidos | `[x]` | CSV para JaCoCo y para el SUS; JSON para Lighthouse y para la auditoría de SQL; XML para JaCoCo; CFF 1.2.0 para la cita |
| I2 | Los vocabularios siguen a su vez los principios FAIR | `[x]` | CFF, CRediT, ISO/IEC 25010 e ISO/IEC/IEEE 29148 son estándares publicados |
| I3 | Los datos incluyen referencias cualificadas a otros datos | `[x]` | La matriz de trazabilidad enlaza requisito, código, prueba y fichero de evidencia, y la validación comprueba que los enlaces resuelven |

## R — Reusable (reutilizable)

| # | Principio | Estado | Evidencia / qué falta |
|---|---|---|---|
| R1 | Los datos se describen con atributos precisos y abundantes | `[x]` | `DICCIONARIO-DATOS.md`: 19 tablas y 129 columnas, generado desde el esquema real y con modo de verificación |
| R1.1 | Se publican con una licencia de uso clara y accesible | `[x]` | MIT para el código; CC BY 4.0 para datos y documentación, declarado en `DATA-PROVENANCE.md` y en §13.9 del informe |
| R1.2 | Se asocian a su procedencia detallada | `[x]` | `DATA-PROVENANCE.md`: origen, instrumento, versión, fecha y comando de cada conjunto |
| R1.3 | Cumplen los estándares de su comunidad | `[ ]` | **No cumplido.** No se ha depositado en un repositorio de datos que aplique los estándares de la comunidad ni se ha asignado un esquema de metadatos de dominio |

---

## Lectura de conjunto

Los bloques **I** y **R** se cumplen íntegramente: formatos abiertos,
vocabularios estándar, diccionario generado desde el esquema real, licencia
explícita y procedencia documentada. El bloque **F** es el que falla, y falla
por una única causa que arrastra tres ítems: **no hay DOI**, porque no se ha
publicado la etiqueta `v1.0.0` en Zenodo. Publicar el depósito cierra F1, F3, F4
y A2 de una vez, y es la acción de mayor rendimiento pendiente en esta lista.
