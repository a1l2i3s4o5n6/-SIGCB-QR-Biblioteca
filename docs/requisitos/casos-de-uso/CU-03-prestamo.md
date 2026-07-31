# CU-03: Prestamo de ejemplares

**Nivel:** 2 (Etapa de objetivo de usuario)
**Actor principal:** Bibliotecario
**Precondicion:** Autenticado con rol BIBLIOTECARIO o ADMIN
**Postcondicion:** Prestamo registrado, ejemplar marcado como PRESTADO

## Escenario principal de exito

1. El bibliotecario selecciona un usuario del sistema
2. El bibliotecario selecciona un ejemplar disponible del inventario
3. El sistema valida que el usuario no tenga mas de 5 prestamos activos
4. El sistema valida que el ejemplar este DISPONIBLE
5. El sistema crea el registro de prestamo con fecha actual y vencimiento en 7 dias
6. El sistema cambia el estado del ejemplar a PRESTADO
7. El sistema disminuye el contador de ejemplares disponibles del libro
8. El sistema retorna el detalle del prestamo creado

## Extensiones

### 3a. Limite de prestamos alcanzado
3a1. El sistema rechaza la operacion con HTTP 400
3a2. Mensaje: "El usuario tiene demasiados prestamos activos"

### 4a. Ejemplar no disponible
4a1. El sistema rechaza la operacion con HTTP 400
4a2. Mensaje: "El ejemplar no esta disponible"

### 5a. Error de base de datos
5a1. El sistema revierte la transaccion
5a2. El sistema retorna HTTP 500 con ProblemDetail

## Requisitos asociados
- REQ-F-006 (Prestamo de ejemplares)
- REQ-NF-006 (Control de acceso por roles)
- REQ-R-003 (Tiempo de respuesta de SP)
