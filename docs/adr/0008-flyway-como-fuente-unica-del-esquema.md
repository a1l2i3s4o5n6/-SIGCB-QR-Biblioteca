# ADR-0008: Flyway como fuente única del esquema de base de datos

- **Estado:** Aceptado
- **Fecha:** 2026-08-30
- **Decisores:** Equipo SIGCB-QR
- **Requisitos afectados:** REQ-NF-004

## Contexto

El esquema podía nacer de tres sitios distintos: los scripts sueltos de `db/`
(`schema.sql`, `seed.sql`, `procs/`), la generación automática de Hibernate
(`ddl-auto`) y los cambios aplicados a mano en pgAdmin durante el desarrollo. Con
tres orígenes, la pregunta «¿cuál es el esquema real?» no tiene respuesta, y las
correcciones aplicadas a una base local se pierden en la siguiente.

El problema es concreto en este proyecto: varias correcciones de seguridad son
cambios de base de datos —hashes únicos por usuario, procedimientos sin SQL
dinámico, el monto de multa leído de configuración— y deben aplicarse sí o sí en
cualquier entorno donde se ejecute el sistema.

## Opciones consideradas

1. **`ddl-auto: update` de Hibernate.** Cómodo en desarrollo. No versiona, no
   permite revisar el cambio antes de aplicarlo, no sabe crear procedimientos y
   nunca borra nada: el esquema deriva sin control.
2. **Scripts SQL manuales con orden por convención.** Versionables, pero nada
   garantiza que se apliquen ni en qué orden; el estado de cada entorno depende de
   la memoria de quien lo montó.
3. **Flyway con migraciones versionadas.** El esquema es el resultado determinista
   de aplicar V1…Vn en orden, y la propia base registra hasta dónde llegó.

## Decisión

Se adopta la opción 3.

- Las migraciones viven en `backend/src/main/resources/db/migration/`, nombradas
  `V<n>__<descripcion>.sql`, y se aplican al arrancar la aplicación.
- `spring.jpa.hibernate.ddl-auto` se fija en **`validate`**: Hibernate comprueba
  que las entidades concuerdan con el esquema, pero no lo modifica. Si una entidad
  y su tabla divergen, la aplicación no arranca.
- Toda corrección de esquema es una migración nueva. **Una migración aplicada no
  se edita jamás**: Flyway guarda su suma de comprobación y detectaría el cambio.
- Los archivos de `db/` (`schema.sql`, `seed.sql`, `procs/`) quedan como
  documentación legible y como fuente desde la que se redactan las migraciones. No
  son ejecutables del despliegue.

Estado a la fecha: diez migraciones, V1 a V10. De ellas, V5, V7, V8, V9 y V10 son
correcciones posteriores a la creación inicial, lo que ilustra el motivo de la
decisión: sin migraciones, esas cinco correcciones vivirían solo en la base local
de quien las hizo.

## Consecuencias

### Positivas

- Una base vacía y una base existente convergen al mismo esquema; el entorno de CI
  reconstruye desde cero en cada ejecución (verificado: las diez migraciones se
  aplican en ~1 s antes de las pruebas).
- Los cambios de esquema pasan por revisión de código como cualquier otro cambio.
- `ddl-auto: validate` convierte en fallo de arranque lo que antes era un error
  silencioso en tiempo de ejecución.

### Negativas

- **Corregir un error obliga a añadir una migración**, no a arreglar la anterior.
  El historial se alarga y hay que leerlo entero para saber cómo quedó una tabla.
  Se compensa con `docs/basedatos/DICCIONARIO-DATOS.md`, generado del esquema real.
- **Duplicidad entre `db/` y `db/migration/`**: dos lugares donde vive SQL parecido
  y que pueden desincronizarse. Se asume porque `db/` es material de la asignatura;
  la regla es que ante la duda manda `db/migration/`.
- Flyway no revierte: no hay `down`. Deshacer exige otra migración.
- `V5__corregir_contraseñas.sql` lleva una eñe en el nombre, lo que puede dar
  problemas de codificación en sistemas de archivos mal configurados. Se conserva
  porque renombrar una migración aplicada rompería su suma de comprobación.

## Verificación

- `backend/src/main/resources/application.yml`: `ddl-auto: validate`,
  `flyway.enabled: true`.
- Registro de arranque: `Successfully applied 10 migrations to schema "public",
  now at version v10`.
- La tabla `flyway_schema_history` documenta en cada entorno qué se aplicó y
  cuándo.
