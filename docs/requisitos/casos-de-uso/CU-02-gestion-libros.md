# CU-02: Gestión de material bibliográfico

**Nivel:** 2 (Etapa de objetivo de usuario)
**Actor principal:** Bibliotecario
**Precondición:** Autenticado con rol BIBLIOTECARIO o ADMIN

## Escenario principal de éxito

1. El bibliotecario solicita el listado de libros (paginado)
2. El sistema retorna los libros activos con paginación, desde caché Redis si está caliente
3. El bibliotecario busca un libro por título
4. El sistema retorna resultados de búsqueda
5. El bibliotecario crea un nuevo libro con título, ISBN, editorial, categoría y autores
6. El sistema valida los datos y crea el libro con ejemplares disponibles = totales
7. El bibliotecario actualiza datos de un libro existente
8. El bibliotecario desactiva un libro (eliminación lógica)

## Extensiones

### 5a. Datos inválidos
5a1. El sistema responde con HTTP 400 y errores de validación

### 7a. Libro no encontrado
7a1. El sistema responde con HTTP 404 y ProblemDetail

## Requisitos asociados
- REQ-F-005 (CRUD libros)
- REQ-NF-001 (Rendimiento con caché)
