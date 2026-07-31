# HU-09: Reportes bibliotecarios

**Formato:** Connextra
**Rol:** Bibliotecario/Administrador
**Objetivo:** Generar reportes de actividad
**Beneficio:** Obtener datos para la toma de decisiones

## Criterios de aceptacion (Gherkin)

```gherkin
Feature: Reportes
  Scenario: Reporte de prestamos diarios
    Given un bibliotecario autenticado
    When solicita GET /api/reportes/prestamos-diarios
    Then recibe el total de prestamos del dia actual
    And el detalle de cada prestamo con usuario y libro

  Scenario: Reporte de libros mas solicitados
    Given un bibliotecario autenticado
    When solicita GET /api/reportes/libros-mas-solicitados
    Then recibe el top 10 de libros con mayor cantidad de prestamos

  Scenario: Reporte de multas cobradas
    Given un administrador autenticado
    When solicita GET /api/reportes/multas-cobradas
    Then recibe el total cobrado en el mes actual
```
