# HU-10: Dashboard de estadisticas

**Formato:** Connextra
**Rol:** Usuario autenticado
**Objetivo:** Visualizar estadisticas generales del sistema
**Beneficio:** Obtener una vision rapida del estado de la biblioteca

## Criterios de aceptacion (Gherkin)

```gherkin
Feature: Dashboard
  Scenario: Visualizar estadisticas
    Given un usuario autenticado
    When solicita GET /api/dashboard/stats
    Then recibe libros prestados hoy
    And libros disponibles
    And estudiantes activos
    And reservas pendientes
    And multas pendientes
    And total acumulado de multas
```
