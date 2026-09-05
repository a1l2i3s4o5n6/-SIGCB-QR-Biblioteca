-- ============================================
-- V7: Contraseñas semilla con hash bcrypt único por usuario
-- Corrige C2: en V3/V5 todos los usuarios compartían el mismo hash bcrypt.
-- Cada UPDATE usa un salt aleatorio propio (pgcrypto), por lo que cada
-- hash es único aunque la contraseña siga siendo la misma.
--
-- Las contraseñas llegan por variables de entorno a través de los
-- placeholders de Flyway (spring.flyway.placeholders), definidos en
-- application.yml. Si un placeholder está vacío la fila NO se modifica,
-- de modo que los valores quedan bajo control del entorno y nunca
-- versionados en el código de migración.
-- ============================================

CREATE EXTENSION IF NOT EXISTS pgcrypto;

UPDATE usuarios SET password = crypt('${seed_admin_password}', gen_salt('bf', 10))
WHERE email = 'admin@biblioteca.com'
  AND '${seed_admin_password}' <> '';

UPDATE usuarios SET password = crypt('${seed_biblio_password}', gen_salt('bf', 10))
WHERE email = 'biblio@biblioteca.com'
  AND '${seed_biblio_password}' <> '';

UPDATE usuarios SET password = crypt('${seed_student_password}', gen_salt('bf', 10))
WHERE email IN (
    'carlos.garcia@estudiante.com',
    'ana.martinez@estudiante.com',
    'pedro.ramirez@estudiante.com',
    'laura.sanchez@estudiante.com'
)
  AND '${seed_student_password}' <> '';