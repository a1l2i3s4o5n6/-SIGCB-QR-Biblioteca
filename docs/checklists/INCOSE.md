# Lista de comprobación — Calidad de requisitos (INCOSE / ISO 29148)

**Referencia:** INCOSE, *Guide to Writing Requirements* (GtWR), y las
características de requisito y de conjunto de requisitos de
ISO/IEC/IEEE 29148:2018, §5.2.
**Ámbito:** `docs/requisitos/SRS.md` v1.0.0 (27 requisitos) y
`docs/trazabilidad/matriz.csv` (35 filas).
**Fecha de cumplimentación:** 3 de septiembre de 2026.
**Estado:** 19 cumplidos, 3 parciales, 2 no cumplidos, sobre 24 ítems.

---

## A. Características de cada requisito (ISO 29148 §5.2.5)

| # | Característica | Estado | Evidencia / qué falta |
|---|---|---|---|
| A1 | **Necesario** — su ausencia dejaría una carencia | `[x]` | Cada requisito lleva campo «Rationale» que justifica su existencia |
| A2 | **Apropiado** — el nivel de detalle corresponde al del sistema | `[x]` | Los enunciados fijan comportamiento observable, no diseño interno |
| A3 | **No ambiguo** — admite una sola interpretación | `[x]` | Enunciados en forma «el sistema deberá…» con condición y respuesta |
| A4 | **Completo** — no requiere información externa | `[~]` | Los funcionales lo son; `REQ-U-001` remite a la escala SUS sin reproducirla |
| A5 | **Singular** — un requisito, una necesidad | `[~]` | `REQ-R-004` mezcla dos afirmaciones: servir desde caché y que el tipo sea deserializable |
| A6 | **Factible** — realizable con la tecnología y los recursos | `[x]` | 22 de 27 están implementados y verificados |
| A7 | **Verificable** — existe un método que decide si se cumple | `[x]` | Cada requisito lleva «Método de verificación» explícito |
| A8 | **Correcto** — describe la necesidad real | `[x]` | Trazado a historia de usuario o caso de uso, salvo los declarados sin trazar |
| A9 | **Conforme** — sigue la plantilla del proyecto | `[x]` | Los 27 usan la misma tabla de nueve atributos |

## B. Características del conjunto (ISO 29148 §5.2.6)

| # | Característica | Estado | Evidencia / qué falta |
|---|---|---|---|
| B1 | **Completo** — cubre todo el alcance declarado | `[x]` | Los 27 identificadores de la matriz coinciden exactamente con los del SRS; el conjunto no tiene huecos |
| B2 | **Consistente** — sin contradicciones internas | `[x]` | No se detectaron requisitos en conflicto |
| B3 | **Asequible** — el conjunto es realizable | `[x]` | 20 de 27 verificados, 7 declarados pendientes |
| B4 | **Delimitado** — el alcance está acotado | `[x]` | §1.2 y §2.4 del SRS fijan alcance y restricciones |

## C. Trazabilidad (ISO 29148 §5.2.8)

| # | Ítem | Estado | Evidencia / qué falta |
|---|---|---|---|
| C1 | Trazabilidad hacia atrás, a la necesidad de origen | `[~]` | 24 de 27 tienen historia o caso de uso; `REQ-F-011`, `REQ-NF-008` y `REQ-NF-009` no, y se declara |
| C2 | Trazabilidad hacia delante, al diseño y al código | `[x]` | Columna `modulo_codigo` y `endpoint_api` en las 35 filas |
| C3 | Trazabilidad a la verificación | `[x]` | Columna `prueba_automatizada`, validada por script |
| C4 | La trazabilidad es comprobable automáticamente | `[x]` | `scripts/validate-traceability.sh`: 97 pruebas descubiertas, 36 filas, 0 errores |
| C5 | Se identifica el tipo de acceso a datos de cada requisito | `[x]` | Columna `tipo_acceso`: CRUD-ORM o SP |

## D. Gestión y priorización

| # | Ítem | Estado | Evidencia / qué falta |
|---|---|---|---|
| D1 | Cada requisito tiene identificador único y estable | `[x]` | Esquema `REQ-{F,NF,R,U}-NNN` |
| D2 | Cada requisito está priorizado | `[x]` | MoSCoW en los 27 |
| D3 | Todos los *Must* están verificados | `[ ]` | **No cumplido.** 3 de 19 sin verificar: `REQ-F-004`, `REQ-F-010`, `REQ-R-001` |
| D4 | El documento está versionado y firmado | `[x]` | SRS v1.0.0, 3 de septiembre de 2026, firmado por los tres integrantes |
| D5 | Los cambios se registran | `[x]` | `docs/requisitos/CHANGELOG-REQ.md` y la nota de cambio en la cabecera del SRS |
| D6 | Las historias de usuario cumplen INVEST | `[ ]` | **No cumplido.** Solo 1 de las 10 historias declara la evaluación INVEST; las otras 9 no |

---

## Lectura de conjunto

El conjunto de requisitos está ahora **cerrado**: el desfase que arrastraba la
entrega anterior —13 requisitos especificados frente a 27 trazados— ha
desaparecido, y los dos conjuntos de identificadores coinciden exactamente. Esa
es la mejora principal, y es comprobable con un `comm` entre ambos ficheros.

Quedan dos incumplimientos claros y uno de ellos importa más que el otro:
**tres requisitos *Must* sin verificar** (D3) es una deficiencia de fondo,
porque un *Must* sin verificación no está priorizado, solo declarado. La
ausencia de evaluación INVEST en nueve historias (D6) es trabajo documental
pendiente y de menor riesgo.
