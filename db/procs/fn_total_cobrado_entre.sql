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
