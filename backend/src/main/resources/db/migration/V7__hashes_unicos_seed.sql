-- ============================================
-- V7: Contraseñas semilla con hash bcrypt único por usuario
-- Corrige C2: en V3/V5 todos los usuarios compartían el mismo hash bcrypt.
-- Cada UPDATE usa un salt aleatorio propio (pgcrypto), por lo que cada
-- hash es único aunque la contraseña siga siendo la misma.
-- Contraseñas válidas: admin123 / biblio123 / estudiante123
-- ============================================

CREATE EXTENSION IF NOT EXISTS pgcrypto;

UPDATE usuarios SET password = crypt('admin123', gen_salt('bf', 10)) WHERE email = 'admin@biblioteca.com';
UPDATE usuarios SET password = crypt('biblio123', gen_salt('bf', 10)) WHERE email = 'biblio@biblioteca.com';
UPDATE usuarios SET password = crypt('estudiante123', gen_salt('bf', 10)) WHERE email IN (
    'carlos.garcia@estudiante.com',
    'ana.martinez@estudiante.com',
    'pedro.ramirez@estudiante.com',
    'laura.sanchez@estudiante.com'
);