-- Seed data inicial para SIGCB-QR
-- NOTA: Los datos semilla se cargan via Flyway en V3__datos_semilla.sql
-- Este archivo se provee como respaldo para el entrypoint de Docker
-- Los datos se insertan solo si las tablas están vacías

BEGIN;

INSERT INTO roles (nombre, descripcion)
SELECT * FROM (VALUES
    ('ADMIN', 'Administrador del sistema'),
    ('BIBLIOTECARIO', 'Personal de biblioteca'),
    ('ESTUDIANTE', 'Usuario estudiante')
) AS v(nombre, descripcion)
WHERE NOT EXISTS (SELECT 1 FROM roles LIMIT 1);

COMMIT;
