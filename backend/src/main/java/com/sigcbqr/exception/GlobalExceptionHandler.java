package com.sigcbqr.exception;

import jakarta.validation.ConstraintViolation;
import jakarta.validation.ConstraintViolationException;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.http.HttpStatus;
import org.springframework.http.ProblemDetail;
import org.springframework.security.access.AccessDeniedException;
import org.springframework.security.authentication.BadCredentialsException;
import org.springframework.security.authentication.DisabledException;
import org.springframework.validation.FieldError;
import org.springframework.web.bind.MethodArgumentNotValidException;
import org.springframework.web.HttpRequestMethodNotSupportedException;
import org.springframework.web.bind.annotation.ExceptionHandler;
import org.springframework.web.bind.annotation.RestControllerAdvice;
import org.springframework.web.servlet.NoHandlerFoundException;
import org.springframework.web.servlet.resource.NoResourceFoundException;

import java.net.URI;
import java.util.HashMap;
import java.util.Map;

@RestControllerAdvice
public class GlobalExceptionHandler {

    private static final Logger log = LoggerFactory.getLogger(GlobalExceptionHandler.class);

    public static final String BASE_ERROR_URL = "https://api.sigcbqr.com/errors";

    @ExceptionHandler(ResourceNotFoundException.class)
    public ProblemDetail handleNotFound(ResourceNotFoundException ex) {
        ProblemDetail pd = ProblemDetail.forStatusAndDetail(HttpStatus.NOT_FOUND, ex.getMessage());
        pd.setType(URI.create(BASE_ERROR_URL + "/not-found"));
        pd.setTitle("Recurso no encontrado");
        pd.setProperty("timestamp", System.currentTimeMillis());
        return pd;
    }

    @ExceptionHandler(BadRequestException.class)
    public ProblemDetail handleBadRequest(BadRequestException ex) {
        ProblemDetail pd = ProblemDetail.forStatusAndDetail(HttpStatus.BAD_REQUEST, ex.getMessage());
        pd.setType(URI.create(BASE_ERROR_URL + "/bad-request"));
        pd.setTitle("Solicitud inválida");
        pd.setProperty("timestamp", System.currentTimeMillis());
        return pd;
    }

    @ExceptionHandler(BadCredentialsException.class)
    public ProblemDetail handleBadCredentials(BadCredentialsException ex) {
        ProblemDetail pd = ProblemDetail.forStatusAndDetail(HttpStatus.UNAUTHORIZED, "Credenciales inválidas");
        pd.setType(URI.create(BASE_ERROR_URL + "/unauthorized"));
        pd.setTitle("No autorizado");
        pd.setProperty("timestamp", System.currentTimeMillis());
        return pd;
    }

    @ExceptionHandler(TooManyRequestsException.class)
    public ProblemDetail handleTooManyRequests(TooManyRequestsException ex) {
        ProblemDetail pd = ProblemDetail.forStatusAndDetail(HttpStatus.TOO_MANY_REQUESTS, ex.getMessage());
        pd.setType(URI.create(BASE_ERROR_URL + "/too-many-requests"));
        pd.setTitle("Demasiadas solicitudes");
        pd.setProperty("timestamp", System.currentTimeMillis());
        return pd;
    }

    @ExceptionHandler(DisabledException.class)
    public ProblemDetail handleDisabled(DisabledException ex) {
        ProblemDetail pd = ProblemDetail.forStatusAndDetail(HttpStatus.UNAUTHORIZED, "Usuario desactivado");
        pd.setType(URI.create(BASE_ERROR_URL + "/unauthorized"));
        pd.setTitle("No autorizado");
        pd.setProperty("timestamp", System.currentTimeMillis());
        return pd;
    }

    @ExceptionHandler(AccessDeniedException.class)
    public ProblemDetail handleAccessDenied(AccessDeniedException ex) {
        ProblemDetail pd = ProblemDetail.forStatusAndDetail(HttpStatus.FORBIDDEN,
                "No tiene permisos para acceder a este recurso");
        pd.setType(URI.create(BASE_ERROR_URL + "/forbidden"));
        pd.setTitle("Acceso denegado");
        pd.setProperty("timestamp", System.currentTimeMillis());
        return pd;
    }

    @ExceptionHandler(MethodArgumentNotValidException.class)
    public ProblemDetail handleValidation(MethodArgumentNotValidException ex) {
        Map<String, String> errors = new HashMap<>();
        for (FieldError error : ex.getBindingResult().getFieldErrors()) {
            errors.put(error.getField(), error.getDefaultMessage());
        }
        ProblemDetail pd = ProblemDetail.forStatusAndDetail(HttpStatus.BAD_REQUEST, "Error de validación");
        pd.setType(URI.create(BASE_ERROR_URL + "/validation"));
        pd.setTitle("Error de validación");
        pd.setProperty("errors", errors);
        pd.setProperty("timestamp", System.currentTimeMillis());
        return pd;
    }

    @ExceptionHandler(ConstraintViolationException.class)
    public ProblemDetail handleConstraintViolation(ConstraintViolationException ex) {
        Map<String, String> errors = new HashMap<>();
        for (ConstraintViolation<?> violation : ex.getConstraintViolations()) {
            errors.put(violation.getPropertyPath().toString(), violation.getMessage());
        }
        ProblemDetail pd = ProblemDetail.forStatusAndDetail(HttpStatus.BAD_REQUEST, "Parámetro inválido");
        pd.setType(URI.create(BASE_ERROR_URL + "/validation"));
        pd.setTitle("Error de validación");
        pd.setProperty("errors", errors);
        pd.setProperty("timestamp", System.currentTimeMillis());
        return pd;
    }

    /**
     * Una ruta que no existe es 404, no 500. Sin este manejador, Spring lanza
     * NoResourceFoundException ("No static resource ..."), que caia en
     * handleGeneral y convertia cualquier URL mal escrita en un error interno,
     * ademas de filtrar el mensaje del framework al cliente.
     */
    @ExceptionHandler({NoResourceFoundException.class, NoHandlerFoundException.class})
    public ProblemDetail handleRutaNoEncontrada(Exception ex) {
        ProblemDetail pd = ProblemDetail.forStatusAndDetail(HttpStatus.NOT_FOUND,
                "El recurso solicitado no existe");
        pd.setType(URI.create(BASE_ERROR_URL + "/not-found"));
        pd.setTitle("Recurso no encontrado");
        pd.setProperty("timestamp", System.currentTimeMillis());
        return pd;
    }

    @ExceptionHandler(HttpRequestMethodNotSupportedException.class)
    public ProblemDetail handleMetodoNoPermitido(HttpRequestMethodNotSupportedException ex) {
        ProblemDetail pd = ProblemDetail.forStatusAndDetail(HttpStatus.METHOD_NOT_ALLOWED,
                "El metodo " + ex.getMethod() + " no esta permitido en este recurso");
        pd.setType(URI.create(BASE_ERROR_URL + "/method-not-allowed"));
        pd.setTitle("Metodo no permitido");
        pd.setProperty("timestamp", System.currentTimeMillis());
        return pd;
    }

    /**
     * Ultimo recurso. El detalle que se devuelve al cliente es generico a
     * proposito: el mensaje de la excepcion puede contener rutas, nombres de
     * clase o fragmentos de consulta. Se registra completo en el servidor,
     * donde si es util, y no se expone.
     */
    @ExceptionHandler(Exception.class)
    public ProblemDetail handleGeneral(Exception ex) {
        log.error("Error no controlado", ex);
        ProblemDetail pd = ProblemDetail.forStatusAndDetail(HttpStatus.INTERNAL_SERVER_ERROR,
                "Error interno del servidor");
        pd.setType(URI.create(BASE_ERROR_URL + "/internal-error"));
        pd.setTitle("Error interno");
        pd.setProperty("timestamp", System.currentTimeMillis());
        return pd;
    }
}
