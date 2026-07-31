# HU-02: Registro de usuarios

**Formato:** Connextra
**Rol:** Visitante
**Objetivo:** Registrarme como usuario del sistema
**Beneficio:** Obtener acceso como estudiante a los servicios bibliotecarios

## Criterios de aceptación (Gherkin)

```gherkin
Feature: Registro
  Scenario: Registro exitoso
    Given un visitante con datos válidos (nombre, email, contraseña)
    When envía POST /api/auth/register
    Then el sistema responde con HTTP 201
    And se crea un usuario con rol ESTUDIANTE
    And se emite un JWT en cookie

  Scenario: Registro con email duplicado
    Given un usuario existente con email "estudiante@test.com"
    When otro visitante intenta registrar el mismo email
    Then el sistema responde con HTTP 400
    And el mensaje indica que el email ya está registrado
```
