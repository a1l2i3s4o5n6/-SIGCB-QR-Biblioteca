package com.sigcbqr.controller;

import com.sigcbqr.config.SecurityConfig;
import com.sigcbqr.model.dto.request.LoginRequest;
import com.sigcbqr.model.dto.request.RegisterRequest;
import com.sigcbqr.model.dto.response.LoginResponse;
import com.sigcbqr.security.JwtAuthenticationEntryPoint;
import com.sigcbqr.security.JwtTokenProvider;
import com.sigcbqr.service.AuthService;
import com.fasterxml.jackson.databind.ObjectMapper;
import jakarta.servlet.http.Cookie;
import org.junit.jupiter.api.Test;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.test.autoconfigure.web.servlet.WebMvcTest;
import org.springframework.boot.test.mock.mockito.MockBean;
import org.springframework.context.annotation.Import;
import org.springframework.http.MediaType;
import org.springframework.test.web.servlet.MockMvc;

import static org.mockito.ArgumentMatchers.any;
import static org.mockito.Mockito.when;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.post;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.*;

@WebMvcTest(AuthController.class)
@Import(SecurityConfig.class)
class AuthControllerTest {

    @Autowired
    private MockMvc mockMvc;

    @Autowired
    private ObjectMapper objectMapper;

    @MockBean
    private AuthService authService;

    @MockBean
    private JwtTokenProvider tokenProvider;

    @MockBean
    private JwtAuthenticationEntryPoint jwtAuthenticationEntryPoint;

    @Test
    void loginExitoso() throws Exception {
        LoginRequest request = new LoginRequest();
        request.setEmail("admin@dev.com");
        request.setPassword("admin123");

        LoginResponse loginResponse = LoginResponse.builder()
                .id(1L)
                .nombre("Admin")
                .email("admin@dev.com")
                .rol("ADMIN")
                .mensaje("Inicio de sesión exitoso")
                .build();

        when(authService.login(any(LoginRequest.class))).thenReturn(loginResponse);
        when(tokenProvider.generateToken(1L, "admin@dev.com", "ADMIN")).thenReturn("test-jwt-token");
        when(tokenProvider.createAccessTokenCookie("test-jwt-token"))
                .thenReturn(new Cookie("access_token", "test-jwt-token"));

        mockMvc.perform(post("/api/auth/login")
                        .contentType(MediaType.APPLICATION_JSON)
                        .content(objectMapper.writeValueAsString(request)))
                .andExpect(status().isOk())
                .andExpect(jsonPath("$.message").value("Inicio de sesión exitoso"));
    }

    @Test
    void registroExitoso() throws Exception {
        RegisterRequest request = new RegisterRequest();
        request.setNombre("Nuevo Usuario");
        request.setEmail("nuevo@test.com");
        request.setPassword("password123");

        LoginResponse loginResponse = LoginResponse.builder()
                .id(2L)
                .nombre("Nuevo Usuario")
                .email("nuevo@test.com")
                .rol("ESTUDIANTE")
                .mensaje("Registro exitoso")
                .build();

        when(authService.register(any(RegisterRequest.class))).thenReturn(loginResponse);
        when(tokenProvider.generateToken(2L, "nuevo@test.com", "ESTUDIANTE")).thenReturn("test-jwt-token");
        when(tokenProvider.createAccessTokenCookie("test-jwt-token"))
                .thenReturn(new Cookie("access_token", "test-jwt-token"));

        mockMvc.perform(post("/api/auth/register")
                        .contentType(MediaType.APPLICATION_JSON)
                        .content(objectMapper.writeValueAsString(request)))
                .andExpect(status().isOk())
                .andExpect(jsonPath("$.message").value("Registro exitoso"));
    }
}
