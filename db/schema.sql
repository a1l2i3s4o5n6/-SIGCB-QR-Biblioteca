-- Schema inicial para SIGCB-QR
-- NOTA: Las tablas se crean via Flyway en V1__create_users_tables.sql y V2__create_library_tables.sql
-- Este archivo se provee como respaldo para el entrypoint de Docker y contiene solo los procedimientos

BEGIN;

-- ============================================================
-- FUNCIONES
-- ============================================================

CREATE OR REPLACE FUNCTION fn_contar_prestamos_entre_fechas(
    p_inicio TIMESTAMP,
    p_fin TIMESTAMP
) RETURNS BIGINT
    LANGUAGE SQL
    STABLE
AS $$
    SELECT COUNT(*)
    FROM prestamos
    WHERE fecha_prestamo >= p_inicio AND fecha_prestamo <= p_fin;
$$;

CREATE OR REPLACE FUNCTION fn_total_multas_pendientes()
RETURNS DECIMAL(12,2)
    LANGUAGE SQL
    STABLE
AS $$
    SELECT COALESCE(SUM(monto), 0)
    FROM multas
    WHERE pagada = false;
$$;

CREATE OR REPLACE FUNCTION fn_total_cobrado_entre(
    p_inicio TIMESTAMP,
    p_fin TIMESTAMP
) RETURNS DECIMAL(12,2)
    LANGUAGE SQL
    STABLE
AS $$
    SELECT COALESCE(SUM(monto), 0)
    FROM multas
    WHERE pagada = true AND fecha_pago BETWEEN p_inicio AND p_fin;
$$;

CREATE OR REPLACE FUNCTION fn_libros_disponibles()
RETURNS TABLE(
    id BIGINT,
    titulo VARCHAR,
    isbn VARCHAR,
    ejemplares_disponibles INT
)
    LANGUAGE SQL
    STABLE
AS $$
    SELECT l.id, l.titulo, l.isbn, l.ejemplares_disponibles
    FROM libros l
    WHERE l.ejemplares_disponibles > 0 AND l.activo = true
    ORDER BY l.titulo;
$$;

-- ============================================================
-- PROCEDIMIENTOS ALMACENADOS
-- ============================================================

CREATE OR REPLACE PROCEDURE sp_reporte_libros_mas_prestados(
    IN p_limit INT,
    INOUT p_ref REFCURSOR
)
    LANGUAGE plpgsql
AS $$
BEGIN
    OPEN p_ref FOR
        SELECT l.id, l.titulo, l.isbn,
               (l.ejemplares_totales - l.ejemplares_disponibles) AS veces_prestado
        FROM libros l
        WHERE l.activo = true
        ORDER BY (l.ejemplares_totales - l.ejemplares_disponibles) DESC
        LIMIT p_limit;
END;
$$;

CREATE OR REPLACE PROCEDURE sp_prestamos_entre_fechas(
    IN p_inicio TIMESTAMP,
    IN p_fin TIMESTAMP,
    INOUT p_ref REFCURSOR
)
    LANGUAGE plpgsql
AS $$
BEGIN
    OPEN p_ref FOR
        SELECT p.id, p.fecha_prestamo, p.fecha_vencimiento, p.fecha_devolucion,
               p.estado, p.observaciones,
               u.id AS usuario_id, u.nombre AS usuario_nombre,
               l.id AS libro_id, l.titulo AS libro_titulo,
               i.codigo_ejemplar
        FROM prestamos p
        JOIN usuarios u ON u.id = p.usuario_id
        JOIN inventario i ON i.id = p.inventario_id
        JOIN libros l ON l.id = i.libro_id
        WHERE p.fecha_prestamo >= p_inicio AND p.fecha_prestamo <= p_fin
        ORDER BY p.fecha_prestamo;
END;
$$;

CREATE OR REPLACE PROCEDURE sp_top_usuarios_prestamos(
    IN p_limit INT,
    INOUT p_ref REFCURSOR
)
    LANGUAGE plpgsql
AS $$
BEGIN
    OPEN p_ref FOR
        SELECT u.id, u.nombre, u.email, COUNT(p.id) AS total_prestamos
        FROM usuarios u
        JOIN prestamos p ON p.usuario_id = u.id
        GROUP BY u.id, u.nombre, u.email
        ORDER BY total_prestamos DESC
        LIMIT p_limit;
END;
$$;

CREATE OR REPLACE PROCEDURE sp_dashboard_estadisticas(
    IN p_fecha_inicio TIMESTAMP,
    IN p_fecha_fin TIMESTAMP,
    INOUT p_ref REFCURSOR
)
    LANGUAGE plpgsql
AS $$
BEGIN
    OPEN p_ref FOR
        SELECT
            (SELECT COUNT(*) FROM prestamos WHERE fecha_prestamo BETWEEN p_fecha_inicio AND p_fecha_fin) AS libros_prestados_hoy,
            (SELECT COUNT(*) FROM libros WHERE ejemplares_disponibles > 0) AS libros_disponibles,
            (SELECT COUNT(*) FROM usuarios WHERE activo = true) AS estudiantes_activos,
            (SELECT COUNT(*) FROM reservas WHERE estado = 'PENDIENTE') AS reservas_pendientes,
            (SELECT COUNT(*) FROM multas WHERE pagada = false) AS multas_pendientes,
            (SELECT COALESCE(SUM(monto), 0) FROM multas WHERE pagada = false) AS total_multas;
END;
$$;

CREATE OR REPLACE PROCEDURE sp_crear_prestamo(
    IN p_usuario_id BIGINT,
    IN p_inventario_id BIGINT,
    IN p_dias_prestamo INT DEFAULT 7,
    INOUT p_prestamo_id BIGINT DEFAULT NULL
)
    LANGUAGE plpgsql
AS $$
DECLARE
    v_estado_inventario VARCHAR;
    v_prestamos_activos BIGINT;
BEGIN
    -- Validar disponibilidad del ejemplar
    SELECT estado INTO v_estado_inventario FROM inventario WHERE id = p_inventario_id;
    IF v_estado_inventario IS NULL THEN
        RAISE EXCEPTION 'Ejemplar no encontrado';
    END IF;
    IF v_estado_inventario != 'DISPONIBLE' THEN
        RAISE EXCEPTION 'El ejemplar no está disponible';
    END IF;

    -- Validar límite de préstamos activos
    SELECT COUNT(*) INTO v_prestamos_activos
    FROM prestamos WHERE usuario_id = p_usuario_id AND estado = 'ACTIVO';
    IF v_prestamos_activos >= 5 THEN
        RAISE EXCEPTION 'El usuario tiene demasiados préstamos activos';
    END IF;

    -- Crear préstamo
    INSERT INTO prestamos (usuario_id, inventario_id, fecha_prestamo, fecha_vencimiento, estado)
    VALUES (p_usuario_id, p_inventario_id, NOW(), NOW() + (p_dias_prestamo || ' days')::INTERVAL, 'ACTIVO')
    RETURNING id INTO p_prestamo_id;

    -- Actualizar inventario
    UPDATE inventario SET estado = 'PRESTADO' WHERE id = p_inventario_id;

    -- Actualizar ejemplares disponibles del libro
    UPDATE libros l
    SET ejemplares_disponibles = ejemplares_disponibles - 1
    FROM inventario i
    WHERE i.id = p_inventario_id AND l.id = i.libro_id;
END;
$$;

CREATE OR REPLACE PROCEDURE sp_devolver_prestamo(
    IN p_prestamo_id BIGINT,
    INOUT p_multa_generada BOOLEAN DEFAULT FALSE
)
    LANGUAGE plpgsql
AS $$
DECLARE
    v_estado VARCHAR;
    v_inventario_id BIGINT;
    v_libro_id BIGINT;
    v_fecha_vencimiento TIMESTAMP;
    v_dias_retraso INT;
BEGIN
    -- Validar préstamo
    SELECT estado, inventario_id, fecha_vencimiento
    INTO v_estado, v_inventario_id, v_fecha_vencimiento
    FROM prestamos WHERE id = p_prestamo_id;

    IF v_estado IS NULL THEN
        RAISE EXCEPTION 'Préstamo no encontrado';
    END IF;
    IF v_estado = 'DEVUELTO' THEN
        RAISE EXCEPTION 'El préstamo ya fue devuelto';
    END IF;

    -- Actualizar préstamo
    UPDATE prestamos
    SET estado = 'DEVUELTO', fecha_devolucion = NOW()
    WHERE id = p_prestamo_id;

    -- Actualizar inventario
    UPDATE inventario SET estado = 'DISPONIBLE' WHERE id = v_inventario_id;

    -- Actualizar ejemplares disponibles
    UPDATE libros l
    SET ejemplares_disponibles = ejemplares_disponibles + 1
    FROM inventario i
    WHERE i.id = v_inventario_id AND l.id = i.libro_id;

    -- Generar multa si está vencido
    IF v_fecha_vencimiento < NOW() THEN
        v_dias_retraso := EXTRACT(DAY FROM (NOW() - v_fecha_vencimiento));
        INSERT INTO multas (prestamo_id, usuario_id, monto, pagada, fecha_multa)
        SELECT p_prestamo_id, p.usuario_id, v_dias_retraso * 0.50, false, NOW()
        FROM prestamos p WHERE p.id = p_prestamo_id;
        p_multa_generada := TRUE;
    END IF;
END;
$$;

CREATE OR REPLACE PROCEDURE sp_renovar_prestamo(
    IN p_prestamo_id BIGINT,
    IN p_dias_renovacion INT DEFAULT 7,
    INOUT p_nuevo_prestamo_id BIGINT DEFAULT NULL
)
    LANGUAGE plpgsql
AS $$
DECLARE
    v_estado VARCHAR;
    v_usuario_id BIGINT;
    v_inventario_id BIGINT;
BEGIN
    -- Validar préstamo original
    SELECT estado, usuario_id, inventario_id
    INTO v_estado, v_usuario_id, v_inventario_id
    FROM prestamos WHERE id = p_prestamo_id;

    IF v_estado IS NULL THEN
        RAISE EXCEPTION 'Préstamo no encontrado';
    END IF;
    IF v_estado != 'ACTIVO' THEN
        RAISE EXCEPTION 'Solo se pueden renovar préstamos activos';
    END IF;

    -- Marcar como renovado
    UPDATE prestamos
    SET estado = 'RENOVADO', observaciones = 'Renovado - nueva fecha: ' || NOW()
    WHERE id = p_prestamo_id;

    -- Crear nuevo préstamo
    INSERT INTO prestamos (usuario_id, inventario_id, fecha_prestamo, fecha_vencimiento, estado, observaciones)
    VALUES (v_usuario_id, v_inventario_id, NOW(), NOW() + (p_dias_renovacion || ' days')::INTERVAL,
            'ACTIVO', 'Renovación del préstamo #' || p_prestamo_id)
    RETURNING id INTO p_nuevo_prestamo_id;
END;
$$;

CREATE OR REPLACE PROCEDURE sp_reporte_prestamos_diarios(
    IN p_fecha DATE,
    INOUT p_ref REFCURSOR
)
    LANGUAGE plpgsql
AS $$
BEGIN
    OPEN p_ref FOR
        SELECT p.id, p.fecha_prestamo, p.estado,
               u.nombre AS usuario_nombre,
               l.titulo AS libro_titulo
        FROM prestamos p
        JOIN usuarios u ON u.id = p.usuario_id
        JOIN inventario i ON i.id = p.inventario_id
        JOIN libros l ON l.id = i.libro_id
        WHERE p.fecha_prestamo::DATE = p_fecha
        ORDER BY p.fecha_prestamo;
END;
$$;

CREATE OR REPLACE PROCEDURE sp_reporte_multas_cobradas(
    IN p_mes INT,
    IN p_anio INT,
    INOUT p_ref REFCURSOR
)
    LANGUAGE plpgsql
AS $$
DECLARE
    v_inicio DATE;
    v_fin DATE;
BEGIN
    v_inicio := MAKE_DATE(p_anio, p_mes, 1);
    v_fin := (v_inicio + INTERVAL '1 month')::DATE - 1;

    OPEN p_ref FOR
        SELECT
            TO_CHAR(v_inicio, 'YYYY-MM') AS mes,
            COALESCE(SUM(m.monto), 0) AS total_cobrado,
            (SELECT COUNT(*) FROM multas WHERE pagada = false) AS pendientes
        FROM multas m
        WHERE m.pagada = true AND m.fecha_pago BETWEEN v_inicio AND (v_fin + INTERVAL '1 day');
END;
$$;

COMMIT;
