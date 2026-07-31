# HU-01: Autenticación de usuarios

**Formato:** Connextra
**Rol:** Usuario del sistema
**Objetivo:** Iniciar sesión con credenciales
**Beneficio:** Acceder a las funcionalidades del sistema según mi rol

**Criterios INVEST:** ✅ Independent ✅ Negotiable ✅ Valuable ✅ Estimable ✅ Small ✅ Testable

## Criterios de aceptación (Gherkin)

```gherkin
Feature: Autenticación
  Scenario: Inicio de sesión exitoso
    Given un usuario registrado con email "admin@dev.com" y contraseña "admin123"
    When el usuario envía POST /api/auth/login con email y contraseña válidos
    Then el sistema responde con HTTP 200
    And la respuesta incluye un JWT en cookie HttpOnly
    And el JWT contiene los claims iss, sub, aud, exp, nbf, iat, jti

  Scenario: Inicio de sesión con credenciales inválidas
    Given un usuario registrado con email "admin@dev.com"
    When el usuario envía POST /api/auth/login con contraseña incorrecta
    Then el sistema responde con HTTP 401
    And la respuesta sigue el formato ProblemDetail RFC 7807
```
