# CU-05: Generacion de reportes

**Nivel:** 1 (Resumen)
**Actor principal:** Bibliotecario/Administrador
**Precondicion:** Autenticado con rol BIBLIOTECARIO o ADMIN
**Postcondicion:** Reporte generado y entregado al solicitante

## Escenario principal de exito

1. El usuario solicita un tipo de reporte (prestamos diarios, libros mas solicitados, multas cobradas)
2. El sistema ejecuta el stored procedure correspondiente
3. El sistema recopila los datos agregados
4. El sistema retorna los datos en formato JSON

## Extensiones

### 2a. Sin datos para el periodo
2a1. El sistema retorna un reporte vacio con total = 0

### 3a. Error de base de datos
3a1. El sistema retorna HTTP 500 con ProblemDetail

## Requisitos asociados
- REQ-F-009 (Reporte de prestamos diarios)
- REQ-NF-006 (Control de acceso por roles)
