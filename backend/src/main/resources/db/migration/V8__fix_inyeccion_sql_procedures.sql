-- ============================================
-- V8: Corrige inyección SQL en stored procedures (Hallazgo H5)
-- Reemplaza la concatenación "(p_dias || ' days')::INTERVAL" por la
-- función MAKE_INTERVAL, inmune a inyección.
-- Idempotente: CREATE OR REPLACE conserva la misma firma de argumentos.
-- ============================================

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
    SELECT estado INTO v_estado_inventario FROM inventario WHERE id = p_inventario_id;
    IF v_estado_inventario IS NULL THEN
        RAISE EXCEPTION 'Ejemplar no encontrado';
    END IF;
    IF v_estado_inventario != 'DISPONIBLE' THEN
        RAISE EXCEPTION 'El ejemplar no está disponible';
    END IF;

    SELECT COUNT(*) INTO v_prestamos_activos
    FROM prestamos WHERE usuario_id = p_usuario_id AND estado = 'ACTIVO';
    IF v_prestamos_activos >= 5 THEN
        RAISE EXCEPTION 'El usuario tiene demasiados préstamos activos';
    END IF;

    INSERT INTO prestamos (usuario_id, inventario_id, fecha_prestamo, fecha_vencimiento, estado)
    VALUES (p_usuario_id, p_inventario_id, NOW(), NOW() + MAKE_INTERVAL(days => p_dias_prestamo), 'ACTIVO')
    RETURNING id INTO p_prestamo_id;

    UPDATE inventario SET estado = 'PRESTADO' WHERE id = p_inventario_id;

    UPDATE libros l
    SET ejemplares_disponibles = ejemplares_disponibles - 1
    FROM inventario i
    WHERE i.id = p_inventario_id AND l.id = i.libro_id;
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
    SELECT estado, usuario_id, inventario_id
    INTO v_estado, v_usuario_id, v_inventario_id
    FROM prestamos WHERE id = p_prestamo_id;

    IF v_estado IS NULL THEN
        RAISE EXCEPTION 'Préstamo no encontrado';
    END IF;
    IF v_estado != 'ACTIVO' THEN
        RAISE EXCEPTION 'Solo se pueden renovar préstamos activos';
    END IF;

    UPDATE prestamos
    SET estado = 'RENOVADO', observaciones = 'Renovado - nueva fecha: ' || NOW()
    WHERE id = p_prestamo_id;

    INSERT INTO prestamos (usuario_id, inventario_id, fecha_prestamo, fecha_vencimiento, estado, observaciones)
    VALUES (v_usuario_id, v_inventario_id, NOW(), NOW() + MAKE_INTERVAL(days => p_dias_renovacion),
            'ACTIVO', 'Renovación del préstamo #' || p_prestamo_id)
    RETURNING id INTO p_nuevo_prestamo_id;
END;
$$;