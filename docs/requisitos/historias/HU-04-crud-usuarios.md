# HU-04: Gestión de usuarios

**Formato:** Connextra
**Rol:** Administrador
**Objetivo:** Gestionar usuarios del sistema
**Beneficio:** Controlar quién tiene acceso y con qué rol

## Criterios de aceptación (Gherkin)

```gherkin
Feature: CRUD de usuarios
  Scenario: Listar usuarios
    Given un administrador autenticado
    When envía GET /api/usuarios
    Then recibe lista paginada de usuarios

  Scenario: Crear usuario (admin)
    Given un administrador autenticado
    When envía POST /api/usuarios con datos válidos
    Then el sistema crea el usuario con el rol especificado

  Scenario: Usuario no admin no puede gestionar usuarios
    Given un estudiante autenticado
    When envía GET /api/usuarios
    Then el sistema responde con HTTP 403
```
