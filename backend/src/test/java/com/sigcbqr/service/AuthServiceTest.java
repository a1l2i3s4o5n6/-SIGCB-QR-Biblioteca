package com.sigcbqr.service;

import com.sigcbqr.exception.BadRequestException;
import com.sigcbqr.model.dto.request.LoginRequest;
import com.sigcbqr.model.dto.request.PerfilRequest;
import com.sigcbqr.model.dto.request.RegisterRequest;
import com.sigcbqr.model.dto.response.LoginResponse;
import com.sigcbqr.model.dto.response.UsuarioResponse;
import com.sigcbqr.model.entity.Rol;
import com.sigcbqr.model.entity.Usuario;
import com.sigcbqr.repository.CarreraRepository;
import com.sigcbqr.repository.FacultadRepository;
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
    @Mock
    private FacultadRepository facultadRepository;
    @Mock
    private CarreraRepository carreraRepository;

    private AuthService authService;

    @BeforeEach
    void setUp() {
        authService = new AuthService(authenticationManager, tokenProvider, usuarioRepository, rolRepository, passwordEncoder, auditoriaService, facultadRepository, carreraRepository);
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

    private Usuario perfilUsuario(Long id, String email, String password) {
        Rol rol = new Rol();
        rol.setId(1L);
        rol.setNombre("ESTUDIANTE");
        return Usuario.builder()
                .id(id)
                .nombre("Antes")
                .email(email)
                .password(password)
                .activo(true)
                .rol(rol)
                .build();
    }

    @Test
    void actualizarPerfilConDatosValidos() {
        Usuario usuario = perfilUsuario(1L, "antes@test.com", "$2a$hash-viejo");
        PerfilRequest request = new PerfilRequest();
        request.setNombre("Nombre Nuevo");
        request.setEmail("NUEVO@test.com");
        request.setTelefono("0999999999");
        request.setFoto("/uploads/avatar.jpg");

        when(usuarioRepository.findById(1L)).thenReturn(Optional.of(usuario));
        when(usuarioRepository.findByEmail("nuevo@test.com")).thenReturn(Optional.empty());
        when(usuarioRepository.save(any(Usuario.class))).thenAnswer(i -> i.getArgument(0));

        UsuarioResponse response = authService.actualizarPerfil(1L, request);
        assertNotNull(response);
        assertEquals("Nombre Nuevo", response.getNombre());
        assertEquals("nuevo@test.com", response.getEmail());
        assertEquals("/uploads/avatar.jpg", response.getFoto());
        verify(usuarioRepository).save(any(Usuario.class));
    }

    @Test
    void actualizarPerfilCambiaPasswordConLaActualCorrecta() {
        Usuario usuario = perfilUsuario(1L, "user@test.com", "$2a$hash-viejo");
        PerfilRequest request = new PerfilRequest();
        request.setNombre("Igual");
        request.setEmail("user@test.com");
        request.setPasswordActual("clave-vieja");
        request.setPasswordNueva("clave-nueva-123");

        when(usuarioRepository.findById(1L)).thenReturn(Optional.of(usuario));
        when(usuarioRepository.findByEmail("user@test.com")).thenReturn(Optional.of(usuario));
        when(passwordEncoder.matches("clave-vieja", "$2a$hash-viejo")).thenReturn(true);
        when(passwordEncoder.encode("clave-nueva-123")).thenReturn("$2a$hash-nuevo");
        when(usuarioRepository.save(any(Usuario.class))).thenAnswer(i -> i.getArgument(0));

        authService.actualizarPerfil(1L, request);
        verify(usuarioRepository).save(argThat(u -> "$2a$hash-nuevo".equals(u.getPassword())));
    }

    @Test
    void actualizarPerfilConPasswordActualIncorrectaLanzaExcepcion() {
        Usuario usuario = perfilUsuario(1L, "user@test.com", "$2a$hash-viejo");
        PerfilRequest request = new PerfilRequest();
        request.setNombre("Igual");
        request.setEmail("user@test.com");
        request.setPasswordActual("clave-errada");
        request.setPasswordNueva("clave-nueva-123");

        when(usuarioRepository.findById(1L)).thenReturn(Optional.of(usuario));
        when(usuarioRepository.findByEmail("user@test.com")).thenReturn(Optional.of(usuario));
        when(passwordEncoder.matches("clave-errada", "$2a$hash-viejo")).thenReturn(false);

        assertThrows(BadRequestException.class, () -> authService.actualizarPerfil(1L, request));
        verify(usuarioRepository, never()).save(any());
    }

    @Test
    void actualizarPerfilConCorreoDeOtroUsuarioLanzaExcepcion() {
        Usuario usuario = perfilUsuario(1L, "yo@test.com", "hash");
        Usuario duplicado = perfilUsuario(2L, "ocupado@test.com", "hash");
        PerfilRequest request = new PerfilRequest();
        request.setNombre("Nombre");
        request.setEmail("ocupado@test.com");

        when(usuarioRepository.findById(1L)).thenReturn(Optional.of(usuario));
        when(usuarioRepository.findByEmail("ocupado@test.com")).thenReturn(Optional.of(duplicado));

        assertThrows(BadRequestException.class, () -> authService.actualizarPerfil(1L, request));
        verify(usuarioRepository, never()).save(any());
    }
}
