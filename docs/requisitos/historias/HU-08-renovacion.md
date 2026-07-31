# HU-08: Renovacion de prestamo

**Formato:** Connextra
**Rol:** Bibliotecario
**Objetivo:** Renovar un prestamo activo
**Beneficio:** Extender el periodo de prestamo sin devolver el material

## Criterios de aceptacion (Gherkin)

```gherkin
Feature: Renovacion de prestamo
  Scenario: Renovacion exitosa
    Given un prestamo activo
    When el bibliotecario solicita la renovacion
    Then el sistema marca el prestamo original como RENOVADO
    And crea un nuevo prestamo con fecha de vencimiento extendida 7 dias
    And el nuevo prestamo queda en estado ACTIVO

  Scenario: Renovacion de prestamo no activo
    Given un prestamo en estado DEVUELTO
    When el bibliotecario intenta renovarlo
    Then el sistema rechaza la operacion con HTTP 400
```
