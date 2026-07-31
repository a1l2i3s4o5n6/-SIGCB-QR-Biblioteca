# HU-05: Gestión de material bibliográfico

**Formato:** Connextra
**Rol:** Bibliotecario/Administrador
**Objetivo:** Gestionar libros, autores, editoriales y categorías
**Beneficio:** Mantener actualizado el catálogo bibliotecario

## Criterios de aceptación (Gherkin)

```gherkin
Feature: CRUD de libros
  Scenario: Listar libros con paginación
    Given un usuario autenticado
    When envía GET /api/libros?page=0&size=10
    Then recibe página de libros activos

  Scenario: Buscar libros por título
    Given un usuario autenticado
    When envía GET /api/libros/buscar?q=programación
    Then recibe libros cuyo título contiene "programación"

  Scenario: Crear libro (bibliotecario)
    Given un bibliotecario autenticado
    When envía POST /api/libros con datos válidos
    Then el sistema crea el libro con ejemplares disponibles = totales

  Scenario: Estudiante no puede crear libros
    Given un estudiante autenticado
    When envía POST /api/libros
    Then el sistema responde con HTTP 403
```
