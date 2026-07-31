# HU-03: Cierre de sesión

**Formato:** Connextra
**Rol:** Usuario autenticado
**Objetivo:** Cerrar sesión
**Beneficio:** Invalidar mi token actual y proteger mi cuenta

## Criterios de aceptación (Gherkin)

```gherkin
Feature: Cierre de sesión
  Scenario: Logout exitoso
    Given un usuario autenticado con JWT válido
    When envía POST /api/auth/logout
    Then el sistema responde con HTTP 200
    And el JTI del token se agrega a la blacklist
    And la cookie se elimina

  Scenario: Acceso post-logout
    Given un usuario cuyo JWT fue invalidado
    When intenta acceder a /api/libros
    Then el sistema responde con HTTP 401
```
