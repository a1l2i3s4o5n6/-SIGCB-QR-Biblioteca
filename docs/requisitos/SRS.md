# Software Requirements Specification (SRS)
## SIGCB-QR — Sistema Integral de Gestión Bibliotecaria

**Versión:** v0.9.0-rc
**Fecha:** 2026-07-30
**Conforme a:** ISO/IEC/IEEE 29148:2018

---

## 1. Introducción

### 1.1 Propósito
Este documento especifica los requisitos funcionales y no funcionales del Sistema Integral de Gestión, Control y Seguimiento de Lectura Interna en la Biblioteca Universitaria mediante Códigos QR (SIGCB-QR). El sistema automatiza los procesos bibliotecarios: autenticación, gestión de catálogo, préstamos, reservas, multas y reportes.

### 1.2 Alcance
SIGCB-QR es una aplicación web de arquitectura cliente-servidor con backend Spring Boot 3.4, frontend Laravel 13, base de datos PostgreSQL 16 y cache Redis 7. El sistema expone una API REST documentada con OpenAPI 3.0 y utiliza autenticación stateless JWT en cookie HttpOnly.

### 1.3 Definiciones
| Término | Definición |
|---------|------------|
| JWT | JSON Web Token (RFC 7519) |
| JPA | Jakarta Persistence API |
| ORM | Object-Relational Mapping |
| CRUD | Create, Read, Update, Delete |
| TTL | Time To Live |
| Hit ratio | Proporción de aciertos de cache |

### 1.4 Referencias
- ISO/IEC/IEEE 29148:2018 — Ingeniería de requisitos
- RFC 7519 — JSON Web Token
- RFC 7807 — Problem Details for HTTP APIs
- INCOSE Guide to Writing Requirements v4
- OWASP Cheat Sheet — SQL Injection Prevention
- Cockburn — Writing Effective Use Cases

### 1.5 Resumen
El SRS se organiza en: descripción global (sección 2), requisitos específicos funcionales (3), no funcionales (4), de interfaz externa (5), de seguridad (6) y de calidad (7).

---

## 2. Descripción global

### 2.1 Perspectiva del producto
SIGCB-QR es un sistema independiente que reemplaza procesos manuales bibliotecarios. Se compone de:
- API REST (sigcb-qr-api): Java 21, Spring Boot 3.4.4
- Frontend (SIGCB-QR): Laravel 13, PHP 8.4
- Base de datos: PostgreSQL 16
- Cache: Redis 7
- Contenedores: Docker Compose

### 2.2 Funciones del sistema
- Autenticación stateless con JWT (login, registro, logout)
- CRUD de usuarios (admin)
- CRUD de material bibliográfico (libros, autores, editoriales, categorías)
- Gestión de ejemplares (inventario)
- Préstamos, devoluciones y renovaciones
- Reservas
- Multas y sanciones
- Reportes y estadísticas de dashboard
- Catalogación (facultades, carreras)

### 2.3 Características de usuarios
| Rol | Descripción |
|-----|-------------|
| ADMIN | Administración completa del sistema |
| BIBLIOTECARIO | Gestión de préstamos, catálogo y reportes |
| ESTUDIANTE | Consulta de catálogo, préstamos y reservas |

### 2.4 Restricciones
- Autenticación stateless (sin sesiones HTTP)
- JWT transmitido en cookie HttpOnly + Secure + SameSite=Strict
- Base de datos gestionada exclusivamente por Flyway (ddl-auto=validate)
- Operaciones no elementales mediante stored procedures
- Contenedores Docker anclados por digest SHA256

### 2.5 Supuestos y dependencias
- Disponibilidad de Docker y Docker Compose
- Conexión de red entre contenedores
- Semillas deterministas fijadas

---

## 3. Requisitos específicos — Funcionales

### REQ-F-001 — Autenticación de usuarios
| Atributo | Valor |
|----------|-------|
| Tipo | Funcional |
| Prioridad (MoSCoW) | Must |
| Enunciado | Al recibir credenciales válidas, el sistema deberá emitir un JWT firmado en cookie HttpOnly con los claims estándar (iss, sub, aud, exp, nbf, iat, jti) |
| Rationale | La autenticación stateless elimina la necesidad de almacenar sesiones en servidor |
| Origen | Stakeholder: Administración bibliotecaria |
| Criterio de aceptación | Login exitoso en menos de 2s con JWT válido |
| Método de verificación | Test (AuthControllerTest) |
| Trazabilidad | HU-01, CU-01 |

### REQ-F-002 — Registro de usuarios
| Atributo | Valor |
|----------|-------|
| Tipo | Funcional |
| Prioridad | Must |
| Enunciado | Al recibir datos de registro válidos, el sistema deberá crear un usuario con rol ESTUDIANTE por defecto y emitir un JWT |
| Rationale | Permitir auto-registro de estudiantes |
| Criterio de aceptación | Registro exitoso con validación de email único |
| Método de verificación | Test |
| Trazabilidad | HU-02, CU-01 |

### REQ-F-003 — Cierre de sesión
| Atributo | Valor |
|----------|-------|
| Tipo | Funcional |
| Prioridad | Must |
| Enunciado | Al recibir una solicitud de logout, el sistema deberá agregar el JTI del token a la blacklist Redis e invalidar la cookie |
| Rationale | Garantizar que el token no pueda reutilizarse |
| Criterio de aceptación | Token blacklistado no es válido para peticiones posteriores |
| Método de verificación | Test |
| Trazabilidad | HU-03, CU-01 |

### REQ-F-004 — CRUD de usuarios (Admin)
| Atributo | Valor |
|----------|-------|
| Tipo | Funcional |
| Prioridad | Must |
| Enunciado | El sistema deberá permitir al administrador crear, leer, actualizar y desactivar usuarios |
| Rationale | Gestión centralizada de cuentas |
| Criterio de aceptación | CRUD completo con validaciones |
| Método de verificación | Test |
| Trazabilidad | HU-04 |

### REQ-F-005 — CRUD de material bibliográfico
| Atributo | Valor |
|----------|-------|
| Tipo | Funcional |
| Prioridad | Must |
| Enunciado | El sistema deberá permitir crear, leer, actualizar y desactivar libros con sus metadatos (título, ISBN, autor, editorial, categoría) |
| Rationale | Catalogación del acervo bibliotecario |
| Criterio de aceptación | CRUD completo con paginación y búsqueda |
| Método de verificación | Test |
| Trazabilidad | HU-05, CU-02 |

### REQ-F-006 — Préstamo de ejemplares
| Atributo | Valor |
|----------|-------|
| Tipo | Funcional |
| Prioridad | Must |
| Enunciado | Al recibir una solicitud de préstamo válida, el sistema deberá crear el préstamo, actualizar el estado del ejemplar y disminuir los ejemplares disponibles |
| Rationale | Control de circulación de material |
| Criterio de aceptación | Préstamo creado en menos de 2s, máximo 5 activos por usuario |
| Método de verificación | Test + stored procedure |
| Trazabilidad | HU-06, CU-03 |

### REQ-F-007 — Devolución de préstamo
| Atributo | Valor |
|----------|-------|
| Tipo | Funcional |
| Prioridad | Must |
| Enunciado | Al recibir una devolución, el sistema deberá actualizar el estado del préstamo, liberar el ejemplar y generar multa si está vencido |
| Rationale | Control de inventario y penalizaciones |
| Criterio de aceptación | Devolución procesada en menos de 2s |
| Método de verificación | Test + stored procedure |
| Trazabilidad | HU-07, CU-04 |

### REQ-F-008 — Reporte de préstamos diarios
| Atributo | Valor |
|----------|-------|
| Tipo | Funcional |
| Prioridad | Should |
| Enunciado | El sistema deberá generar un reporte de préstamos del día actual con datos de usuario y libro |
| Rationale | Seguimiento de actividad diaria |
| Criterio de aceptación | Reporte generado en menos de 3s |
| Método de verificación | Test + stored procedure |
| Trazabilidad | HU-08 |

---

## 4. Requisitos no funcionales

### REQ-NF-001 — Rendimiento — Listado de libros
| Atributo | Valor |
|----------|-------|
| Tipo | No funcional |
| Prioridad | Must |
| Enunciado | El endpoint GET /api/libros deberá responder con p95 ≤ 200 ms con cache Redis caliente |
| Rationale | Experiencia de usuario fluida en navegación de catálogo |
| Criterio de aceptación | p95 < 200ms en 3 corridas con cache caliente |
| Método de verificación | Test de rendimiento (k6) |
| Trazabilidad | Evidencia: docs/mediciones/perf/ |

### REQ-NF-002 — Seguridad — Autenticación
| Atributo | Valor |
|----------|-------|
| Tipo | No funcional |
| Prioridad | Must |
| Enunciado | El sistema deberá implementar autenticación JWT sin sesiones, con cookie HttpOnly + Secure + SameSite=Strict |
| Rationale | Mitigación de ataques XSS y CSRF |
| Criterio de aceptación | Ningún endpoint protegido accesible sin JWT válido |
| Método de verificación | Test de seguridad |
| Trazabilidad | OWASP Top 10 |

### REQ-NF-003 — Seguridad — Protección contra inyección SQL
| Atributo | Valor |
|----------|-------|
| Tipo | No funcional |
| Prioridad | Must |
| Enunciado | Ninguna consulta SQL deberá concatenar entrada de usuario; toda parametrización será mediante parámetros nombrados |
| Rationale | Prevención de inyección SQL |
| Criterio de aceptación | Escaneo SAST sin hallazgos de inyección SQL |
| Método de verificación | Análisis estático (inspection) |
| Trazabilidad | OWASP Cheat Sheet |

### REQ-NF-004 — Disponibilidad — Contenedores
| Atributo | Valor |
|----------|-------|
| Tipo | No funcional |
| Prioridad | Must |
| Enunciado | El sistema deberá poder reconstruirse en menos de 2 minutos desde una clonación limpia con un solo comando (make up) |
| Rationale | Reproducibilidad por terceros |
| Criterio de aceptación | docker-compose up exitoso en ambiente limpio |
| Método de verificación | Demostración (demonstration) |
| Trazabilidad | Docker Compose |

### REQ-NF-005 — Mantenibilidad — Cobertura de pruebas
| Atributo | Valor |
|----------|-------|
| Tipo | No funcional |
| Prioridad | Should |
| Enunciado | El backend deberá tener cobertura de línea ≥ 30% medida por JaCoCo |
| Rationale | Garantía mínima de calidad |
| Criterio de aceptación | Reporte JaCoCo ≥ 30% |
| Método de verificación | Test (JaCoCo) |
| Trazabilidad | pom.xml |

---

## 5. Requisitos de interfaz externa

### 5.1 Interfaz de usuario
Frontend Laravel 13 con Tailwind CSS, accesible vía navegador en puerto 8000.

### 5.2 Interfaz API REST
Documentación OpenAPI 3.0 en `/api-docs` e interfaz Swagger UI en `/swagger-ui.html`.

### 5.3 Interfaz de base de datos
Conexión JDBC a PostgreSQL 16, esquema gestionado por Flyway.

---

## 6. Requisitos de seguridad

- JWT con claims estándar RFC 7519
- Cookie HttpOnly + Secure + SameSite=Strict
- Blacklist de JTI revocados en Redis
- CORS restringido a orígenes configurados
- BCrypt para almacenamiento de contraseñas
- Parámetros nombrados en todas las consultas
- Procedimientos almacenados sin SQL dinámico

---

## 7. Calidad de software

### 7.1 Características del conjunto de requisitos (INCOSE C10-C15)
| Atributo | Cumplimiento |
|----------|-------------|
| Complete (C10) | Todos los escenarios principales cubiertos |
| Consistent (C11) | Sin contradicciones entre requisitos |
| Feasible (C12) | Implementable con la pila tecnológica actual |
| Comprehensible (C13) | Redactado en lenguaje claro |
| Able to be validated (C14) | Cada requisito tiene método de verificación |
| Correct (C15) | Aprobado por stakeholders |

---
*Fin del SRS*
