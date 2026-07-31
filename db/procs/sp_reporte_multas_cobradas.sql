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
