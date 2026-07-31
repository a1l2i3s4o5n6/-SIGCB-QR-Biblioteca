# HU-06: Prestamo de ejemplares

**Formato:** Connextra
**Rol:** Bibliotecario
**Objetivo:** Registrar el prestamo de un ejemplar a un usuario
**Beneficio:** Controlar la circulacion del material bibliotecario

## Criterios de aceptacion (Gherkin)

```gherkin
Feature: Prestamo de ejemplares
  Scenario: Prestamo exitoso
    Given un usuario activo y un ejemplar disponible
    When el bibliotecario registra el prestamo
    Then el sistema crea el prestamo con fecha de vencimiento en 7 dias
    And el estado del ejemplar cambia a PRESTADO
    And los ejemplares disponibles del libro disminuyen en 1

  Scenario: Prestamo con ejemplar no disponible
    Given un ejemplar en estado PRESTADO
    When el bibliotecario intenta prestarlo
    Then el sistema rechaza la operacion con HTTP 400
    And el mensaje indica que el ejemplar no esta disponible

  Scenario: Prestamo con usuario que supera el limite
    Given un usuario con 5 prestamos activos
    When el bibliotecario intenta registrar un nuevo prestamo
    Then el sistema rechaza la operacion con HTTP 400
    And el mensaje indica limite de prestamos alcanzado
```
