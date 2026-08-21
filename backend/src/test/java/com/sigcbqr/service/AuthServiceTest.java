package com.sigcbqr.service;

import com.sigcbqr.exception.BadRequestException;
import com.sigcbqr.model.dto.request.LoginRequest;
import com.sigcbqr.model.dto.request.RegisterRequest;
import com.sigcbqr.model.dto.response.LoginResponse;
import com.sigcbqr.model.entity.Rol;
import com.sigcbqr.model.entity.Usuario;
import com.sigcbqr.repository.RolRepository;
import com.sigcbqr.repository.UsuarioRepository;
import com.sigcbqr.security.JwtTokenProvider;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.extension.ExtendWith;
import org.mockito.Mock;
import org.mockito.junit.jupiter.MockitoExtension;
import org.springframework.security.authentication.AuthenticationManager;
import org.springframework.security.crypto.password.PasswordEncoder;

import java.util.Optional;

import static org.junit.jupiter.api.Assertions.*;
import static org.mockito.ArgumentMatchers.any;
import static org.mockito.Mockito.*;

@ExtendWith(MockitoExtension.class)
class AuthServiceTest {

    @Mock
    private AuthenticationManager authenticationManager;
    @Mock
    private JwtTokenProvider tokenProvider;
    @Mock
    private UsuarioRepository usuarioRepository;
    @Mock
    private RolRepository rolRepository;
    @Mock
    private PasswordEncoder passwordEncoder;
    @Mock
    private AuditoriaService auditoriaService;

    private AuthService authService;

    @BeforeEach
    void setUp() {
        authService = new AuthService(authenticationManager, tokenProvider, usuarioRepository, rolRepository, passwordEncoder, auditoriaService);
    }

    @Test
    void registerCreaUsuarioConRolEstudiantePorDefecto() {
        RegisterRequest request = new RegisterRequest();
        request.setNombre("Test User");
        request.setEmail("test@test.com");
        request.setPassword("password123");

        Rol estudiante = new Rol();
        estudiante.setId(3L);
        estudiante.setNombre("ESTUDIANTE");

        when(usuarioRepository.existsByEmail("test@test.com")).thenReturn(false);
        when(rolRepository.findByNombre("ESTUDIANTE")).thenReturn(Optional.of(estudiante));
        when(passwordEncoder.encode("password123")).thenReturn("encoded-password");

        Usuario savedUser = Usuario.builder()
                .id(1L)
                .nombre("Test User")
                .email("test@test.com")
                .password("encoded-password")
                .activo(true)
                .rol(estudiante)
                .build();
        when(usuarioRepository.save(any(Usuario.class))).thenReturn(savedUser);

        LoginResponse response = authService.register(request);
        assertNotNull(response);
        assertEquals("Test User", response.getNombre());
        verify(usuarioRepository).save(any(Usuario.class));
    }

    @Test
    void registerConEmailDuplicadoLanzaExcepcion() {
        RegisterRequest request = new RegisterRequest();
        request.setEmail("duplicate@test.com");

        when(usuarioRepository.existsByEmail("duplicate@test.com")).thenReturn(true);

        assertThrows(BadRequestException.class, () -> authService.register(request));
        verify(usuarioRepository, never()).save(any());
    }
}
