# ADR-0005: CRUD por ORM y agregaciones por procedimientos almacenados

- **Estado:** Aceptado
- **Fecha:** 2026-08-29 (aplicado en el código desde 2026-07)
- **Decisores:** Equipo SIGCB-QR
- **Requisitos afectados:** REQ-F-006, REQ-F-007, REQ-F-008, REQ-F-009, REQ-F-010; observación OBS-06

## Contexto

Hasta la Segunda Entrega, todo el acceso a datos —incluidos los reportes y las
estadísticas del panel— se resolvía con JPQL sobre entidades JPA. La observación
OBS-06 señaló que no existía separación entre el acceso elemental y las consultas
de agregación, y que operaciones con varios pasos (crear un préstamo, devolverlo
generando la multa) se orquestaban desde el servicio, con varias idas y venidas a
la base y sin atomicidad real a nivel de motor.

## Opciones consideradas

1. **Todo por ORM.** Un solo modelo mental y portabilidad entre motores. Para las
   agregaciones obliga a traer filas a la JVM o a escribir JPQL cada vez más
   opaco; para las operaciones de varios pasos, deja la consistencia en manos de
   la transacción de Spring y de varias consultas separadas.
2. **Todo por SQL y procedimientos.** Máximo control, pero se pierde el mapeo, la
   validación y el tipado que el ORM ya da gratis para el CRUD, que es la mayoría
   del código.
3. **Reparto por naturaleza de la operación**: ORM para el CRUD elemental,
   procedimientos y funciones almacenadas para agregaciones y transacciones de
   varios pasos.

## Decisión

Se adopta la opción 3, con un criterio explícito de reparto:

| Va por ORM (Spring Data JPA) | Va por procedimiento o función |
|---|---|
| Alta, baja, modificación y consulta por clave de una entidad | Agregaciones sobre rangos de fechas |
| Listados paginados y filtros sobre una entidad | Reportes con `GROUP BY`, ránkings y totales |
| Navegación por relaciones (con `@EntityGraph`) | Operaciones que tocan varias tablas de forma atómica |

Los objetos de base de datos viven en `db/procs/` y se aplican con Flyway
(ADR-0008). Están catalogados en `docs/basedatos/CATALOGO-SP.md`:

- `sp_crear_prestamo`, `sp_devolver_prestamo`, `sp_renovar_prestamo`
- `sp_dashboard_estadisticas`, `sp_prestamos_entre_fechas`
- `sp_reporte_libros_mas_prestados`, `sp_reporte_multas_cobradas`, `sp_reporte_prestamos_diarios`
- `sp_top_usuarios_prestamos`
- `fn_libros_disponibles`, `fn_contar_prestamos_entre_fechas`, `fn_total_cobrado_entre`, `fn_total_multas_pendientes`

La columna `tipo_acceso` de `docs/trazabilidad/matriz.csv` registra, requisito a
requisito, cuál de las dos vías se usó.

## Consecuencias

### Positivas

- Las agregaciones se resuelven donde están los datos: una llamada en lugar de
  traer filas a la JVM para sumarlas.
- `sp_crear_prestamo` y `sp_devolver_prestamo` hacen atómica una operación que
  antes eran varias consultas: comprobar disponibilidad, insertar, decrementar el
  inventario y, en la devolución, calcular la multa.
- El monto de la multa diaria se lee de la tabla `configuracion` dentro del propio
  procedimiento (V9, V10), de modo que cambiarlo no exige recompilar ni desplegar.

### Negativas

- **La lógica de negocio queda repartida entre dos lenguajes**: Java y PL/pgSQL.
  Un lector debe mirar en ambos sitios para entender una devolución. Es el coste
  principal de esta decisión.
- **El sistema queda atado a PostgreSQL.** Migrar de motor obligaría a reescribir
  los trece objetos de `db/procs/`.
- **Los procedimientos son más difíciles de probar.** Las pruebas unitarias con
  Mockito no los alcanzan; se necesita una base real, lo que explica en parte la
  baja cobertura de rama medida en `docs/mediciones/cobertura/`.
- Una versión temprana de estos procedimientos construía SQL concatenando
  parámetros; el defecto se corrigió en `V8__fix_inyeccion_sql_procedures.sql`.
  Se registra aquí porque ilustra el riesgo que esta decisión introduce: el SQL
  dinámico dentro del motor no lo protege el ORM.

## Verificación

- `db/procs/` contiene los trece objetos; `docs/basedatos/CATALOGO-SP.md` los
  documenta con firma, parámetros y uso.
- Migraciones V4, V8, V9 y V10 en `backend/src/main/resources/db/migration/`.
- `PrestamoServiceTest` cubre las rutas de creación y devolución desde el servicio.
- Auditoría OWASP, bloque A03: cargas de inyección SQL enviadas a los endpoints de
  búsqueda del sistema en marcha se tratan como dato literal.
