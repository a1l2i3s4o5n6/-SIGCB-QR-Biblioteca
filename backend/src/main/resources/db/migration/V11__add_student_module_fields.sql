-- V11: Campos del módulo estudiante
-- Agrega campos de datos académicos al usuario, prioridad a notificaciones,
-- historial de renovaciones a préstamos y posición en lista de espera a reservas.

-- 1. Usuarios: datos académicos del estudiante
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS codigo_estudiantil VARCHAR(30) UNIQUE;
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS facultad_id BIGINT REFERENCES facultades(id);
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS carrera_id BIGINT REFERENCES carreras(id);

-- 2. Notificaciones: prioridad de entrega
ALTER TABLE notificaciones ADD COLUMN IF NOT EXISTS prioridad VARCHAR(20) DEFAULT 'NORMAL';

-- 3. Préstamos: número de renovaciones realizadas
ALTER TABLE prestamos ADD COLUMN IF NOT EXISTS num_renovaciones INTEGER DEFAULT 0;

-- 4. Reservas: posición en la lista de espera del libro
ALTER TABLE reservas ADD COLUMN IF NOT EXISTS posicion_lista INTEGER;

-- Índices para consultas frecuentes del estudiante
CREATE INDEX IF NOT EXISTS idx_usuarios_facultad ON usuarios(facultad_id);
CREATE INDEX IF NOT EXISTS idx_usuarios_carrera ON usuarios(carrera_id);
CREATE INDEX IF NOT EXISTS idx_notificaciones_prioridad ON notificaciones(prioridad);
