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
    VALUES (v_usuario_id, v_inventario_id, NOW(), NOW() + (p_dias_renovacion || ' days')::INTERVAL,
            'ACTIVO', 'Renovación del préstamo #' || p_prestamo_id)
    RETURNING id INTO p_nuevo_prestamo_id;
END;
$$;
