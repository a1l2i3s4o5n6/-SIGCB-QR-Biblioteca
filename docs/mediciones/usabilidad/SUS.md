# Evaluación de usabilidad — System Usability Scale (SUS)

**Instrumento:** System Usability Scale (Brooke, 1996), versión española
**Requisito asociado:** REQ-U-001
**Estado:** **instrumento y protocolo listos; sin datos recogidos**
**Herramienta de cálculo:** `scripts/sus-score.py`
**Plantilla de respuestas:** [`sus-respuestas.csv`](sus-respuestas.csv)

---

> ### Estado de esta medición
>
> **No se ha administrado el cuestionario a ningún participante.** Este documento
> contiene el instrumento, el protocolo, las tareas y el procedimiento de cálculo,
> pero **no contiene resultados**, porque no los hay.
>
> Se declara así de forma explícita en cumplimiento de la regla 2 de `ETHICS.md`:
> lo no medido se marca como no medido. Publicar una puntuación SUS plausible sin
> participantes sería inventar evidencia, que es precisamente la observación que
> esta entrega debe corregir. La fila REQ-U-001 de la matriz de trazabilidad
> figura como `pendiente` por el mismo motivo.

---

## 1. Qué mide el SUS y qué no

El SUS es un cuestionario de diez afirmaciones con escala Likert de cinco puntos
que produce una puntuación de 0 a 100. **No es un porcentaje**: 68 se considera
el promedio de la industria, y la escala de adjetivos de Bangor et al. (2009)
sitúa «aceptable» a partir de ~70 y «excelente» por encima de ~85.

Mide la **percepción global de usabilidad**, no la eficacia ni la eficiencia. Un
sistema puede obtener 80 y aun así hacer que la gente tarde el doble en una
tarea. Por eso el protocolo de §4 registra también tasa de éxito y tiempo por
tarea.

## 2. Cuestionario

Escala: 1 = totalmente en desacuerdo … 5 = totalmente de acuerdo.

| # | Afirmación |
|---:|---|
| 1 | Creo que usaría este sistema con frecuencia. |
| 2 | Encuentro este sistema innecesariamente complejo. |
| 3 | Creo que el sistema es fácil de usar. |
| 4 | Creo que necesitaría el apoyo de un técnico para poder usar este sistema. |
| 5 | Encuentro que las distintas funciones del sistema están bien integradas. |
| 6 | Creo que el sistema es demasiado inconsistente. |
| 7 | Imagino que la mayoría de la gente aprendería a usar este sistema muy rápido. |
| 8 | Encuentro el sistema muy incómodo de usar. |
| 9 | Me siento seguro usando el sistema. |
| 10 | Necesité aprender muchas cosas antes de poder empezar a usar el sistema. |

Los impares están redactados en positivo y los pares en negativo, de forma
alternada y deliberada: obliga a leer cada afirmación en lugar de marcar una
columna entera.

## 3. Cálculo

1. Ítems **impares**: puntuación − 1.
2. Ítems **pares**: 5 − puntuación.
3. Sumar las diez contribuciones (rango 0–40) y multiplicar por 2,5 (rango 0–100).

Se informará, sobre la muestra: media, desviación típica, mediana, mínimo,
máximo e intervalo de confianza al 95 % de la media. `scripts/sus-score.py`
calcula todo eso a partir del CSV de respuestas y rechaza filas incompletas o con
valores fuera del rango 1–5.

## 4. Protocolo previsto

**Participantes.** 12 personas, en tres perfiles según los roles del sistema
(`ETHICS.md`, §4):

| Perfil | Participantes | Tareas |
|---|---:|---|
| Estudiante | 6 | T1, T2, T3 |
| Bibliotecario | 4 | T1, T4, T5, T6 |
| Administrador | 2 | T1, T7, T8 |

Doce es un tamaño **pequeño**: suficiente para detectar problemas graves de
usabilidad, insuficiente para una estimación estrecha de la media. Se declarará
así al informar, con el intervalo de confianza a la vista.

**Tareas.**

| Id | Tarea | Éxito = |
|---|---|---|
| T1 | Iniciar sesión y localizar el panel | Ve el panel con sus cifras |
| T2 | Buscar un libro por título y comprobar su disponibilidad | Identifica los ejemplares disponibles |
| T3 | Reservar un libro sin ejemplares libres | La reserva aparece en su listado |
| T4 | Registrar un préstamo a un estudiante | El préstamo consta como activo |
| T5 | Registrar la devolución de un préstamo vencido | Se genera la multa correspondiente |
| T6 | Consultar el informe de préstamos diarios | Obtiene el informe del periodo pedido |
| T7 | Dar de alta un usuario con rol bibliotecario | El usuario puede iniciar sesión |
| T8 | Consultar la auditoría y filtrar por acción | Ve sólo las acciones filtradas |

**Procedimiento.**

1. Sistema recién levantado con `make up` y datos de la semilla, idéntico para
   todos los participantes.
2. Explicación del propósito y consentimiento verbal. No se recogen datos
   personales: cada respuesta se identifica con `P01`…`P12`.
3. El participante ejecuta sus tareas **sin ayuda**; se registra éxito o abandono
   y el tiempo empleado.
4. El SUS se administra **al terminar todas las tareas**, no entre ellas.
5. Comentarios en abierto al final, transcritos sin atribución.

**Sesgos que el protocolo intenta contener**, y que se declararán igualmente
como amenazas a la validez:

- *Sesgo de cortesía*: si el evaluador es autor del sistema, los participantes
  tienden a puntuar más alto. Se mitiga con administración escrita y anónima del
  cuestionario, pero no se elimina.
- *Muestra de conveniencia*: los participantes serán compañeros de curso, más
  competentes con la tecnología que la población real de la biblioteca.
- *Efecto de orden*: las tareas se presentan siempre en el mismo orden, así que el
  aprendizaje acumulado favorece a las últimas.

## 5. Recogida y cálculo

Las respuestas se anotan en `sus-respuestas.csv`, una fila por participante:

```csv
participante,perfil,p1,p2,p3,p4,p5,p6,p7,p8,p9,p10
P01,estudiante,4,2,5,1,4,2,5,1,4,2
```

Y se procesan con:

```bash
python scripts/sus-score.py docs/mediciones/usabilidad/sus-respuestas.csv
```

El script se ha verificado con el ejemplo canónico de la literatura: un
cuestionario con todos los impares a 5 y todos los pares a 1 debe dar exactamente
100, y el inverso, 0. Esa comprobación se ejecuta con `--autotest`.

## 6. Resultados

_Pendiente de la administración del cuestionario._

Cuando se recojan, esta sección incluirá: tabla de puntuaciones individuales,
media con intervalo de confianza al 95 %, desviación típica, distribución por
perfil, tasa de éxito y tiempo por tarea, y los comentarios en abierto, incluidos
los desfavorables.

## 7. Referencias

- Brooke, J. (1996). *SUS: A quick and dirty usability scale.* En P. W. Jordan et
  al. (eds.), *Usability Evaluation in Industry* (pp. 189–194). Taylor & Francis.
- Bangor, A., Kortum, P. y Miller, J. (2009). *Determining what individual SUS
  scores mean: adding an adjective rating scale.* Journal of Usability Studies,
  4(3), 114–123.
- Lewis, J. R. (2018). *The System Usability Scale: past, present, and future.*
  International Journal of Human–Computer Interaction, 34(7), 577–590.
