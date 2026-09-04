-- V12: Flujo de renovación con aprobación del bibliotecario
-- Permite que el estudiante solicite la renovación de un préstamo y que
-- el bibliotecario la apruebe o rechace. Se usa un estado intermedio
-- RENOVACION_PENDIENTE en la tabla prestamos.

-- Ampliar el CHECK constraint de estado de préstamos para incluir el estado intermedio
ALTER TABLE prestamos DROP CONSTRAINT IF EXISTS chk_estado_prestamo;
ALTER TABLE prestamos ADD CONSTRAINT chk_estado_prestamo
    CHECK (estado IN ('ACTIVO', 'DEVUELTO', 'VENCIDO', 'RENOVADO', 'RENOVACION_PENDIENTE'));
