CREATE OR REPLACE FUNCTION fn_total_multas_pendientes()
RETURNS DECIMAL(12,2)
    LANGUAGE SQL
    STABLE
AS $$
    SELECT COALESCE(SUM(monto), 0)
    FROM multas
    WHERE pagada = false;
$$;
