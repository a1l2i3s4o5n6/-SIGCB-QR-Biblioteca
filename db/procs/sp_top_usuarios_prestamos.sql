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
