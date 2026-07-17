-- ============================================
-- ROLES
-- ============================================
INSERT INTO roles (nombre, descripcion) VALUES
('ADMIN', 'Administrador del sistema con todos los permisos'),
('BIBLIOTECARIO', 'Encargado de gestión de préstamos y biblioteca'),
('ESTUDIANTE', 'Usuario estudiante de la universidad');

-- ============================================
-- USUARIOS (password: admin123 / biblio123 / estudiante123)
-- ============================================
INSERT INTO usuarios (nombre, email, password, telefono, activo, rol_id) VALUES
('Admin Sistema', 'admin@biblioteca.com', '$2a$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', '555-0100', true, 1),
('María López', 'biblio@biblioteca.com', '$2a$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', '555-0101', true, 2),
('Carlos García', 'carlos.garcia@estudiante.com', '$2a$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', '555-0102', true, 3),
('Ana Martínez', 'ana.martinez@estudiante.com', '$2a$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', '555-0103', true, 3),
('Pedro Ramírez', 'pedro.ramirez@estudiante.com', '$2a$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', '555-0104', true, 3),
('Laura Sánchez', 'laura.sanchez@estudiante.com', '$2a$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', '555-0105', true, 3);

-- ============================================
-- FACULTADES
-- ============================================
INSERT INTO facultades (nombre, codigo) VALUES
('Facultad de Ingeniería', 'FI'),
('Facultad de Ciencias de la Salud', 'FCS'),
('Facultad de Ciencias Económicas', 'FCE'),
('Facultad de Ciencias Sociales', 'FCSO'),
('Facultad de Arquitectura', 'FA');

-- ============================================
-- CARRERAS
-- ============================================
INSERT INTO carreras (nombre, codigo, facultad_id) VALUES
('Ingeniería en Software', 'IS', 1),
('Ingeniería en Sistemas', 'ISIS', 1),
('Medicina', 'MED', 2),
('Enfermería', 'ENF', 2),
('Administración de Empresas', 'ADE', 3),
('Contaduría', 'CON', 3),
('Psicología', 'PSI', 4),
('Arquitectura', 'ARQ', 5);

-- ============================================
-- CATEGORÍAS
-- ============================================
INSERT INTO categorias (nombre, descripcion) VALUES
('Ingeniería', 'Libros de ingeniería y tecnología'),
('Medicina', 'Libros de ciencias de la salud'),
('Matemáticas', 'Libros de matemáticas y estadística'),
('Derecho', 'Libros de ciencias jurídicas'),
('Literatura', 'Libros de literatura y lingüística');

-- ============================================
-- EDITORIALES
-- ============================================
INSERT INTO editoriales (nombre, pais) VALUES
('Pearson', 'Estados Unidos'),
('McGraw-Hill', 'Estados Unidos'),
('Alfaomega', 'México'),
('Santillana', 'España'),
('O Reilly Media', 'Estados Unidos'),
('Springer', 'Alemania'),
('Cambridge University Press', 'Reino Unido');

-- ============================================
-- AUTORES
-- ============================================
INSERT INTO autores (nombre, apellido, nacionalidad) VALUES
('Robert', 'Martin', 'Estadounidense'),
('Martin', 'Fowler', 'Británico'),
('Eric', 'Gamma', 'Suizo'),
('Grady', 'Booch', 'Estadounidense'),
('Kent', 'Beck', 'Estadounidense'),
('Catherine', 'Linn', 'Estadounidense'),
('Thomas', 'Cormen', 'Estadounidense'),
('Donald', 'Knuth', 'Estadounidense'),
('Andrew', 'Tanenbaum', 'Estadounidense'),
('Ralph', 'Johnson', 'Estadounidense');

-- ============================================
-- LIBROS (20 libros)
-- ============================================
INSERT INTO libros (titulo, isbn, anio_publicacion, ejemplares_totales, ejemplares_disponibles, ubicacion, descripcion, categoria_id, editorial_id) VALUES
('Clean Code', '978-0132350884', 2008, 5, 5, 'Estante A-01', 'Un manual de estilo de desarrollo ágil de software', 1, 1),
('Design Patterns', '978-0201633610', 1994, 3, 3, 'Estante A-02', 'Elementos de software orientado a objetos reutilizables', 1, 1),
('Introduction to Algorithms', '978-0262033848', 2009, 4, 3, 'Estante A-03', 'Libro completo sobre algoritmos', 1, 3),
('Structure and Interpretation of Computer Programs', '978-0262510875', 1996, 2, 2, 'Estante A-04', 'Principios fundamentales de programación', 1, 3),
('The Art of Computer Programming', '978-0201896831', 1997, 2, 2, 'Estante A-05', 'Serie completa de programación', 1, 1),
('Computer Networks', '978-0132126953', 2010, 4, 4, 'Estante A-06', 'Redes de computadoras - enfoque descendente', 1, 1),
('Operating Systems: Design and Implementation', '978-0131429383', 2006, 3, 2, 'Estante A-07', 'Sistemas operativos modernos', 1, 1),
('Refactoring', '978-0134757599', 2018, 3, 3, 'Estante A-08', 'Mejora del diseño de código existente', 1, 1),
('Cálculo Diferencial', '978-6073206037', 2015, 6, 5, 'Estante B-01', 'Cálculo diferencial para ingeniería', 3, 3),
('Álgebra Lineal', '978-6073206051', 2016, 5, 5, 'Estante B-02', 'Álgebra lineal y sus aplicaciones', 3, 3),
('Anatomía Humana', '978-8490228421', 2018, 3, 2, 'Estante C-01', 'Atlas de anatomía humana', 2, 4),
('Fisiología Médica', '978-8416004843', 2017, 3, 3, 'Estante C-02', 'Principios de fisiología', 2, 4),
('Derecho Constitucional', '978-9584201293', 2019, 4, 4, 'Estante D-01', 'Introducción al derecho constitucional', 4, 5),
('Introducción al Derecho', '978-9584207561', 2020, 4, 4, 'Estante D-02', 'Fundamentos del derecho', 4, 5),
('Cien Años de Soledad', '978-8437604947', 1967, 3, 3, 'Estante E-01', 'Novela emblemática del realismo mágico', 5, 6),
('Don Quijote de la Mancha', '978-8467041505', 1605, 2, 2, 'Estante E-02', 'Obra cumbre de la literatura universal', 5, 6),
('La Sombra del Viento', '978-8408043643', 2001, 3, 3, 'Estante E-03', 'Misterio y romance en la Barcelona del siglo XX', 5, 6),
('Microservicios con Spring Boot', '978-8441541892', 2021, 4, 4, 'Estante A-09', 'Construcción de microservicios con Spring Boot y Spring Cloud', 1, 2),
('JavaScript: The Good Parts', '978-0596517748', 2008, 3, 3, 'Estante A-10', 'Lenguaje JavaScript optimizado', 1, 5),
('Machine Learning', '978-0071824093', 2016, 3, 2, 'Estante A-11', 'Introducción al aprendizaje automático', 1, 2);

-- ============================================
-- LIBRO-AUTORES (asociaciones)
-- ============================================
INSERT INTO libro_autores (libro_id, autor_id) VALUES
(1, 1),  -- Clean Code - Martin
(2, 3),  -- Design Patterns - Gamma
(2, 4),  -- Design Patterns - Booch
(2, 10), -- Design Patterns - Johnson
(3, 7),  -- Algorithms - Cormen
(4, 6),  -- SICP - Linn
(5, 8),  -- Art of Programming - Knuth
(6, 9),  -- Networks - Tanenbaum
(7, 9),  -- OS - Tanenbaum
(8, 2),  -- Refactoring - Fowler
(18, 2), -- Microservicios - Fowler
(19, 1), -- JS Good Parts - Martin
(20, 9); -- ML - Tanenbaum

-- ============================================
-- INVENTARIO (ejemplares físicos)
-- ============================================
INSERT INTO inventario (libro_id, codigo_ejemplar, estado, ubicacion_estante) VALUES
(1, 'LIB-001-01', 'DISPONIBLE', 'A-01'), (1, 'LIB-001-02', 'DISPONIBLE', 'A-01'), (1, 'LIB-001-03', 'DISPONIBLE', 'A-01'), (1, 'LIB-001-04', 'DISPONIBLE', 'A-01'), (1, 'LIB-001-05', 'DISPONIBLE', 'A-01'),
(2, 'LIB-002-01', 'DISPONIBLE', 'A-02'), (2, 'LIB-002-02', 'PRESTADO', 'A-02'), (2, 'LIB-002-03', 'DISPONIBLE', 'A-02'),
(3, 'LIB-003-01', 'DISPONIBLE', 'A-03'), (3, 'LIB-003-02', 'DISPONIBLE', 'A-03'), (3, 'LIB-003-03', 'PRESTADO', 'A-03'), (3, 'LIB-003-04', 'DISPONIBLE', 'A-03'),
(4, 'LIB-004-01', 'DISPONIBLE', 'A-04'), (4, 'LIB-004-02', 'DISPONIBLE', 'A-04'),
(5, 'LIB-005-01', 'DISPONIBLE', 'A-05'), (5, 'LIB-005-02', 'DISPONIBLE', 'A-05'),
(6, 'LIB-006-01', 'DISPONIBLE', 'A-06'), (6, 'LIB-006-02', 'DISPONIBLE', 'A-06'), (6, 'LIB-006-03', 'PRESTADO', 'A-06'), (6, 'LIB-006-04', 'DISPONIBLE', 'A-06'),
(7, 'LIB-007-01', 'DISPONIBLE', 'A-07'), (7, 'LIB-007-02', 'PRESTADO', 'A-07'), (7, 'LIB-007-03', 'DISPONIBLE', 'A-07'),
(8, 'LIB-008-01', 'DISPONIBLE', 'A-08'), (8, 'LIB-008-02', 'DISPONIBLE', 'A-08'), (8, 'LIB-008-03', 'DISPONIBLE', 'A-08'),
(9, 'LIB-009-01', 'DISPONIBLE', 'B-01'), (9, 'LIB-009-02', 'PRESTADO', 'B-01'), (9, 'LIB-009-03', 'DISPONIBLE', 'B-01'), (9, 'LIB-009-04', 'DISPONIBLE', 'B-01'), (9, 'LIB-009-05', 'DISPONIBLE', 'B-01'), (9, 'LIB-009-06', 'DANADO', 'B-01'),
(10, 'LIB-010-01', 'DISPONIBLE', 'B-02'), (10, 'LIB-010-02', 'DISPONIBLE', 'B-02'), (10, 'LIB-010-03', 'DISPONIBLE', 'B-02'), (10, 'LIB-010-04', 'DISPONIBLE', 'B-02'), (10, 'LIB-010-05', 'DISPONIBLE', 'B-02'),
(11, 'LIB-011-01', 'DISPONIBLE', 'C-01'), (11, 'LIB-011-02', 'PRESTADO', 'C-01'), (11, 'LIB-011-03', 'DISPONIBLE', 'C-01'),
(12, 'LIB-012-01', 'DISPONIBLE', 'C-02'), (12, 'LIB-012-02', 'DISPONIBLE', 'C-02'), (12, 'LIB-012-03', 'DISPONIBLE', 'C-02'),
(13, 'LIB-013-01', 'DISPONIBLE', 'D-01'), (13, 'LIB-013-02', 'DISPONIBLE', 'D-01'), (13, 'LIB-013-03', 'DISPONIBLE', 'D-01'), (13, 'LIB-013-04', 'DISPONIBLE', 'D-01'),
(14, 'LIB-014-01', 'DISPONIBLE', 'D-02'), (14, 'LIB-014-02', 'DISPONIBLE', 'D-02'), (14, 'LIB-014-03', 'DISPONIBLE', 'D-02'), (14, 'LIB-014-04', 'DISPONIBLE', 'D-02'),
(15, 'LIB-015-01', 'DISPONIBLE', 'E-01'), (15, 'LIB-015-02', 'DISPONIBLE', 'E-01'), (15, 'LIB-015-03', 'DISPONIBLE', 'E-01'),
(16, 'LIB-016-01', 'DISPONIBLE', 'E-02'), (16, 'LIB-016-02', 'DISPONIBLE', 'E-02'),
(17, 'LIB-017-01', 'DISPONIBLE', 'E-03'), (17, 'LIB-017-02', 'DISPONIBLE', 'E-03'), (17, 'LIB-017-03', 'DISPONIBLE', 'E-03'),
(18, 'LIB-018-01', 'DISPONIBLE', 'A-09'), (18, 'LIB-018-02', 'DISPONIBLE', 'A-09'), (18, 'LIB-018-03', 'DISPONIBLE', 'A-09'), (18, 'LIB-018-04', 'DISPONIBLE', 'A-09'),
(19, 'LIB-019-01', 'DISPONIBLE', 'A-10'), (19, 'LIB-019-02', 'DISPONIBLE', 'A-10'), (19, 'LIB-019-03', 'DISPONIBLE', 'A-10'),
(20, 'LIB-020-01', 'DISPONIBLE', 'A-11'), (20, 'LIB-020-02', 'PRESTADO', 'A-11'), (20, 'LIB-020-03', 'DISPONIBLE', 'A-11');

-- ============================================
-- PRÉSTAMOS (10 registros)
-- ============================================
INSERT INTO prestamos (usuario_id, inventario_id, fecha_prestamo, fecha_vencimiento, estado) VALUES
(3, 7, '2026-07-10 10:00:00', '2026-07-17 10:00:00', 'ACTIVO'),
(3, 11, '2026-07-11 11:00:00', '2026-07-18 11:00:00', 'ACTIVO'),
(4, 22, '2026-07-12 09:00:00', '2026-07-19 09:00:00', 'ACTIVO'),
(4, 29, '2026-07-13 14:00:00', '2026-07-20 14:00:00', 'ACTIVO'),
(5, 16, '2026-07-14 08:00:00', '2026-07-21 08:00:00', 'ACTIVO'),
(5, 38, '2026-07-14 10:00:00', '2026-07-21 10:00:00', 'ACTIVO'),
(6, 69, '2026-07-15 09:30:00', '2026-07-22 09:30:00', 'ACTIVO'),
(3, 2, '2026-07-01 10:00:00', '2026-07-08 10:00:00', 'DEVUELTO'),
(4, 8, '2026-06-28 11:00:00', '2026-07-05 11:00:00', 'VENCIDO'),
(5, 12, '2026-06-25 12:00:00', '2026-07-02 12:00:00', 'DEVUELTO');

UPDATE prestamos SET fecha_devolucion = '2026-07-08 16:00:00' WHERE id = 8;
UPDATE prestamos SET fecha_devolucion = '2026-07-02 15:00:00' WHERE id = 10;

-- ============================================
-- RESERVAS (5 registros)
-- ============================================
INSERT INTO reservas (usuario_id, libro_id, fecha_reserva, fecha_vencimiento, estado) VALUES
(6, 1, '2026-07-16 10:00:00', '2026-07-18 10:00:00', 'PENDIENTE'),
(4, 7, '2026-07-16 11:00:00', '2026-07-18 11:00:00', 'PENDIENTE'),
(5, 20, '2026-07-15 14:00:00', '2026-07-17 14:00:00', 'CONFIRMADA'),
(3, 11, '2026-07-14 09:00:00', '2026-07-16 09:00:00', 'COMPLETADA'),
(6, 9, '2026-07-13 08:00:00', '2026-07-15 08:00:00', 'CANCELADA');

-- ============================================
-- MULTAS (3 registros)
-- ============================================
INSERT INTO multas (prestamo_id, usuario_id, monto, pagada, concepto) VALUES
(9, 4, 25.00, false, 'Multa por retraso de 5 días en devolución'),
(1, 3, 10.00, false, 'Multa por retraso de 2 días'),
(10, 5, 15.00, true, 'Multa por retraso de 3 días');

UPDATE multas SET fecha_pago = '2026-07-03 12:00:00' WHERE id = 3;

-- ============================================
-- NOTIFICACIONES
-- ============================================
INSERT INTO notificaciones (usuario_id, titulo, mensaje, leida, tipo) VALUES
(3, 'Préstamo próximo a vencer', 'El libro "Design Patterns" vence mañana. Por favor devuélvelo a tiempo.', false, 'RECORDATORIO'),
(4, 'Multa pendiente', 'Tienes una multa pendiente de $25.00 por retraso en la devolución.', false, 'MULTA'),
(5, 'Reserva confirmada', 'Tu reserva del libro "Machine Learning" ha sido confirmada.', true, 'RESERVA');

-- ============================================
-- CONFIGURACIÓN
-- ============================================
INSERT INTO configuracion (clave, valor, descripcion) VALUES
('dias_prestamo', '7', 'Días máximos para préstamo de libros'),
('monto_multa_diario', '5.00', 'Monto de multa por día de retraso'),
('max_prestamos_activos', '5', 'Cantidad máxima de préstamos activos por usuario'),
('correo_institucional', 'biblioteca@universidad.edu', 'Correo de contacto de la biblioteca'),
('horario_atencion', 'Lun-Vie 8:00-18:00, Sáb 9:00-13:00', 'Horario de atención al público');

-- ============================================
-- QR CÓDIGOS (para algunos libros)
-- ============================================
INSERT INTO qr_codigos (libro_id, codigo, activo) VALUES
(1, 'QR-978-0132350884', true),
(2, 'QR-978-0201633610', true),
(3, 'QR-978-0262033848', true),
(9, 'QR-978-6073206037', true),
(15, 'QR-978-8437604947', true);
