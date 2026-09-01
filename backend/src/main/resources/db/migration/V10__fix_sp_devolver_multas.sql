-- Fix: el INSERT anterior usaba la columna inexistente "fecha_multa".
-- La tabla multas real usa: prestamo_id, usuario_id, monto, pagada, concepto, created_at.
CREATE OR REPLACE PROCEDURE sp_devolver_prestamo(
    IN p_prestamo_id BIGINT,
    INOUT p_multa_generada BOOLEAN DEFAULT FALSE
)
    LANGUAGE plpgsql
AS $$
DECLARE
    v_estado VARCHAR;
    v_inventario_id BIGINT;
    v_fecha_vencimiento TIMESTAMP;
    v_dias_retraso INT;
    v_multa_diaria NUMERIC;
BEGIN
    v_multa_diaria := COALESCE(
        (SELECT CAST(valor AS NUMERIC) FROM configuracion WHERE clave = 'monto_multa_diario' LIMIT 1),
        5.00
    );

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

    -- Generar multa si está vencido, usando el monto diario configurado
    IF v_fecha_vencimiento < NOW() THEN
        v_dias_retraso := EXTRACT(DAY FROM (NOW() - v_fecha_vencimiento));
        INSERT INTO multas (prestamo_id, usuario_id, monto, pagada, concepto)
        SELECT p_prestamo_id, p.usuario_id, v_dias_retraso * v_multa_diaria, false,
               'Multa por retraso de ' || v_dias_retraso || ' días en devolución'
        FROM prestamos p WHERE p.id = p_prestamo_id;
        p_multa_generada := TRUE;
    END IF;
END;
$$;