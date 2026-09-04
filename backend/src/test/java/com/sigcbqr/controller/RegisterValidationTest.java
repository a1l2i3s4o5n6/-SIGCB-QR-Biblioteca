package com.sigcbqr.controller;

import com.fasterxml.jackson.databind.ObjectMapper;
import com.sigcbqr.config.SecurityConfig;
import com.sigcbqr.model.dto.request.RegisterRequest;
import com.sigcbqr.model.dto.response.LoginResponse;
import jakarta.servlet.http.Cookie;
import com.sigcbqr.security.JwtAuthenticationEntryPoint;
import com.sigcbqr.security.JwtTokenProvider;
import com.sigcbqr.service.AuthService;
import com.sigcbqr.service.RateLimitService;
import org.junit.jupiter.api.DisplayName;
import org.junit.jupiter.api.Test;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.test.autoconfigure.web.servlet.WebMvcTest;
import org.springframework.boot.test.mock.mockito.MockBean;
import org.springframework.context.annotation.Import;
import org.springframework.http.MediaType;
import org.springframework.test.web.servlet.MockMvc;

import static org.mockito.ArgumentMatchers.any;
import static org.mockito.ArgumentMatchers.anyLong;
import static org.mockito.ArgumentMatchers.anyString;
import static org.mockito.Mockito.when;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.post;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.jsonPath;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.status;

/**
 * Prueba de regresion de los limites de longitud en el registro.
 *
 * <p>Origen del defecto. RegisterRequest validaba que los campos no estuvieran
 * vacios, pero no imponia longitud maxima. Una cadena mas larga que la columna
 * de destino superaba la validacion, llegaba al INSERT y PostgreSQL respondia
 * "value too long for type character varying(100)". El manejador global lo
 * convertia en 500.
 *
 * <p>Un dato de entrada demasiado largo es un error del CLIENTE: corresponde
 * 400 con el motivo, no 500. Un 500 aqui es ademas informativo para un atacante,
 * porque revela que la entrada llego hasta la base de datos.
 *
 * <p>Lo detecto la sonda de desbordamiento de bufer de OWASP ZAP. No lo detecto
 * la auditoria propia ni la suite: ninguna de las dos probaba entradas
 * anormalmente largas, porque ambas se escribieron pensando en el uso legitimo.
 */
@WebMvcTest(AuthController.class)
@Import({SecurityConfig.class, JwtAuthenticationEntryPoint.class})
class RegisterValidationTest {

    @Autowired
    private MockMvc mockMvc;

    @Autowired
    private ObjectMapper objectMapper;

    @MockBean
    private AuthService authService;

    @MockBean
    private RateLimitService rateLimitService;

    @MockBean
    private JwtTokenProvider tokenProvider;

    private RegisterRequest peticionValida() {
        RegisterRequest r = new RegisterRequest();
        r.setNombre("Usuario Valido");
        r.setEmail("valido@uteq.edu.ec");
        r.setPassword("Abcdef123");
        r.setTelefono("0999999999");
        return r;
    }

    @Test
    @DisplayName("Un nombre mas largo que la columna responde 400, no 500")
    void nombreDemasiadoLargoDevuelve400() throws Exception {
        RegisterRequest r = peticionValida();
        r.setNombre("A".repeat(101));      // la columna es VARCHAR(100)

        mockMvc.perform(post("/api/auth/register")
                        .contentType(MediaType.APPLICATION_JSON)
                        .content(objectMapper.writeValueAsString(r)))
                .andExpect(status().isBadRequest())
                .andExpect(jsonPath("$.errors.nombre").exists());
    }

    @Test
    @DisplayName("Un correo mas largo que la columna responde 400, no 500")
    void emailDemasiadoLargoDevuelve400() throws Exception {
        RegisterRequest r = peticionValida();
        r.setEmail("a".repeat(140) + "@uteq.edu.ec");   // la columna es VARCHAR(150)

        mockMvc.perform(post("/api/auth/register")
                        .contentType(MediaType.APPLICATION_JSON)
                        .content(objectMapper.writeValueAsString(r)))
                .andExpect(status().isBadRequest());
    }

    @Test
    @DisplayName("Un telefono mas largo que la columna responde 400, no 500")
    void telefonoDemasiadoLargoDevuelve400() throws Exception {
        RegisterRequest r = peticionValida();
        r.setTelefono("9".repeat(21));     // la columna es VARCHAR(20)

        mockMvc.perform(post("/api/auth/register")
                        .contentType(MediaType.APPLICATION_JSON)
                        .content(objectMapper.writeValueAsString(r)))
                .andExpect(status().isBadRequest())
                .andExpect(jsonPath("$.errors.telefono").exists());
    }

    @Test
    @DisplayName("La sonda de ZAP que provoco el 500 ahora devuelve 400")
    void cargaDeNueveMilCaracteresDevuelve400() throws Exception {
        // Reproduce la sonda concreta de OWASP ZAP que descubrio el defecto.
        RegisterRequest r = peticionValida();
        r.setNombre("A".repeat(9000));

        mockMvc.perform(post("/api/auth/register")
                        .contentType(MediaType.APPLICATION_JSON)
                        .content(objectMapper.writeValueAsString(r)))
                .andExpect(status().isBadRequest());
    }

    @Test
    @DisplayName("Los limites no rompen el registro legitimo")
    void peticionEnElLimiteSigueSiendoValida() throws Exception {
        // Justo en el limite, no por encima: debe pasar la validacion. Sin este
        // caso, unos limites demasiado estrictos pasarian inadvertidos.
        //
        // Aqui SI hay que doblar el servicio: los otros casos no llegan a el
        // porque la validacion los rechaza antes, pero este si, y sin doble
        // devolveria null y el controlador fallaria con 500.
        LoginResponse respuesta = LoginResponse.builder()
                .id(2L).nombre("Usuario Valido").email("valido@uteq.edu.ec")
                .rol("ESTUDIANTE").mensaje("Registro exitoso").build();
        when(authService.register(any(RegisterRequest.class))).thenReturn(respuesta);
        when(tokenProvider.generateToken(anyLong(), anyString(), anyString()))
                .thenReturn("token-de-prueba");
        when(tokenProvider.createAccessTokenCookie(anyString()))
                .thenReturn(new Cookie("access_token", "token-de-prueba"));

        RegisterRequest r = peticionValida();
        r.setNombre("A".repeat(100));

        mockMvc.perform(post("/api/auth/register")
                        .contentType(MediaType.APPLICATION_JSON)
                        .content(objectMapper.writeValueAsString(r)))
                .andExpect(status().isOk());
    }
}
