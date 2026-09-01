# Consideraciones éticas — SIGCB-QR

**Versión:** 1.0.0
**Última revisión:** 2026-09-01
**Ámbito:** Sistema Integral de Gestión de Biblioteca con Códigos QR (SIGCB-QR),
proyecto académico de la Universidad Técnica Estatal de Quevedo (UTEQ).

Este documento declara cómo el sistema trata datos personales, qué riesgos éticos
se identificaron y qué controles técnicos concretos los mitigan. Cada control
apunta al artefacto del repositorio donde puede verificarse.

---

## 1. Naturaleza de los datos tratados

El sistema es un prototipo académico. **No se han cargado datos de personas
reales**: el conjunto de datos de la base es sintético y se genera desde
`backend/src/main/resources/db/migration/V3__datos_semilla.sql`. Los nombres y
correos de la semilla (`admin@biblioteca.com`, `carlos.garcia@estudiante.com`,
etc.) son ficticios y se usan únicamente para pruebas y demostración.

Si el sistema llegara a desplegarse con datos reales de estudiantes, las
categorías de datos serían:

| Categoría | Campos | Base del tratamiento | Sensibilidad |
|---|---|---|---|
| Identificación | `usuarios.nombre`, `usuarios.email` | Prestación del servicio bibliotecario | Media |
| Contacto | `usuarios.telefono` | Notificación de vencimientos | Media |
| Credenciales | `usuarios.password` (hash bcrypt) | Autenticación | **Alta** |
| Actividad | `prestamos`, `reservas`, `multas` | Control del acervo | Media |
| Trazabilidad | `auditoria` (acción, IP, equipo, fecha) | Seguridad y no repudio | **Alta** |
| Sanciones | `sanciones` (tipo, motivo) | Reglamento de biblioteca | **Alta** |

**El historial de préstamos es un dato sensible.** Lo que una persona lee permite
inferir su ideología, su estado de salud, su orientación o sus creencias. El
sistema trata este historial con el mismo cuidado que las credenciales.

---

## 2. Principios adoptados

1. **Minimización.** Solo se almacenan los campos necesarios para el préstamo. No
   se recogen cédula, dirección, fecha de nacimiento, género ni datos biométricos.
2. **Limitación de la finalidad.** Los datos de préstamo sirven para gestionar el
   acervo y las multas. No se emplean para perfilado, publicidad ni evaluación
   académica del estudiante.
3. **Confidencialidad por defecto.** Un usuario con rol `ESTUDIANTE` solo puede
   ver sus propios préstamos y reservas; el control se aplica en el servidor, no
   en la interfaz.
4. **Integridad y no repudio.** Toda acción sobre datos ajenos queda registrada en
   `auditoria` con autor, acción, entidad, IP y marca temporal.
5. **Transparencia.** Este documento, el diccionario de datos
   (`docs/basedatos/DICCIONARIO-DATOS.md`) y los ADR (`docs/adr/`) declaran de
   forma pública qué se guarda y por qué.
6. **Reproducibilidad honesta.** Ninguna cifra publicada como evidencia empírica
   puede ser inventada. Ver §6.

---

## 3. Controles técnicos implementados

| Riesgo ético | Control | Dónde verificarlo |
|---|---|---|
| Robo de credenciales | Contraseñas con bcrypt (coste 10) y salt único por usuario; nunca en texto plano ni reversibles | `SecurityConfig.passwordEncoder`, `V7__hashes_unicos_seed.sql` |
| Reutilización de sesión tras cerrarla | Lista negra de JTI: el token deja de valer en el instante del logout | `JwtBlacklistService`, `JwtBlacklistServiceTest` |
| Robo de token vía JavaScript (XSS) | JWT en cookie `HttpOnly` + `SameSite=Strict`; `Secure` en producción | `JwtTokenProvider.createAccessTokenCookie`, `application-prod.yml` |
| Lectura del historial de otra persona | Autorización por rol y por propiedad del recurso, en el servidor | `@PreAuthorize` en controladores; auditoría OWASP API1/API5 |
| Fuerza bruta sobre el login | Límite de 5 intentos por IP y minuto, respuesta 429 | `RateLimitService`, `AuthController` |
| Exfiltración desde un sitio de terceros | CORS restringido a orígenes declarados; origen no permitido → 403 | `CorsConfig`, auditoría OWASP API8 |
| Inyección SQL sobre el historial | Consultas parametrizadas y procedimientos sin SQL dinámico concatenado | `V8__fix_inyeccion_sql_procedures.sql`, `SecurityTest` |
| Pérdida de trazabilidad | Registro de auditoría en acciones de escritura y de autenticación | `AuditoriaService` |

### Lo que el sistema *no* protege todavía

Declararlo es parte de la honestidad del informe:

- **La contraseña de la base de datos viaja en `.env`** y el repositorio incluye un
  `.env` de desarrollo. En un despliegue real debe sustituirse por un gestor de
  secretos; el `.env` de desarrollo no debe reutilizarse en producción.
- **No hay cifrado en reposo** de la columna `auditoria.ip` ni del historial de
  préstamos. Es aceptable en un prototipo local; no lo sería con datos reales.
- **No existe un flujo de borrado ni de exportación de datos personales a
  petición del titular.** Es una carencia conocida, no un descuido; queda
  registrada en §5 como trabajo futuro.
- **No hay política de retención**: los registros de auditoría y el historial de
  préstamos crecen indefinidamente.

---

## 4. Roles y separación de privilegios

| Rol | Puede | No puede |
|---|---|---|
| `ESTUDIANTE` | Consultar el catálogo, ver sus préstamos, reservar, ver sus multas | Ver usuarios, auditoría, reportes agregados ni datos de terceros |
| `BIBLIOTECARIO` | Todo lo anterior, más gestionar catálogo, préstamos, devoluciones y multas | Gestionar usuarios, ver auditoría, cambiar la configuración |
| `ADMIN` | Todo | — |

La separación se verifica empíricamente en la auditoría OWASP
(`docs/mediciones/seguridad/`), bloques API1 y API5.

---

## 5. Trabajo futuro en materia de privacidad

1. Derecho de acceso y portabilidad: un endpoint que exporte los datos del titular.
2. Derecho de supresión: anonimizar el historial en lugar de borrarlo, para no
   romper la contabilidad del acervo.
3. Política de retención: purgar auditoría e historial pasado un plazo declarado.
4. Consentimiento explícito y aviso de privacidad en el primer inicio de sesión.
5. Cifrado en reposo de las columnas de mayor sensibilidad.

---

## 6. Ética de la evidencia académica

Este proyecto se entrega con calificación. Se aplican tres reglas al reportar
resultados:

1. **Ninguna métrica se publica sin la orden que la produjo.** Cada cifra de
   `docs/mediciones/` va acompañada de la línea de comandos, la fecha y el
   entorno donde se ejecutó, y de la salida cruda cuando existe.
2. **Lo no medido se marca como no medido.** Una casilla vacía o «pendiente» es
   una respuesta legítima; un número inventado no lo es. La matriz de
   trazabilidad (`docs/trazabilidad/matriz.csv`) solo declara «verificado» un
   requisito cuya prueba existe realmente en el repositorio y pasa.
3. **Los defectos encontrados se publican.** El apartado de amenazas a la validez
   del informe técnico y `docs/mediciones/seguridad/` incluyen los hallazgos
   negativos, no solo los favorables.

Estas reglas responden a observaciones formales recibidas en entregas anteriores,
en las que se detectaron cifras de ejemplo presentadas como resultados y pruebas
declaradas como verificadas sin existir en el repositorio. La corrección de ambas
situaciones está registrada en `CHANGELOG.md` y en
`docs/observaciones/OBSERVACIONES.md`.

---

## 7. Uso de asistentes de IA

Se usaron asistentes de programación basados en modelos de lenguaje como apoyo a
la redacción de documentación, la generación de pruebas y la revisión de código.
Todo artefacto generado fue revisado y ejecutado por el equipo antes de
incorporarse: las pruebas se ejecutan en CI y las mediciones se obtienen del
sistema en marcha. La responsabilidad sobre el contenido entregado es del equipo.

---

## 8. Contacto

Cuestiones sobre el tratamiento de datos en este prototipo: a través del
repositorio del proyecto (issues) o del docente responsable de la asignatura.
