# CU-04: Devolucion de prestamo

**Nivel:** 2 (Etapa de objetivo de usuario)
**Actor principal:** Bibliotecario
**Precondicion:** Autenticado con rol BIBLIOTECARIO o ADMIN
**Postcondicion:** Prestamo cerrado, inventario actualizado, multa generada si aplica

## Escenario principal de exito

1. El bibliotecario localiza el prestamo activo por su ID
2. El sistema verifica que el prestamo exista y este ACTIVO
3. El sistema registra la fecha de devolucion (fecha actual)
4. El sistema cambia el estado del prestamo a DEVUELTO
5. El sistema cambia el estado del ejemplar a DISPONIBLE
6. El sistema incrementa el contador de ejemplares disponibles del libro
7. Si la fecha actual supera la fecha de vencimiento, el sistema genera una multa
8. El sistema retorna el detalle del prestamo actualizado

## Extensiones

### 2a. Prestamo no encontrado
2a1. El sistema retorna HTTP 404 con ProblemDetail

### 2b. Prestamo ya devuelto
2b1. El sistema rechaza con HTTP 400
2b2. Mensaje: "El prestamo ya fue devuelto"

### 7a. Calculo de multa
7a1. Dias de retraso = fecha_actual - fecha_vencimiento
7a2. Monto = dias_retraso * $0.50
7a3. La multa se registra con estado PENDIENTE

## Requisitos asociados
- REQ-F-007 (Devolucion de prestamo)
- REQ-R-003 (Tiempo de respuesta de SP)
