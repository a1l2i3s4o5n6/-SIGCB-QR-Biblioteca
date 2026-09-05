-- ============================================
-- V5: Corregir contraseñas de los usuarios semilla
-- El hash del V3 ($2a$10$N9qo8uLOickg...) no correspondía a ninguna
-- contraseña, por lo que el login fallaba aunque la documentación
-- prometía unas credenciales concretas.
-- Idempotente: los UPDATE se aplican por email y no dependen de la versión de la BD.
-- Las contraseñas efectivas finales se fijan en V7 desde variables de entorno;
-- estos hashes son intermedios.
-- ============================================

UPDATE usuarios SET password = '$2y$10$hh.L5z/VHk0HV.5wNb79i.chs1x1psOW3pFEeejBKFzK04e8NsIHe' WHERE email = 'admin@biblioteca.com';
UPDATE usuarios SET password = '$2y$10$q/xkKqVLts7yvm8nGJ.Yp.BWSJDLE83Ckamxdh0zwOMRIXmiWPUsa' WHERE email = 'biblio@biblioteca.com';
UPDATE usuarios SET password = '$2y$10$uVpXs6O/bXNhXWwS7uF17uXANjDkv1RbQKWYUbG3zJ0AsOxTi5MSK' WHERE email IN (
    'carlos.garcia@estudiante.com',
    'ana.martinez@estudiante.com',
    'pedro.ramirez@estudiante.com',
    'laura.sanchez@estudiante.com'
);