# ADR-0001: Usar ADR para registrar las decisiones de arquitectura

- **Estado:** Aceptado
- **Fecha:** 2026-08-28
- **Decisores:** Equipo SIGCB-QR

## Contexto

El proyecto acumuló decisiones de arquitectura no triviales —dos lenguajes de
servidor, autenticación por cookie, caché externa, procedimientos almacenados—
sin dejar constancia escrita de por qué se tomó cada una. Esto tuvo dos efectos
verificables:

1. En la evaluación de la Tercera Entrega se señaló que el repositorio «mezcla una
   aplicación Laravel/PHP con un backend Spring Boot, sin un ADR que justifique la
   pila», lo que impidió evaluar la coherencia arquitectónica. La decisión existía
   y era razonable (ver ADR-0002), pero no era **legible** desde el repositorio.
2. Dentro del propio equipo, decisiones ya tomadas se volvieron a discutir en
   revisiones posteriores porque nadie recordaba las alternativas descartadas.

Un diagrama C4 muestra *qué* se construyó; no muestra *por qué* se descartó lo
demás. Esa es justamente la información que se pierde primero y la que más cuesta
reconstruir.

## Opciones consideradas

1. **No documentar decisiones.** Coste inmediato nulo. Es el estado que produjo
   la observación del evaluador.
2. **Documentarlas en el README o en el informe técnico.** Un solo documento
   crece, mezcla niveles de detalle y se reescribe, con lo que se pierde el
   histórico: no queda registro de lo que se creía cuando se decidió.
3. **Un ADR por decisión, en formato MADR, versionado junto al código.** Cada
   decisión es un archivo inmutable y numerado; superarla consiste en escribir uno
   nuevo que la reemplace, no en editar el anterior.

## Decisión

Se adopta la opción 3: **un archivo por decisión en `docs/adr/`, formato MADR
simplificado, numeración correlativa e inmutable**.

Se documenta una decisión cuando cumple al menos una de estas condiciones:

- afecta a más de un módulo o a la frontera entre frontend y backend;
- es cara de revertir una vez que hay datos o clientes;
- se eligió entre alternativas razonables y alguien podría preguntar «¿por qué no
  la otra?»;
- restringe algo que el equipo podría querer hacer más adelante.

Las decisiones ya tomadas antes de este ADR se documentan retroactivamente
(ADR-0002 a ADR-0009), indicando la fecha real en que se aplicaron en el código.
Un ADR retroactivo es honesto mientras no reescriba la historia: registra la
decisión tal como se tomó, incluidas las opciones que en su momento no se
evaluaron.

## Consecuencias

### Positivas

- La coherencia arquitectónica pasa a ser auditable desde el repositorio, sin
  necesidad de entrevistar al equipo.
- Las revisiones dejan de reabrir decisiones cerradas: se responde con el enlace
  al ADR o se escribe uno que lo reemplace.
- El informe técnico puede citar los ADR en lugar de duplicar su contenido.

### Negativas

- Cada decisión relevante cuesta entre quince y treinta minutos de redacción.
- Existe el riesgo de que el índice se desactualice respecto a los archivos; se
  mitiga con la comprobación de CI descrita abajo.
- Los ADR retroactivos reconstruyen el razonamiento *a posteriori* y pueden
  resultar más ordenados de lo que fue la decisión real. Se asume el sesgo y se
  declara aquí.

## Verificación

- `docs/adr/` contiene un archivo por fila del índice de `docs/adr/README.md`.
- El trabajo de CI `validate-docs` comprueba que todo `NNNN-*.md` del directorio
  aparece en el índice y que todo enlace del índice apunta a un archivo existente
  (`scripts/validate-adr.sh`).
