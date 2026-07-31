CREATE OR REPLACE FUNCTION fn_contar_prestamos_entre_fechas(
    p_inicio TIMESTAMP,
    p_fin TIMESTAMP
) RETURNS BIGINT
    LANGUAGE SQL
    STABLE
AS $$
    SELECT COUNT(*)
    FROM prestamos
    WHERE fecha_prestamo >= p_inicio AND fecha_prestamo <= p_fin;
$$;
