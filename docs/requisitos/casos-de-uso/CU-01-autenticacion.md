# CU-01: Autenticación de usuarios

**Nivel:** 1 (Resumen)
**Actor principal:** Usuario no autenticado
**Stakeholders:** Administración bibliotecaria (desea control de acceso)

## Escenario principal de éxito

1. El usuario envía sus credenciales (email, contraseña) al sistema
2. El sistema valida las credenciales contra la base de datos
3. El sistema genera un JWT firmado con claims estándar (iss, sub, aud, exp, nbf, iat, jti)
4. El sistema establece una cookie HttpOnly + Secure + SameSite=Strict con el JWT
5. El usuario queda autenticado y puede acceder a recursos protegidos
6. El sistema permite al usuario cerrar sesión, agregando el JTI a la blacklist

## Extensiones

### 1a. Credenciales inválidas
1a1. El sistema responde con HTTP 401 y ProblemDetail

### 2a. Token expirado
2a1. El sistema rechaza la petición con HTTP 401

### 3a. Token blacklistado
3a1. El sistema rechaza la petición con HTTP 401

## Requisitos asociados
- REQ-F-001 (Login)
- REQ-F-002 (Registro)
- REQ-F-003 (Logout)
- REQ-NF-002 (Seguridad)
