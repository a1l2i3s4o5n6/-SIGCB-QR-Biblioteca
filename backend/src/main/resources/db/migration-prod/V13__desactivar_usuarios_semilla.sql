-- ============================================
-- V13 [PROD]: Desactivar usuarios semilla con contraseñas conocidas
-- Esta migración SOLO se ejecuta en el entorno de producción
-- (location classpath:db/migration-prod, definida en application-prod.yml).
-- Se versiona como V13 (y no V11) porque Flyway aplica ambas ubicaciones en
-- producción y la numeración debe ser única en todo el esquema.
-- En desarrollo los seed permanecen activos para poder probar el sistema.
--
-- Las contraseñas de los usuarios semilla se mantienen en claro-solo-desarrollo
-- (.env.example, variables SEED_*_PASSWORD de V7), por lo que en producción
-- representan un riesgo de acceso no autorizado. Se desactivan para
-- impedir su uso; un administrador deberá activarlos y cambiarles la
-- contraseña de forma manual durante el primer despliegue.
-- ============================================

UPDATE usuarios SET activo = false WHERE email IN (
    'admin@biblioteca.com',
    'biblio@biblioteca.com',
    'carlos.garcia@estudiante.com',
    'ana.martinez@estudiante.com',
    'pedro.ramirez@estudiante.com',
    'laura.sanchez@estudiante.com'
);
