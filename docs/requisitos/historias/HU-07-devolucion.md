# HU-07: Devolucion de prestamo

**Formato:** Connextra
**Rol:** Bibliotecario
**Objetivo:** Registrar la devolucion de un ejemplar prestado
**Beneficio:** Actualizar el inventario y gestionar multas por retraso

## Criterios de aceptacion (Gherkin)

```gherkin
Feature: Devolucion de prestamo
  Scenario: Devolucion en fecha
    Given un prestamo activo cuya fecha de vencimiento no ha pasado
    When el bibliotecario registra la devolucion
    Then el sistema marca el prestamo como DEVUELTO
    And registra la fecha de devolucion
    And el ejemplar vuelve a estar DISPONIBLE
    And los ejemplares disponibles del libro aumentan en 1
    And no se genera ninguna multa

  Scenario: Devolucion con retraso
    Given un prestamo activo cuya fecha de vencimiento ya paso
    When el bibliotecario registra la devolucion
    Then el sistema genera una multa de $0.50 por dia de retraso
    And la multa queda registrada como PENDIENTE
```
