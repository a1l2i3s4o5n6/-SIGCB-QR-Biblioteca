# Software Requirements Specification (SRS)
## SIGCB-QR -- Sistema Integral de Gestion Bibliotecaria

**Version:** v1.0.0
**Fecha:** 2026-09-03
**Conforme a:** ISO/IEC/IEEE 29148:2018
**Estado:** Aprobado para la Entrega Final
**Firmado por:** Arias Moreira, Maybelin Gregoria; Romero Mendez, Bryam Steven;
Zambrano Moreira, Alison Ariana (Universidad Tecnica Estatal de Quevedo)

> **Cambio principal respecto de v0.9.0-rc.** Aquella version especificaba trece
> requisitos mientras la matriz de trazabilidad trazaba veintisiete, de modo que
> catorce requisitos trazados carecian de especificacion. Esta version los
> incorpora: REQ-F-009 a REQ-F-011, REQ-NF-006 a REQ-NF-010, REQ-R-001 a
> REQ-R-004 y REQ-U-001 y REQ-U-002. Se declara ademas el estado real de los que
> no estan verificados, en lugar de omitirlos.

---

## 1. Introduccion

### 1.1 Proposito
Este documento especifica los requisitos funcionales y no funcionales del Sistema Integral de Gestion, Control y Seguimiento de Lectura Interna en la Biblioteca Universitaria mediante Codigos QR (SIGCB-QR). El sistema automatiza los procesos bibliotecarios: autenticacion, gestion de catalogo, prestamos, reservas, multas y reportes.

### 1.2 Alcance
SIGCB-QR es una aplicacion web de arquitectura cliente-servidor con backend Spring Boot 3.5, frontend Laravel 13, base de datos PostgreSQL 16 y cache Redis 7. El sistema expone una API REST documentada con OpenAPI 3.0 y utiliza autenticacion stateless JWT en cookie HttpOnly.

### 1.3 Definiciones
| Termino | Definicion |
|---------|------------|
| JWT | JSON Web Token (RFC 7519) |
| JPA | Jakarta Persistence API |
| ORM | Object-Relational Mapping |
| CRUD | Create, Read, Update, Delete |
| TTL | Time To Live |
| Hit ratio | Proporcion de aciertos de cache |

### 1.4 Referencias
- ISO/IEC/IEEE 29148:2018 -- Ingenieria de requisitos
- RFC 7519 -- JSON Web Token
- RFC 7807 -- Problem Details for HTTP APIs
- INCOSE Guide to Writing Requirements v4
- OWASP Cheat Sheet -- SQL Injection Prevention
- Cockburn -- Writing Effective Use Cases

### 1.5 Resumen
El SRS se organiza en: descripcion global (seccion 2), requisitos especificos funcionales (3), no funcionales (4), de interfaz externa (5), de seguridad (6) y de calidad (7).

---

## 2. Descripcion global

### 2.1 Perspectiva del producto
SIGCB-QR es un sistema independiente que reemplaza procesos manuales bibliotecarios. Se compone de:
- API REST (sigcb-qr-api): Java 21, Spring Boot 3.4.4
- Frontend (SIGCB-QR): Laravel 13, PHP 8.4
- Base de datos: PostgreSQL 16
- Cache: Redis 7
- Contenedores: Docker Compose

### 2.2 Funciones del sistema
- Autenticacion stateless con JWT (login, registro, logout)
- CRUD de usuarios (admin)
- CRUD de material bibliografico (libros, autores, editoriales, categorias)
- Gestion de ejemplares (inventario)
- Prestamos, devoluciones y renovaciones
- Reservas
- Multas y sanciones
- Reportes y estadisticas de dashboard
- Catalogacion (facultades, carreras)

### 2.3 Caracteristicas de usuarios
| Rol | Descripcion |
|-----|-------------|
| ADMIN | Administracion completa del sistema |
| BIBLIOTECARIO | Gestion de prestamos, catalogo y reportes |
| ESTUDIANTE | Consulta de catalogo, prestamos y reservas |

### 2.4 Restricciones
- Autenticacion stateless (sin sesiones HTTP)
- JWT transmitido en cookie HttpOnly + Secure + SameSite=Strict
- Base de datos gestionada exclusivamente por Flyway (ddl-auto=validate)
- Operaciones no elementales mediante stored procedures
- Contenedores Docker anclados por digest SHA256

### 2.5 Supuestos y dependencias
- Disponibilidad de Docker y Docker Compose
- Conexion de red entre contenedores
- Semillas deterministas fijadas

---

## 3. Requisitos especificos -- Funcionales

### REQ-F-001 -- Autenticacion de usuarios
| Atributo | Valor |
|----------|-------|
| Tipo | Funcional |
| Prioridad (MoSCoW) | Must |
| Enunciado | Al recibir credenciales validas, el sistema debera emitir un JWT firmado en cookie HttpOnly con los claims estandar (iss, sub, aud, exp, nbf, iat, jti) |
| Rationale | La autenticacion stateless elimina la necesidad de almacenar sesiones en servidor |
| Origen | Stakeholder: Administracion bibliotecaria |
| Criterio de aceptacion | Login exitoso en menos de 2s con JWT valido |
| Metodo de verificacion | Test (AuthControllerTest) |
| Trazabilidad | HU-01, CU-01 |

### REQ-F-002 -- Registro de usuarios
| Atributo | Valor |
|----------|-------|
| Tipo | Funcional |
| Prioridad | Must |
| Enunciado | Al recibir datos de registro validos, el sistema debera crear un usuario con rol ESTUDIANTE por defecto y emitir un JWT |
| Rationale | Permitir auto-registro de estudiantes |
| Criterio de aceptacion | Registro exitoso con validacion de email unico |
| Metodo de verificacion | Test |
| Trazabilidad | HU-02, CU-01 |

### REQ-F-003 -- Cierre de sesion
| Atributo | Valor |
|----------|-------|
| Tipo | Funcional |
| Prioridad | Must |
| Enunciado | Al recibir una solicitud de logout, el sistema debera agregar el JTI del token a la blacklist Redis e invalidar la cookie |
| Rationale | Garantizar que el token no pueda reutilizarse |
| Criterio de aceptacion | Token blacklistado no es valido para peticiones posteriores |
| Metodo de verificacion | Test |
| Trazabilidad | HU-03, CU-01 |

### REQ-F-004 -- CRUD de usuarios (Admin)
| Atributo | Valor |
|----------|-------|
| Tipo | Funcional |
| Prioridad | Must |
| Enunciado | El sistema debera permitir al administrador crear, leer, actualizar y desactivar usuarios |
| Rationale | Gestion centralizada de cuentas |
| Criterio de aceptacion | CRUD completo con validaciones |
| Metodo de verificacion | Test |
| Trazabilidad | HU-04 |

### REQ-F-005 -- CRUD de material bibliografico
| Atributo | Valor |
|----------|-------|
| Tipo | Funcional |
| Prioridad | Must |
| Enunciado | El sistema debera permitir crear, leer, actualizar y desactivar libros con sus metadatos (titulo, ISBN, autor, editorial, categoria) |
| Rationale | Catalogacion del acervo bibliotecario |
| Criterio de aceptacion | CRUD completo con paginacion y busqueda |
| Metodo de verificacion | Test |
| Trazabilidad | HU-05, CU-02 |

### REQ-F-006 -- Prestamo de ejemplares
| Atributo | Valor |
|----------|-------|
| Tipo | Funcional |
| Prioridad | Must |
| Enunciado | Al recibir una solicitud de prestamo valida, el sistema debera crear el prestamo, actualizar el estado del ejemplar y disminuir los ejemplares disponibles |
| Rationale | Control de circulacion de material |
| Criterio de aceptacion | Prestamo creado en menos de 2s, maximo 5 activos por usuario |
| Metodo de verificacion | Test + stored procedure |
| Trazabilidad | HU-06, CU-03 |

### REQ-F-007 -- Devolucion de prestamo
| Atributo | Valor |
|----------|-------|
| Tipo | Funcional |
| Prioridad | Must |
| Enunciado | Al recibir una devolucion, el sistema debera actualizar el estado del prestamo, liberar el ejemplar y generar multa si esta vencido |
| Rationale | Control de inventario y penalizaciones |
| Criterio de aceptacion | Devolucion procesada en menos de 2s |
| Metodo de verificacion | Test + stored procedure |
| Trazabilidad | HU-07, CU-04 |

### REQ-F-008 -- Reporte de prestamos diarios
| Atributo | Valor |
|----------|-------|
| Tipo | Funcional |
| Prioridad | Should |
| Enunciado | El sistema debera generar un reporte de prestamos del dia actual con datos de usuario y libro |
| Rationale | Seguimiento de actividad diaria |
| Criterio de aceptacion | Reporte generado en menos de 3s |
| Metodo de verificacion | Test + stored procedure |
| Trazabilidad | HU-08 |

### REQ-F-009 -- Reporte de prestamos diarios agregado
| Atributo | Valor |
|----------|-------|
| Tipo | Funcional |
| Prioridad (MoSCoW) | Should |
| Enunciado | El sistema debera devolver el numero de prestamos agrupado por dia, calculado mediante procedimiento almacenado y no en la capa de aplicacion |
| Rationale | La agregacion en base de datos evita transferir el detalle completo de prestamos al servidor de aplicacion (ADR-0005) |
| Origen | Stakeholder: Direccion de biblioteca |
| Criterio de aceptacion | Un BIBLIOTECARIO obtiene 200 con la serie diaria; un ESTUDIANTE obtiene 403 |
| Metodo de verificacion | Test (ReporteControllerTest.prestamosDiariosComoBibliotecario) |
| Tipo de acceso | SP (procedimiento almacenado) |
| Trazabilidad | HU-09, CU-05 |

### REQ-F-010 -- Tablero de control por rol
| Atributo | Valor |
|----------|-------|
| Tipo | Funcional |
| Prioridad (MoSCoW) | Must |
| Enunciado | El sistema debera exponer un conjunto de indicadores agregados cuyo contenido dependa del rol del solicitante, de modo que cada rol reciba unicamente los indicadores de su ambito |
| Rationale | Un unico endpoint por rol evita duplicar logica de agregacion en el frontend |
| Origen | Stakeholder: Administracion bibliotecaria |
| Criterio de aceptacion | La respuesta para ESTUDIANTE no contiene indicadores globales de la biblioteca |
| Metodo de verificacion | Inspeccion; **pendiente de prueba automatizada** |
| Tipo de acceso | SP (procedimiento almacenado) |
| Trazabilidad | HU-10 |
| Estado | Implementado, no verificado |

### REQ-F-011 -- Reserva de ejemplares
| Atributo | Valor |
|----------|-------|
| Tipo | Funcional |
| Prioridad (MoSCoW) | Should |
| Enunciado | El sistema debera permitir a un usuario autenticado reservar un ejemplar no disponible y cancelar su propia reserva, sin poder cancelar la de otro usuario |
| Rationale | La reserva desacopla la demanda de la disponibilidad inmediata |
| Origen | Stakeholder: Personal de circulacion |
| Criterio de aceptacion | La cancelacion de una reserva ajena devuelve 403 |
| Metodo de verificacion | Test (ReservaControllerTest); **prueba anadida en esta entrega** |
| Tipo de acceso | CRUD-ORM |
| Trazabilidad | Sin historia de usuario asociada -- **deficiencia declarada** |

---

## 4. Requisitos no funcionales

### REQ-NF-001 -- Rendimiento -- Listado de libros
| Atributo | Valor |
|----------|-------|
| Tipo | No funcional |
| Prioridad | Must |
| Enunciado | El endpoint GET /api/libros debera responder con p95 <= 200 ms con cache Redis caliente |
| Rationale | Experiencia de usuario fluida en navegacion de catalogo |
| Criterio de aceptacion | p95 < 200ms en 3 corridas con cache caliente |
| Metodo de verificacion | Test de rendimiento (k6) |
| Trazabilidad | Evidencia: docs/mediciones/perf/ |

### REQ-NF-002 -- Seguridad -- Autenticacion
| Atributo | Valor |
|----------|-------|
| Tipo | No funcional |
| Prioridad | Must |
| Enunciado | El sistema debera implementar autenticacion JWT sin sesiones, con cookie HttpOnly + Secure + SameSite=Strict |
| Rationale | Mitigacion de ataques XSS y CSRF |
| Criterio de aceptacion | Ningun endpoint protegido accesible sin JWT valido |
| Metodo de verificacion | Test de seguridad |
| Trazabilidad | OWASP Top 10 |

### REQ-NF-003 -- Seguridad -- Proteccion contra inyeccion SQL
| Atributo | Valor |
|----------|-------|
| Tipo | No funcional |
| Prioridad | Must |
| Enunciado | Ninguna consulta SQL debera concatenar entrada de usuario; toda parametrizacion sera mediante parametros nombrados |
| Rationale | Prevencion de inyeccion SQL |
| Criterio de aceptacion | Escaneo SAST sin hallazgos de inyeccion SQL |
| Metodo de verificacion | Analisis estatico (inspection) |
| Trazabilidad | OWASP Cheat Sheet |

### REQ-NF-004 -- Disponibilidad -- Contenedores
| Atributo | Valor |
|----------|-------|
| Tipo | No funcional |
| Prioridad | Must |
| Enunciado | El sistema debera poder reconstruirse en menos de 2 minutos desde una clonacion limpia con un solo comando (make up) |
| Rationale | Reproducibilidad por terceros |
| Criterio de aceptacion | docker-compose up exitoso en ambiente limpio |
| Metodo de verificacion | Demostracion (demonstration) |
| Trazabilidad | Docker Compose |

### REQ-NF-005 -- Mantenibilidad -- Cobertura de pruebas
| Atributo | Valor |
|----------|-------|
| Tipo | No funcional |
| Prioridad | Should |
| Enunciado | El backend debera tener cobertura de linea >= 30% medida por JaCoCo |
| Rationale | Garantia minima de calidad |
| Criterio de aceptacion | Reporte JaCoCo >= 30% |
| Metodo de verificacion | Test (JaCoCo) |
| Trazabilidad | pom.xml |

### REQ-NF-006 -- Seguridad -- Control de acceso por rol en reportes
| Atributo | Valor |
|----------|-------|
| Tipo | No funcional (seguridad) |
| Prioridad (MoSCoW) | Must |
| Enunciado | El sistema debera denegar con 403 el acceso a los endpoints de reportes a todo usuario cuyo rol no sea BIBLIOTECARIO o ADMIN, con independencia de que la interfaz muestre o no el enlace |
| Rationale | La decision de autorizacion pertenece al servidor; ocultar un boton no es un control de acceso |
| Criterio de aceptacion | Un ESTUDIANTE autenticado que invoca el endpoint directamente recibe 403 |
| Metodo de verificacion | Test (ReporteControllerTest.prestamosDiariosComoEstudianteRetorna403) y auditoria OWASP |
| Trazabilidad | HU-04, CU-01 |

### REQ-NF-007 -- Seguridad -- Revocacion de tokens
| Atributo | Valor |
|----------|-------|
| Tipo | No funcional (seguridad) |
| Prioridad (MoSCoW) | Must |
| Enunciado | Al cerrar sesion, el sistema debera registrar el identificador jti del token en una lista de revocacion, de modo que todo uso posterior de ese token sea rechazado antes de su expiracion natural |
| Rationale | Un JWT stateless es valido hasta expirar; sin revocacion, el cierre de sesion es cosmetico (ADR-0009) |
| Criterio de aceptacion | Un token cuyo jti esta en la lista negra es rechazado |
| Metodo de verificacion | Test (JwtBlacklistServiceTest.isBlacklistedDevuelveTrueCuandoElJTIEstaRegistrado) |
| Trazabilidad | HU-03 |

### REQ-NF-008 -- Fiabilidad -- Codigo de estado correcto ante recurso inexistente
| Atributo | Valor |
|----------|-------|
| Tipo | No funcional (fiabilidad) |
| Prioridad (MoSCoW) | Must |
| Enunciado | Ante una ruta o un recurso inexistente, el sistema debera responder 404 con un cuerpo conforme a RFC 7807, y no 500 |
| Rationale | Defecto D2 detectado en esta entrega: toda ruta inexistente devolvia 500 |
| Criterio de aceptacion | GET a una ruta inexistente devuelve 404 y content-type application/problem+json |
| Metodo de verificacion | Test (GlobalExceptionHandlerTest.handleNotFoundRetornaProblemDetail) |
| Trazabilidad | Origen: hallazgo propio de auditoria |

### REQ-NF-009 -- Seguridad -- No filtracion de mensajes internos
| Atributo | Valor |
|----------|-------|
| Tipo | No funcional (seguridad) |
| Prioridad (MoSCoW) | Must |
| Enunciado | El sistema no debera incluir en ninguna respuesta de error el mensaje de la excepcion interna, la traza de pila ni el nombre de la clase que la origino |
| Rationale | Los mensajes del framework revelan version, rutas de clase y estructura interna |
| Criterio de aceptacion | La respuesta de error contiene un mensaje generico y un identificador de correlacion |
| Metodo de verificacion | Test (GlobalExceptionHandlerTest.handleGeneralNoFiltraElMensajeInternoAlCliente) |
| Trazabilidad | OWASP API Security Top 10 |

### REQ-NF-010 -- Fiabilidad -- Serializacion estable de la cache
| Atributo | Valor |
|----------|-------|
| Tipo | No funcional (fiabilidad) |
| Prioridad (MoSCoW) | Must |
| Enunciado | Todo objeto que el sistema almacene en cache debera poder recuperarse de ella y devolverse al cliente sin error, es decir, debera soportar un ciclo completo de serializacion y deserializacion |
| Rationale | Defecto D1: se cacheaba PageImpl, que Redis no puede deserializar, y todo acierto de cache devolvia 500 (ADR-0006) |
| Criterio de aceptacion | El tipo cacheado completa el ciclo de ida y vuelta por el serializador de Redis |
| Metodo de verificacion | Test (CacheSerializationTest.pageResponseHaceRoundTripPorElSerializadorDeRedis) |
| Trazabilidad | HU-05, ADR-0006 |

---

## 4.b Requisitos de rendimiento

### REQ-R-001 -- Latencia de autenticacion
| Atributo | Valor |
|----------|-------|
| Tipo | Rendimiento |
| Prioridad (MoSCoW) | Must |
| Enunciado | El percentil 95 de la latencia del endpoint de autenticacion no debera superar 500 ms bajo la carga nominal definida en el protocolo de medicion |
| Rationale | La autenticacion es la primera interaccion y condiciona la percepcion del sistema |
| Criterio de aceptacion | p95 < 500 ms con 50 usuarios virtuales sostenidos 30 s |
| Metodo de verificacion | Medicion (k6) |
| Estado | **Pendiente**: el arnes actual excluye el login del tramo medido para no contaminar la metrica del catalogo |
| Trazabilidad | docs/mediciones/perf/REPORT.md |

### REQ-R-002 -- Latencia del listado de catalogo
| Atributo | Valor |
|----------|-------|
| Tipo | Rendimiento |
| Prioridad (MoSCoW) | Must |
| Enunciado | El percentil 95 de la latencia del listado de catalogo no debera superar 200 ms bajo la carga nominal definida en el protocolo de medicion |
| Rationale | Es la operacion mas frecuente del sistema |
| Criterio de aceptacion | p95 < 200 ms |
| Metodo de verificacion | Medicion (k6, metrica catalogo_duracion) |
| Trazabilidad | docs/mediciones/perf/REPORT.md |

### REQ-R-003 -- Latencia del registro de prestamo
| Atributo | Valor |
|----------|-------|
| Tipo | Rendimiento |
| Prioridad (MoSCoW) | Should |
| Enunciado | El percentil 95 de la latencia del registro de prestamo no debera superar 800 ms, considerando que la operacion invoca un procedimiento almacenado transaccional |
| Rationale | El prestamo es una operacion de varios pasos con bloqueo de ejemplar |
| Criterio de aceptacion | p95 < 800 ms |
| Metodo de verificacion | Medicion (k6) |
| Estado | **Pendiente**: no medido; el arnes de carga solo ejercita lectura |
| Trazabilidad | docs/mediciones/perf/REPORT.md |

### REQ-R-004 -- Eficacia de la cache de catalogo
| Atributo | Valor |
|----------|-------|
| Tipo | Rendimiento |
| Prioridad (MoSCoW) | Should |
| Enunciado | El sistema debera servir desde cache las consultas repetidas de catalogo, y el tipo cacheado debera ser deserializable |
| Rationale | Reducir la carga sobre PostgreSQL en la operacion mas frecuente |
| Criterio de aceptacion | Hit ratio de Redis medible y superior a cero en regimen |
| Metodo de verificacion | Test (CacheSerializationTest.pageImplNoSePuedeDeserializarYPorEsoNoSeCachea) y medicion de hit ratio |
| Observacion | La medicion no permite afirmar que la cache mejore el rendimiento de este sistema: vease la seccion de rendimiento del informe |
| Trazabilidad | ADR-0006 |

---

## 4.c Requisitos de usabilidad

### REQ-U-001 -- Satisfaccion de uso
| Atributo | Valor |
|----------|-------|
| Tipo | Usabilidad |
| Prioridad (MoSCoW) | Should |
| Enunciado | El sistema debera alcanzar una puntuacion SUS igual o superior a 68, que es la media de referencia de la escala |
| Rationale | Un sistema bibliotecario lo usan personas sin formacion tecnica |
| Criterio de aceptacion | SUS >= 68 con al menos 15 participantes |
| Metodo de verificacion | Encuesta (instrumento SUS validado, scripts/sus-score.py) |
| Estado | **No verificado**: el instrumento esta construido y validado, con cinco casos canonicos de autotest en CI, pero se aplico a **cero participantes**. No hay resultado que informar |
| Trazabilidad | docs/mediciones/usabilidad/SUS.md |

### REQ-U-002 -- Calidad tecnica de la interfaz web
| Atributo | Valor |
|----------|-------|
| Tipo | Usabilidad |
| Prioridad (MoSCoW) | Should |
| Enunciado | Las paginas principales deberan alcanzar al menos 80 en rendimiento, 90 en accesibilidad, 90 en buenas practicas y 85 en SEO segun Lighthouse |
| Rationale | La accesibilidad es exigible en un servicio universitario |
| Criterio de aceptacion | Los cuatro umbrales se superan |
| Metodo de verificacion | Medicion (Lighthouse 12.8.2) |
| Observacion | Superado en la unica corrida movil disponible (82 / 100 / 100 / 91). **No hay corrida de escritorio** y la URL auditada es localhost |
| Trazabilidad | docs/mediciones/frontend/LIGHTHOUSE.md |

---

## 5. Requisitos de interfaz externa

### 5.1 Interfaz de usuario
Frontend Laravel 13 con Tailwind CSS, accesible via navegador en puerto 8000.

### 5.2 Interfaz API REST
Documentacion OpenAPI 3.0 en `/api-docs` e interfaz Swagger UI en `/swagger-ui.html`.

### 5.3 Interfaz de base de datos
Conexion JDBC a PostgreSQL 16, esquema gestionado por Flyway.

---

## 6. Requisitos de seguridad

- JWT con claims estandar RFC 7519
- Cookie HttpOnly + Secure + SameSite=Strict
- Blacklist de JTI revocados en Redis
- CORS restringido a origenes configurados
- BCrypt para almacenamiento de contrasenas
- Parametros nombrados en todas las consultas
- Procedimientos almacenados sin SQL dinamico

---

## 7. Calidad de software

### 7.1 Caracteristicas del conjunto de requisitos (INCOSE C10-C15)
| Atributo | Cumplimiento |
|----------|-------------|
| Complete (C10) | Todos los escenarios principales cubiertos |
| Consistent (C11) | Sin contradicciones entre requisitos |
| Feasible (C12) | Implementable con la pila tecnologica actual |
| Comprehensible (C13) | Redactado en lenguaje claro |
| Able to be validated (C14) | Cada requisito tiene metodo de verificacion |
| Correct (C15) | Aprobado por stakeholders |

---
*Fin del SRS*
