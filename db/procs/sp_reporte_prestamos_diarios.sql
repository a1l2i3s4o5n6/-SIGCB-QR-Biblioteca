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
