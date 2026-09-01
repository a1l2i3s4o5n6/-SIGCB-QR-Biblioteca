# Diccionario de datos — SIGCB-QR

> **Generado automáticamente** por `scripts/generar-diccionario-datos.py` a
> partir del esquema real de la base en marcha. No editar a mano: los cambios
> se pierden en la siguiente generación. Para modificar el esquema, añadir una
> migración de Flyway (ADR-0008) y volver a ejecutar el generador.

- **Generado el:** 2026-09-01 20:56 UTC
- **Motor:** PostgreSQL 16.15 (Debian 16.15-1.pgdg13+2) on x86_64-pc-linux-gnu
- **Base:** `sigcbqr`, esquema `public`
- **Tablas de dominio:** 19 (se excluye `flyway_schema_history`)
- **Origen del esquema:** migraciones `V1`–`V10` en `backend/src/main/resources/db/migration/`

Los recuentos de filas corresponden al conjunto de datos **sintético** de la
semilla (`V3__datos_semilla.sql`). No hay datos de personas reales; ver
`ETHICS.md`.

## Índice de tablas

| Tabla | Propósito | Columnas | Filas |
|---|---|---:|---:|
| [`auditoria`](#auditoria) | Registro de acciones sobre el sistema: quién, qué, cuándo y desde dónde. | 9 | 24 |
| [`autores`](#autores) | Autores de las obras. Relación N:M con libros. | 6 | 10 |
| [`carreras`](#carreras) | Programas académicos, cada uno adscrito a una facultad. | 6 | 8 |
| [`categorias`](#categorias) | Clasificación temática del acervo. | 5 | 5 |
| [`configuracion`](#configuracion) | Parámetros del sistema editables sin desplegar (p. ej. monto de multa diaria). | 6 | 5 |
| [`editoriales`](#editoriales) | Sellos editoriales de las obras. | 5 | 7 |
| [`facultades`](#facultades) | Unidades académicas de la universidad. | 5 | 5 |
| [`inventario`](#inventario) | Ejemplar físico concreto de un libro. Es lo que se presta. | 6 | 69 |
| [`jwt_blacklist`](#jwt_blacklist) | Identificadores (jti) de tokens revocados en el cierre de sesión (ADR-0009). | 4 | 4 |
| [`libro_autores`](#libro_autores) | Tabla puente entre libros y autores. | 2 | 13 |
| [`libros`](#libros) | Obra bibliográfica como título (la obra, no el ejemplar físico). | 15 | 20 |
| [`multas`](#multas) | Sanción económica derivada de una devolución tardía. | 8 | 3 |
| [`notificaciones`](#notificaciones) | Avisos dirigidos a un usuario (vencimientos, reservas disponibles). | 7 | 3 |
| [`prestamos`](#prestamos) | Entrega de un ejemplar a un usuario, con fecha de vencimiento y devolución. | 10 | 10 |
| [`qr_codigos`](#qr_codigos) | Código QR asociado a un libro para su identificación rápida. | 6 | 5 |
| [`reservas`](#reservas) | Solicitud anticipada de un libro cuando no hay ejemplar disponible. | 7 | 5 |
| [`roles`](#roles) | Perfiles de autorización. Determinan a qué endpoints puede llamar un usuario. | 4 | 3 |
| [`sanciones`](#sanciones) | Restricción no económica aplicada a un usuario (suspensión temporal). | 8 | 0 |
| [`usuarios`](#usuarios) | Personas que usan el sistema: estudiantes, bibliotecarios y administradores. | 10 | 6 |

## Convenciones

- Toda tabla de dominio usa una clave primaria sustituta `id BIGSERIAL`.
- Las marcas temporales `created_at` / `updated_at` son `timestamp` sin zona,
  con `CURRENT_TIMESTAMP` por defecto; la aplicación trabaja en la zona
  `America/Guayaquil`.
- Las bajas son **lógicas**: la columna `activo` se pone en `false` y la fila
  se conserva, para no romper el historial de préstamos.
- Los nombres de tabla van en plural y en minúsculas; los de columna, en
  `snake_case`.

## Datos personales

Columnas con implicaciones de privacidad, según `ETHICS.md`:

| Tabla | Columna | Consideración |
|---|---|---|
| `auditoria` | `equipo` | Agente de usuario del cliente. Contribuye a la huella del navegador. |
| `auditoria` | `ip` | Dirección IP del cliente. Dato personal; sin cifrado en reposo. |
| `prestamos` | `usuario_id` | Vincula a una persona con lo que lee. Alta sensibilidad. |
| `sanciones` | `motivo` | Texto libre sobre la conducta de una persona. |
| `usuarios` | `email` | Dato personal identificativo. Es además la credencial de acceso. |
| `usuarios` | `password` | Hash bcrypt con salt único. Nunca se devuelve por la API. |
| `usuarios` | `telefono` | Dato personal de contacto. |

---

## `auditoria`

Registro de acciones sobre el sistema: quién, qué, cuándo y desde dónde.

**Clave primaria:** `PRIMARY KEY (id)`

| # | Columna | Tipo | Nulo | Por defecto | Notas |
|---:|---|---|:---:|---|---|
| 1 | `id` | `bigint` | no | autoincremental |  |
| 2 | `usuario_id` | `bigint` | sí | — |  |
| 3 | `accion` | `character varying(100)` | no | — |  |
| 4 | `entidad` | `character varying(100)` | sí | — |  |
| 5 | `entidad_id` | `bigint` | sí | — |  |
| 6 | `detalle` | `text` | sí | — |  |
| 7 | `ip` | `character varying(50)` | sí | — | Dirección IP del cliente. Dato personal; sin cifrado en reposo. |
| 8 | `equipo` | `character varying(255)` | sí | — | Agente de usuario del cliente. Contribuye a la huella del navegador. |
| 9 | `created_at` | `timestamp` | sí | `CURRENT_TIMESTAMP` |  |

**Claves foráneas**

- `fk_auditoria_usuario`: FOREIGN KEY (usuario_id) REFERENCES usuarios(id)

**Índices**

- `idx_auditoria_fecha`: `btree (created_at)`
- `idx_auditoria_usuario`: `btree (usuario_id)`

---

## `autores`

Autores de las obras. Relación N:M con libros.

**Clave primaria:** `PRIMARY KEY (id)`

| # | Columna | Tipo | Nulo | Por defecto | Notas |
|---:|---|---|:---:|---|---|
| 1 | `id` | `bigint` | no | autoincremental |  |
| 2 | `nombre` | `character varying(100)` | no | — |  |
| 3 | `apellido` | `character varying(100)` | no | — |  |
| 4 | `nacionalidad` | `character varying(100)` | sí | — |  |
| 5 | `activo` | `boolean` | sí | `true` |  |
| 6 | `created_at` | `timestamp` | sí | `CURRENT_TIMESTAMP` |  |

---

## `carreras`

Programas académicos, cada uno adscrito a una facultad.

**Clave primaria:** `PRIMARY KEY (id)`

| # | Columna | Tipo | Nulo | Por defecto | Notas |
|---:|---|---|:---:|---|---|
| 1 | `id` | `bigint` | no | autoincremental |  |
| 2 | `nombre` | `character varying(200)` | no | — |  |
| 3 | `codigo` | `character varying(20)` | sí | — |  |
| 4 | `facultad_id` | `bigint` | no | — |  |
| 5 | `activo` | `boolean` | sí | `true` |  |
| 6 | `created_at` | `timestamp` | sí | `CURRENT_TIMESTAMP` |  |

**Claves foráneas**

- `fk_carrera_facultad`: FOREIGN KEY (facultad_id) REFERENCES facultades(id)

**Restricciones de unicidad y comprobación**

- `carreras_codigo_key`: UNIQUE (codigo)

**Índices**

- `carreras_codigo_key`: `btree (codigo)`

---

## `categorias`

Clasificación temática del acervo.

**Clave primaria:** `PRIMARY KEY (id)`

| # | Columna | Tipo | Nulo | Por defecto | Notas |
|---:|---|---|:---:|---|---|
| 1 | `id` | `bigint` | no | autoincremental |  |
| 2 | `nombre` | `character varying(100)` | no | — |  |
| 3 | `descripcion` | `character varying(255)` | sí | — |  |
| 4 | `activo` | `boolean` | sí | `true` |  |
| 5 | `created_at` | `timestamp` | sí | `CURRENT_TIMESTAMP` |  |

**Restricciones de unicidad y comprobación**

- `categorias_nombre_key`: UNIQUE (nombre)

**Índices**

- `categorias_nombre_key`: `btree (nombre)`

---

## `configuracion`

Parámetros del sistema editables sin desplegar (p. ej. monto de multa diaria).

**Clave primaria:** `PRIMARY KEY (id)`

| # | Columna | Tipo | Nulo | Por defecto | Notas |
|---:|---|---|:---:|---|---|
| 1 | `id` | `bigint` | no | autoincremental |  |
| 2 | `clave` | `character varying(100)` | no | — |  |
| 3 | `valor` | `character varying(500)` | no | — |  |
| 4 | `descripcion` | `character varying(255)` | sí | — |  |
| 5 | `created_at` | `timestamp` | sí | `CURRENT_TIMESTAMP` |  |
| 6 | `updated_at` | `timestamp` | sí | `CURRENT_TIMESTAMP` |  |

**Restricciones de unicidad y comprobación**

- `configuracion_clave_key`: UNIQUE (clave)

**Índices**

- `configuracion_clave_key`: `btree (clave)`

---

## `editoriales`

Sellos editoriales de las obras.

**Clave primaria:** `PRIMARY KEY (id)`

| # | Columna | Tipo | Nulo | Por defecto | Notas |
|---:|---|---|:---:|---|---|
| 1 | `id` | `bigint` | no | autoincremental |  |
| 2 | `nombre` | `character varying(200)` | no | — |  |
| 3 | `pais` | `character varying(100)` | sí | — |  |
| 4 | `activo` | `boolean` | sí | `true` |  |
| 5 | `created_at` | `timestamp` | sí | `CURRENT_TIMESTAMP` |  |

---

## `facultades`

Unidades académicas de la universidad.

**Clave primaria:** `PRIMARY KEY (id)`

| # | Columna | Tipo | Nulo | Por defecto | Notas |
|---:|---|---|:---:|---|---|
| 1 | `id` | `bigint` | no | autoincremental |  |
| 2 | `nombre` | `character varying(200)` | no | — |  |
| 3 | `codigo` | `character varying(20)` | sí | — |  |
| 4 | `activo` | `boolean` | sí | `true` |  |
| 5 | `created_at` | `timestamp` | sí | `CURRENT_TIMESTAMP` |  |

**Restricciones de unicidad y comprobación**

- `facultades_codigo_key`: UNIQUE (codigo)

**Índices**

- `facultades_codigo_key`: `btree (codigo)`

---

## `inventario`

Ejemplar físico concreto de un libro. Es lo que se presta.

**Clave primaria:** `PRIMARY KEY (id)`

| # | Columna | Tipo | Nulo | Por defecto | Notas |
|---:|---|---|:---:|---|---|
| 1 | `id` | `bigint` | no | autoincremental |  |
| 2 | `libro_id` | `bigint` | no | — |  |
| 3 | `codigo_ejemplar` | `character varying(50)` | no | — |  |
| 4 | `estado` | `character varying(30)` | sí | `'DISPONIBLE'::character varying` |  |
| 5 | `ubicacion_estante` | `character varying(50)` | sí | — |  |
| 6 | `fecha_registro` | `timestamp` | sí | `CURRENT_TIMESTAMP` |  |

**Claves foráneas**

- `fk_inventario_libro`: FOREIGN KEY (libro_id) REFERENCES libros(id)

**Restricciones de unicidad y comprobación**

- `inventario_codigo_ejemplar_key`: UNIQUE (codigo_ejemplar)
- `chk_estado_inventario`: CHECK (((estado)::text = ANY ((ARRAY['DISPONIBLE'::character varying, 'PRESTADO'::character varying, 'DANADO'::character varying, 'PERDIDO'::character varying, 'REPARACION'::character varying])::text[])))

**Índices**

- `idx_inventario_estado`: `btree (estado)`
- `idx_inventario_libro`: `btree (libro_id)`
- `inventario_codigo_ejemplar_key`: `btree (codigo_ejemplar)`

---

## `jwt_blacklist`

Identificadores (jti) de tokens revocados en el cierre de sesión (ADR-0009).

**Clave primaria:** `PRIMARY KEY (id)`

| # | Columna | Tipo | Nulo | Por defecto | Notas |
|---:|---|---|:---:|---|---|
| 1 | `id` | `bigint` | no | autoincremental |  |
| 2 | `jti` | `character varying(255)` | no | — |  |
| 3 | `fecha_expiracion` | `timestamp` | no | — |  |
| 4 | `created_at` | `timestamp` | sí | `CURRENT_TIMESTAMP` |  |

**Restricciones de unicidad y comprobación**

- `jwt_blacklist_jti_key`: UNIQUE (jti)

**Índices**

- `idx_jwt_blacklist_expiracion`: `btree (fecha_expiracion)`
- `idx_jwt_blacklist_jti`: `btree (jti)`
- `jwt_blacklist_jti_key`: `btree (jti)`

---

## `libro_autores`

Tabla puente entre libros y autores.

**Clave primaria:** `PRIMARY KEY (libro_id, autor_id)`

| # | Columna | Tipo | Nulo | Por defecto | Notas |
|---:|---|---|:---:|---|---|
| 1 | `libro_id` | `bigint` | no | — |  |
| 2 | `autor_id` | `bigint` | no | — |  |

**Claves foráneas**

- `fk_la_autor`: FOREIGN KEY (autor_id) REFERENCES autores(id) ON DELETE CASCADE
- `fk_la_libro`: FOREIGN KEY (libro_id) REFERENCES libros(id) ON DELETE CASCADE

---

## `libros`

Obra bibliográfica como título (la obra, no el ejemplar físico).

**Clave primaria:** `PRIMARY KEY (id)`

| # | Columna | Tipo | Nulo | Por defecto | Notas |
|---:|---|---|:---:|---|---|
| 1 | `id` | `bigint` | no | autoincremental |  |
| 2 | `titulo` | `character varying(300)` | no | — |  |
| 3 | `isbn` | `character varying(20)` | sí | — |  |
| 4 | `anio_publicacion` | `integer` | sí | — |  |
| 5 | `edicion` | `character varying(50)` | sí | — |  |
| 6 | `ejemplares_totales` | `integer` | sí | `1` |  |
| 7 | `ejemplares_disponibles` | `integer` | sí | `1` |  |
| 8 | `ubicacion` | `character varying(100)` | sí | — |  |
| 9 | `descripcion` | `text` | sí | — |  |
| 10 | `portada` | `character varying(255)` | sí | — |  |
| 11 | `categoria_id` | `bigint` | sí | — |  |
| 12 | `editorial_id` | `bigint` | sí | — |  |
| 13 | `activo` | `boolean` | sí | `true` |  |
| 14 | `created_at` | `timestamp` | sí | `CURRENT_TIMESTAMP` |  |
| 15 | `updated_at` | `timestamp` | sí | `CURRENT_TIMESTAMP` |  |

**Claves foráneas**

- `fk_libro_categoria`: FOREIGN KEY (categoria_id) REFERENCES categorias(id)
- `fk_libro_editorial`: FOREIGN KEY (editorial_id) REFERENCES editoriales(id)

**Restricciones de unicidad y comprobación**

- `libros_isbn_key`: UNIQUE (isbn)

**Índices**

- `idx_libros_categoria`: `btree (categoria_id)`
- `idx_libros_isbn`: `btree (isbn)`
- `idx_libros_titulo`: `btree (titulo)`
- `libros_isbn_key`: `btree (isbn)`

---

## `multas`

Sanción económica derivada de una devolución tardía.

**Clave primaria:** `PRIMARY KEY (id)`

| # | Columna | Tipo | Nulo | Por defecto | Notas |
|---:|---|---|:---:|---|---|
| 1 | `id` | `bigint` | no | autoincremental |  |
| 2 | `prestamo_id` | `bigint` | no | — |  |
| 3 | `usuario_id` | `bigint` | no | — |  |
| 4 | `monto` | `numeric(10,2)` | no | — |  |
| 5 | `pagada` | `boolean` | sí | `false` |  |
| 6 | `fecha_pago` | `timestamp` | sí | — |  |
| 7 | `concepto` | `character varying(255)` | sí | — |  |
| 8 | `created_at` | `timestamp` | sí | `CURRENT_TIMESTAMP` |  |

**Claves foráneas**

- `fk_multa_prestamo`: FOREIGN KEY (prestamo_id) REFERENCES prestamos(id)
- `fk_multa_usuario`: FOREIGN KEY (usuario_id) REFERENCES usuarios(id)

**Índices**

- `idx_multas_pagada`: `btree (pagada)`
- `idx_multas_usuario`: `btree (usuario_id)`

---

## `notificaciones`

Avisos dirigidos a un usuario (vencimientos, reservas disponibles).

**Clave primaria:** `PRIMARY KEY (id)`

| # | Columna | Tipo | Nulo | Por defecto | Notas |
|---:|---|---|:---:|---|---|
| 1 | `id` | `bigint` | no | autoincremental |  |
| 2 | `usuario_id` | `bigint` | no | — |  |
| 3 | `titulo` | `character varying(200)` | no | — |  |
| 4 | `mensaje` | `text` | no | — |  |
| 5 | `leida` | `boolean` | sí | `false` |  |
| 6 | `tipo` | `character varying(50)` | sí | `'INFO'::character varying` |  |
| 7 | `created_at` | `timestamp` | sí | `CURRENT_TIMESTAMP` |  |

**Claves foráneas**

- `fk_notificacion_usuario`: FOREIGN KEY (usuario_id) REFERENCES usuarios(id)

**Índices**

- `idx_notificaciones_leida`: `btree (leida)`
- `idx_notificaciones_usuario`: `btree (usuario_id)`

---

## `prestamos`

Entrega de un ejemplar a un usuario, con fecha de vencimiento y devolución.

**Clave primaria:** `PRIMARY KEY (id)`

| # | Columna | Tipo | Nulo | Por defecto | Notas |
|---:|---|---|:---:|---|---|
| 1 | `id` | `bigint` | no | autoincremental |  |
| 2 | `usuario_id` | `bigint` | no | — | Vincula a una persona con lo que lee. Alta sensibilidad. |
| 3 | `inventario_id` | `bigint` | no | — |  |
| 4 | `fecha_prestamo` | `timestamp` | sí | `CURRENT_TIMESTAMP` |  |
| 5 | `fecha_vencimiento` | `timestamp` | no | — |  |
| 6 | `fecha_devolucion` | `timestamp` | sí | — |  |
| 7 | `estado` | `character varying(30)` | sí | `'ACTIVO'::character varying` |  |
| 8 | `observaciones` | `text` | sí | — |  |
| 9 | `created_at` | `timestamp` | sí | `CURRENT_TIMESTAMP` |  |
| 10 | `updated_at` | `timestamp` | sí | `CURRENT_TIMESTAMP` |  |

**Claves foráneas**

- `fk_prestamo_inventario`: FOREIGN KEY (inventario_id) REFERENCES inventario(id)
- `fk_prestamo_usuario`: FOREIGN KEY (usuario_id) REFERENCES usuarios(id)

**Restricciones de unicidad y comprobación**

- `chk_estado_prestamo`: CHECK (((estado)::text = ANY ((ARRAY['ACTIVO'::character varying, 'DEVUELTO'::character varying, 'VENCIDO'::character varying, 'RENOVADO'::character varying])::text[])))

**Índices**

- `idx_prestamos_estado`: `btree (estado)`
- `idx_prestamos_fecha`: `btree (fecha_prestamo)`
- `idx_prestamos_usuario`: `btree (usuario_id)`

---

## `qr_codigos`

Código QR asociado a un libro para su identificación rápida.

**Clave primaria:** `PRIMARY KEY (id)`

| # | Columna | Tipo | Nulo | Por defecto | Notas |
|---:|---|---|:---:|---|---|
| 1 | `id` | `bigint` | no | autoincremental |  |
| 2 | `libro_id` | `bigint` | no | — |  |
| 3 | `codigo` | `character varying(255)` | no | — |  |
| 4 | `imagen_url` | `character varying(500)` | sí | — |  |
| 5 | `activo` | `boolean` | sí | `true` |  |
| 6 | `created_at` | `timestamp` | sí | `CURRENT_TIMESTAMP` |  |

**Claves foráneas**

- `fk_qr_libro`: FOREIGN KEY (libro_id) REFERENCES libros(id)

**Restricciones de unicidad y comprobación**

- `qr_codigos_codigo_key`: UNIQUE (codigo)

**Índices**

- `idx_qr_libro`: `btree (libro_id)`
- `qr_codigos_codigo_key`: `btree (codigo)`

---

## `reservas`

Solicitud anticipada de un libro cuando no hay ejemplar disponible.

**Clave primaria:** `PRIMARY KEY (id)`

| # | Columna | Tipo | Nulo | Por defecto | Notas |
|---:|---|---|:---:|---|---|
| 1 | `id` | `bigint` | no | autoincremental |  |
| 2 | `usuario_id` | `bigint` | no | — |  |
| 3 | `libro_id` | `bigint` | no | — |  |
| 4 | `fecha_reserva` | `timestamp` | sí | `CURRENT_TIMESTAMP` |  |
| 5 | `fecha_vencimiento` | `timestamp` | sí | — |  |
| 6 | `estado` | `character varying(30)` | sí | `'PENDIENTE'::character varying` |  |
| 7 | `created_at` | `timestamp` | sí | `CURRENT_TIMESTAMP` |  |

**Claves foráneas**

- `fk_reserva_libro`: FOREIGN KEY (libro_id) REFERENCES libros(id)
- `fk_reserva_usuario`: FOREIGN KEY (usuario_id) REFERENCES usuarios(id)

**Restricciones de unicidad y comprobación**

- `chk_estado_reserva`: CHECK (((estado)::text = ANY ((ARRAY['PENDIENTE'::character varying, 'CONFIRMADA'::character varying, 'CANCELADA'::character varying, 'COMPLETADA'::character varying])::text[])))

**Índices**

- `idx_reservas_estado`: `btree (estado)`
- `idx_reservas_usuario`: `btree (usuario_id)`

---

## `roles`

Perfiles de autorización. Determinan a qué endpoints puede llamar un usuario.

**Clave primaria:** `PRIMARY KEY (id)`

| # | Columna | Tipo | Nulo | Por defecto | Notas |
|---:|---|---|:---:|---|---|
| 1 | `id` | `bigint` | no | autoincremental |  |
| 2 | `nombre` | `character varying(50)` | no | — |  |
| 3 | `descripcion` | `character varying(255)` | sí | — |  |
| 4 | `created_at` | `timestamp` | sí | `CURRENT_TIMESTAMP` |  |

**Restricciones de unicidad y comprobación**

- `roles_nombre_key`: UNIQUE (nombre)

**Índices**

- `roles_nombre_key`: `btree (nombre)`

---

## `sanciones`

Restricción no económica aplicada a un usuario (suspensión temporal).

**Clave primaria:** `PRIMARY KEY (id)`

| # | Columna | Tipo | Nulo | Por defecto | Notas |
|---:|---|---|:---:|---|---|
| 1 | `id` | `bigint` | no | autoincremental |  |
| 2 | `usuario_id` | `bigint` | no | — |  |
| 3 | `tipo` | `character varying(50)` | no | — |  |
| 4 | `motivo` | `text` | sí | — | Texto libre sobre la conducta de una persona. |
| 5 | `fecha_inicio` | `timestamp` | no | — |  |
| 6 | `fecha_fin` | `timestamp` | sí | — |  |
| 7 | `activa` | `boolean` | sí | `true` |  |
| 8 | `created_at` | `timestamp` | sí | `CURRENT_TIMESTAMP` |  |

**Claves foráneas**

- `fk_sancion_usuario`: FOREIGN KEY (usuario_id) REFERENCES usuarios(id)

**Restricciones de unicidad y comprobación**

- `chk_tipo_sancion`: CHECK (((tipo)::text = ANY ((ARRAY['SUSPENSION'::character varying, 'BLOQUEO_TEMPORAL'::character varying, 'ADVERTENCIA'::character varying])::text[])))

**Índices**

- `idx_sanciones_activa`: `btree (activa)`
- `idx_sanciones_usuario`: `btree (usuario_id)`

---

## `usuarios`

Personas que usan el sistema: estudiantes, bibliotecarios y administradores.

**Clave primaria:** `PRIMARY KEY (id)`

| # | Columna | Tipo | Nulo | Por defecto | Notas |
|---:|---|---|:---:|---|---|
| 1 | `id` | `bigint` | no | autoincremental |  |
| 2 | `nombre` | `character varying(100)` | no | — |  |
| 3 | `email` | `character varying(150)` | no | — | Dato personal identificativo. Es además la credencial de acceso. |
| 4 | `password` | `character varying(255)` | no | — | Hash bcrypt con salt único. Nunca se devuelve por la API. |
| 5 | `telefono` | `character varying(20)` | sí | — | Dato personal de contacto. |
| 6 | `foto` | `character varying(255)` | sí | — |  |
| 7 | `activo` | `boolean` | sí | `true` |  |
| 8 | `rol_id` | `bigint` | no | — |  |
| 9 | `created_at` | `timestamp` | sí | `CURRENT_TIMESTAMP` |  |
| 10 | `updated_at` | `timestamp` | sí | `CURRENT_TIMESTAMP` |  |

**Claves foráneas**

- `fk_usuario_rol`: FOREIGN KEY (rol_id) REFERENCES roles(id)

**Restricciones de unicidad y comprobación**

- `usuarios_email_key`: UNIQUE (email)

**Índices**

- `idx_usuarios_email`: `btree (email)`
- `idx_usuarios_rol`: `btree (rol_id)`
- `usuarios_email_key`: `btree (email)`

---

## Objetos programables

Los procedimientos y funciones almacenados están catalogados aparte, en
`docs/basedatos/CATALOGO-SP.md`. El criterio para decidir qué va por ORM y qué
va por procedimiento se documenta en ADR-0005.

