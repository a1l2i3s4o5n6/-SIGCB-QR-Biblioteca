package com.sigcbqr.exception;

import org.junit.jupiter.api.Test;
import org.springframework.http.HttpStatus;
import org.springframework.http.HttpMethod;
import org.springframework.http.ProblemDetail;
import org.springframework.web.HttpRequestMethodNotSupportedException;
import org.springframework.web.servlet.resource.NoResourceFoundException;

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
        assertNotNull(pd.getType());
        assertNotNull(pd.getTitle());
    }

    @Test
    void handleGeneralNoFiltraElMensajeInternoAlCliente() {
        // El mensaje de una excepcion no controlada puede contener rutas, nombres
        // de clase o fragmentos de consulta. Se registra en el servidor, no se
        // devuelve. Antes, el detalle era "Error interno del servidor: " + mensaje
        // y llego a exponer trazas de Jackson y de Redis a clientes no autenticados.
        Exception ex = new RuntimeException("jdbc:postgresql://db:5432/sigcbqr password=secreto");
        ProblemDetail pd = handler.handleGeneral(ex);

        assertEquals(HttpStatus.INTERNAL_SERVER_ERROR.value(), pd.getStatus());
        assertFalse(pd.getDetail().contains("jdbc"), "el detalle no debe filtrar la cadena de conexion");
        assertFalse(pd.getDetail().contains("secreto"), "el detalle no debe filtrar credenciales");
        assertEquals("Error interno del servidor", pd.getDetail());
    }

    @Test
    void rutaInexistenteEs404YNo500() {
        // Sin manejador propio, NoResourceFoundException caia en handleGeneral y
        // cualquier URL mal escrita respondia 500.
        NoResourceFoundException ex = new NoResourceFoundException(HttpMethod.GET, "/api/no-existe");
        ProblemDetail pd = handler.handleRutaNoEncontrada(ex);

        assertEquals(HttpStatus.NOT_FOUND.value(), pd.getStatus());
        assertFalse(pd.getDetail().contains("static resource"),
                "el detalle no debe filtrar el mensaje del framework");
    }

    @Test
    void metodoNoSoportadoEs405() {
        HttpRequestMethodNotSupportedException ex =
                new HttpRequestMethodNotSupportedException("DELETE");
        ProblemDetail pd = handler.handleMetodoNoPermitido(ex);

        assertEquals(HttpStatus.METHOD_NOT_ALLOWED.value(), pd.getStatus());
        assertTrue(pd.getDetail().contains("DELETE"));
    }
}
