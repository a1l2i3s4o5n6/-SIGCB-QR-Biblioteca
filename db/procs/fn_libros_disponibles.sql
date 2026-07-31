CREATE OR REPLACE FUNCTION fn_libros_disponibles()
RETURNS TABLE(
    id BIGINT,
    titulo VARCHAR,
    isbn VARCHAR,
    ejemplares_disponibles INT
)
    LANGUAGE SQL
    STABLE
AS $$
    SELECT l.id, l.titulo, l.isbn, l.ejemplares_disponibles
    FROM libros l
    WHERE l.ejemplares_disponibles > 0 AND l.activo = true
    ORDER BY l.titulo;
$$;
