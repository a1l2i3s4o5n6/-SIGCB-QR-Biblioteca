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
