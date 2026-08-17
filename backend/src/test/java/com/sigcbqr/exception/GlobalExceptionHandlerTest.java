package com.sigcbqr.exception;

import org.junit.jupiter.api.Test;
import org.springframework.http.HttpStatus;
import org.springframework.http.ProblemDetail;

import static org.junit.jupiter.api.Assertions.*;

class GlobalExceptionHandlerTest {

    private final GlobalExceptionHandler handler = new GlobalExceptionHandler();

    @Test
    void handleNotFoundRetornaProblemDetail() {
        ResourceNotFoundException ex = new ResourceNotFoundException("Libro", 99L);
        ProblemDetail pd = handler.handleNotFound(ex);

        assertEquals(HttpStatus.NOT_FOUND.value(), pd.getStatus());
        assertTrue(pd.getDetail().contains("Libro"));
        assertNotNull(pd.getType());
        assertNotNull(pd.getTitle());
    }

    @Test
    void handleBadRequestRetornaProblemDetail() {
        BadRequestException ex = new BadRequestException("Solicitud inválida");
        ProblemDetail pd = handler.handleBadRequest(ex);

        assertEquals(HttpStatus.BAD_REQUEST.value(), pd.getStatus());
        assertEquals("Solicitud inválida", pd.getDetail());
    }

    @Test
    void handleGeneralErrorRetornaProblemDetail() {
        Exception ex = new RuntimeException("Error inesperado");
        ProblemDetail pd = handler.handleGeneral(ex);

        assertEquals(HttpStatus.INTERNAL_SERVER_ERROR.value(), pd.getStatus());
        assertTrue(pd.getDetail().contains("Error inesperado"));
    }
}
