# Catálogo de Procedimientos Almacenados y Funciones — SIGCB-QR

> Versión: v0.9.0-rc
> Fecha: 2026-07-30

## Convención de nomenclatura

| Prefijo | Tipo | Archivo |
|---------|------|---------|
| `fn_` | Función SQL (RETURNS) | `db/procs/fn_<verbo>_<sustantivo>.sql` |
| `sp_` | Procedimiento almacenado | `db/procs/sp_<verbo>_<sustantivo>.sql` |

---

## Funciones

### fn_contar_prestamos_entre_fechas
| Propiedad | Valor |
|-----------|-------|
| Propósito | Cuenta préstamos en un rango de fechas |
| Parámetros | `p_inicio TIMESTAMP`, `p_fin TIMESTAMP` |
| Retorno | `BIGINT` |
| Tablas afectadas | `prestamos` (SELECT) |

### fn_total_multas_pendientes
| Propiedad | Valor |
|-----------|-------|
| Propósito | Suma el monto total de multas no pagadas |
| Parámetros | Ninguno |
| Retorno | `DECIMAL(12,2)` |
| Tablas afectadas | `multas` (SELECT) |

### fn_total_cobrado_entre
| Propiedad | Valor |
|-----------|-------|
| Propósito | Suma el monto cobrado en un rango de fechas |
| Parámetros | `p_inicio TIMESTAMP`, `p_fin TIMESTAMP` |
| Retorno | `DECIMAL(12,2)` |
| Tablas afectadas | `multas` (SELECT) |

### fn_libros_disponibles
| Propiedad | Valor |
|-----------|-------|
| Propósito | Lista libros con ejemplares disponibles |
| Parámetros | Ninguno |
| Retorno | `TABLE(id BIGINT, titulo VARCHAR, isbn VARCHAR, ejemplares_disponibles INT)` |
| Tablas afectadas | `libros` (SELECT) |

---

## Procedimientos almacenados

### sp_reporte_libros_mas_prestados
| Propiedad | Valor |
|-----------|-------|
| Propósito | Obtiene los libros más prestados (top N) |
| Parámetros | `p_limit INT` (IN), `p_ref REFCURSOR` (INOUT) |
| Cursores | Retorna cursor con id, titulo, isbn, veces_prestado |
| Tablas afectadas | `libros` (SELECT) |

### sp_prestamos_entre_fechas
| Propiedad | Valor |
|-----------|-------|
| Propósito | Obtiene préstamos en un rango de fechas con datos de usuario y libro |
| Parámetros | `p_inicio TIMESTAMP` (IN), `p_fin TIMESTAMP` (IN), `p_ref REFCURSOR` (INOUT) |
| Cursores | Retorna cursor con datos completos del préstamo + usuario + libro |
| Tablas afectadas | `prestamos`, `usuarios`, `inventario`, `libros` (SELECT, JOIN) |

### sp_top_usuarios_prestamos
| Propiedad | Valor |
|-----------|-------|
| Propósito | Obtiene los usuarios con más préstamos (top N) |
| Parámetros | `p_limit INT` (IN), `p_ref REFCURSOR` (INOUT) |
| Cursores | Retorna cursor con id, nombre, email, total_prestamos |
| Tablas afectadas | `usuarios`, `prestamos` (SELECT, JOIN, GROUP BY) |

### sp_dashboard_estadisticas
| Propiedad | Valor |
|-----------|-------|
| Propósito | Obtiene estadísticas agregadas para el dashboard |
| Parámetros | `p_fecha_inicio TIMESTAMP` (IN), `p_fecha_fin TIMESTAMP` (IN), `p_ref REFCURSOR` (INOUT) |
| Cursores | Retorna cursor con 6 métricas agregadas |
| Tablas afectadas | `prestamos`, `libros`, `usuarios`, `reservas`, `multas` (SELECT, COUNT, SUM) |

### sp_crear_prestamo
| Propiedad | Valor |
|-----------|-------|
| Propósito | Crea un préstamo con validaciones de disponibilidad y límite |
| Parámetros | `p_usuario_id BIGINT` (IN), `p_inventario_id BIGINT` (IN), `p_dias_prestamo INT DEFAULT 7` (IN), `p_prestamo_id BIGINT DEFAULT NULL` (INOUT) |
| Validaciones | Disponibilidad del ejemplar, límite de 5 préstamos activos |
| Tablas afectadas | `prestamos` (INSERT), `inventario` (UPDATE), `libros` (UPDATE) |

### sp_devolver_prestamo
| Propiedad | Valor |
|-----------|-------|
| Propósito | Procesa la devolución de un préstamo y genera multa si aplica |
| Parámetros | `p_prestamo_id BIGINT` (IN), `p_multa_generada BOOLEAN DEFAULT FALSE` (INOUT) |
| Validaciones | Estado del préstamo, fecha de vencimiento |
| Tablas afectadas | `prestamos` (UPDATE), `inventario` (UPDATE), `libros` (UPDATE), `multas` (INSERT) |

### sp_renovar_prestamo
| Propiedad | Valor |
|-----------|-------|
| Propósito | Renueva un préstamo activo creando uno nuevo |
| Parámetros | `p_prestamo_id BIGINT` (IN), `p_dias_renovacion INT DEFAULT 7` (IN), `p_nuevo_prestamo_id BIGINT DEFAULT NULL` (INOUT) |
| Validaciones | Estado del préstamo original |
| Tablas afectadas | `prestamos` (UPDATE, INSERT) |

### sp_reporte_prestamos_diarios
| Propiedad | Valor |
|-----------|-------|
| Propósito | Genera reporte de préstamos de un día específico |
| Parámetros | `p_fecha DATE` (IN), `p_ref REFCURSOR` (INOUT) |
| Cursores | Retorna cursor con préstamos del día + usuario + libro |
| Tablas afectadas | `prestamos`, `usuarios`, `inventario`, `libros` (SELECT, JOIN) |

### sp_reporte_multas_cobradas
| Propiedad | Valor |
|-----------|-------|
| Propósito | Genera reporte de multas cobradas en un mes específico |
| Parámetros | `p_mes INT` (IN), `p_anio INT` (IN), `p_ref REFCURSOR` (INOUT) |
| Cursores | Retorna cursor con mes, total_cobrado, pendientes |
| Tablas afectadas | `multas` (SELECT, COUNT, SUM) |

---

## Seguridad

Todos los procedimientos usan exclusivamente parámetros nombrados. No se construye SQL dinámico
mediante concatenación (EXECUTE IMMEDIATE, sp_executesql o equivalentes).

La cuenta de base de datos de la aplicación tiene únicamente:
- `EXECUTE` sobre estos procedimientos y funciones
- `SELECT`, `INSERT`, `UPDATE`, `DELETE` sobre las tablas del dominio
