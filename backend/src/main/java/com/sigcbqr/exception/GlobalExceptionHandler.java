package com.sigcbqr.exception;

import jakarta.validation.ConstraintViolation;
import jakarta.validation.ConstraintViolationException;
import org.springframework.http.HttpStatus;
import org.springframework.http.ProblemDetail;
import org.springframework.security.access.AccessDeniedException;
import org.springframework.security.authentication.BadCredentialsException;
import org.springframework.security.authentication.DisabledException;
import org.springframework.validation.FieldError;
import org.springframework.web.bind.MethodArgumentNotValidException;
import org.springframework.web.method.annotation.MethodArgumentTypeMismatchException;
import org.springframework.web.bind.annotation.ExceptionHandler;
import org.springframework.web.bind.annotation.RestControllerAdvice;

import java.net.URI;
import java.util.HashMap;
import java.util.Map;

@RestControllerAdvice
public class GlobalExceptionHandler {

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

    @ExceptionHandler(MethodArgumentTypeMismatchException.class)
    public ProblemDetail handleTypeMismatch(MethodArgumentTypeMismatchException ex) {
        ProblemDetail pd = ProblemDetail.forStatusAndDetail(HttpStatus.BAD_REQUEST,
                "Parámetro inválido: " + ex.getName());
        pd.setType(URI.create(BASE_ERROR_URL + "/bad-request"));
        pd.setTitle("Solicitud inválida");
        pd.setProperty("timestamp", System.currentTimeMillis());
        return pd;
    }

    @ExceptionHandler(Exception.class)
    public ProblemDetail handleGeneral(Exception ex) {
        ProblemDetail pd = ProblemDetail.forStatusAndDetail(HttpStatus.INTERNAL_SERVER_ERROR,
                "Error interno del servidor: " + ex.getMessage());
        pd.setType(URI.create(BASE_ERROR_URL + "/internal-error"));
        pd.setTitle("Error interno");
        pd.setProperty("timestamp", System.currentTimeMillis());
        return pd;
    }
}
