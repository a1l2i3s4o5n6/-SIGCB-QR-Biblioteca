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
