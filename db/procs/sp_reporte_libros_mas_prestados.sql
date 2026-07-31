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
