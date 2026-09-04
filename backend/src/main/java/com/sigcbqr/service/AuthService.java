package com.sigcbqr.service;

import com.sigcbqr.exception.BadRequestException;
import com.sigcbqr.exception.ResourceNotFoundException;
import com.sigcbqr.model.dto.request.LoginRequest;
import com.sigcbqr.model.dto.request.PerfilRequest;
import com.sigcbqr.model.dto.request.RegisterRequest;
import com.sigcbqr.model.dto.response.LoginResponse;
import com.sigcbqr.model.dto.response.UsuarioResponse;
import com.sigcbqr.model.entity.Carrera;
import com.sigcbqr.model.entity.Facultad;
import com.sigcbqr.model.entity.Rol;
import com.sigcbqr.model.entity.Usuario;
import com.sigcbqr.repository.CarreraRepository;
import com.sigcbqr.repository.FacultadRepository;
import com.sigcbqr.repository.RolRepository;
import com.sigcbqr.repository.UsuarioRepository;
import com.sigcbqr.security.JwtTokenProvider;
import org.springframework.security.authentication.AuthenticationManager;
import org.springframework.security.authentication.UsernamePasswordAuthenticationToken;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.time.LocalDateTime;

@Service
public class AuthService {

    private final AuthenticationManager authenticationManager;
    private final JwtTokenProvider tokenProvider;
    private final UsuarioRepository usuarioRepository;
    private final RolRepository rolRepository;
    private final PasswordEncoder passwordEncoder;
    private final AuditoriaService auditoriaService;
    private final FacultadRepository facultadRepository;
    private final CarreraRepository carreraRepository;

    public AuthService(AuthenticationManager authenticationManager,
                       JwtTokenProvider tokenProvider,
                       UsuarioRepository usuarioRepository,
                       RolRepository rolRepository,
                       PasswordEncoder passwordEncoder,
                       AuditoriaService auditoriaService,
                       FacultadRepository facultadRepository,
                       CarreraRepository carreraRepository) {
        this.authenticationManager = authenticationManager;
        this.tokenProvider = tokenProvider;
        this.usuarioRepository = usuarioRepository;
        this.rolRepository = rolRepository;
        this.passwordEncoder = passwordEncoder;
        this.auditoriaService = auditoriaService;
        this.facultadRepository = facultadRepository;
        this.carreraRepository = carreraRepository;
    }

    public LoginResponse login(LoginRequest request) {
        authenticationManager.authenticate(
                new UsernamePasswordAuthenticationToken(request.getEmail(), request.getPassword())
        );

        Usuario usuario = usuarioRepository.findByEmail(request.getEmail())
                .orElseThrow(() -> new ResourceNotFoundException("Usuario no encontrado"));

        String token = tokenProvider.generateToken(usuario.getId(), usuario.getEmail(),
                usuario.getRol().getNombre());

        auditoriaService.registrar(usuario, "LOGIN", "USUARIO", usuario.getId(),
                "Inicio de sesión exitoso");

        return LoginResponse.builder()
                .id(usuario.getId())
                .nombre(usuario.getNombre())
                .email(usuario.getEmail())
                .rol(usuario.getRol().getNombre())
                .foto(usuario.getFoto())
                .token(token)
                .mensaje("Inicio de sesión exitoso")
                .build();
    }

    @Transactional
    public LoginResponse register(RegisterRequest request) {
        if (usuarioRepository.existsByEmail(request.getEmail())) {
            throw new BadRequestException("El correo ya está registrado");
        }

        Rol rol = rolRepository.findByNombre("ESTUDIANTE")
                    .orElseThrow(() -> new ResourceNotFoundException("Rol ESTUDIANTE no encontrado"));

        Usuario usuario = Usuario.builder()
                .nombre(request.getNombre())
                .email(request.getEmail())
                .password(passwordEncoder.encode(request.getPassword()))
                .telefono(request.getTelefono())
                .activo(true)
                .rol(rol)
                .build();

        usuario = usuarioRepository.save(usuario);

        auditoriaService.registrar(usuario, "REGISTRAR", "USUARIO", usuario.getId(),
                "Registro de nuevo usuario: " + usuario.getEmail());

        String token = tokenProvider.generateToken(usuario.getId(), usuario.getEmail(),
                usuario.getRol().getNombre());

        return LoginResponse.builder()
                .id(usuario.getId())
                .nombre(usuario.getNombre())
                .email(usuario.getEmail())
                .rol(usuario.getRol().getNombre())
                .mensaje("Registro exitoso")
                .build();
    }

    public void logout(String token, LocalDateTime expiration) {
        String jti = tokenProvider.getJtiFromToken(token);
        tokenProvider.getJwtBlacklistService().blacklist(jti, expiration);
    }

    public Usuario getCurrentUser(Long userId) {
        return usuarioRepository.findById(userId)
                .orElseThrow(() -> new ResourceNotFoundException("Usuario", userId));
    }

    @Transactional
    public UsuarioResponse actualizarPerfil(Long userId, PerfilRequest request) {
        Usuario usuario = usuarioRepository.findById(userId)
                .orElseThrow(() -> new ResourceNotFoundException("Usuario", userId));

        String nuevoEmail = request.getEmail().trim().toLowerCase();
        usuarioRepository.findByEmail(nuevoEmail)
                .filter(u -> !u.getId().equals(userId))
                .ifPresent(u -> {
                    throw new BadRequestException("El correo ya está registrado");
                });

        usuario.setNombre(request.getNombre().trim());
        usuario.setEmail(nuevoEmail);
        usuario.setTelefono(request.getTelefono() == null ? null : request.getTelefono().trim());
        usuario.setFoto(request.getFoto() == null ? null : request.getFoto().trim());

        if (request.getCodigoEstudiantil() != null) {
            usuario.setCodigoEstudiantil(request.getCodigoEstudiantil().trim());
        }

        if (request.getFacultadId() != null) {
            Facultad facultad = facultadRepository.findById(request.getFacultadId())
                    .orElseThrow(() -> new ResourceNotFoundException("Facultad", request.getFacultadId()));
            usuario.setFacultad(facultad);
        }

        if (request.getCarreraId() != null) {
            Carrera carrera = carreraRepository.findById(request.getCarreraId())
                    .orElseThrow(() -> new ResourceNotFoundException("Carrera", request.getCarreraId()));
            usuario.setCarrera(carrera);
        }

        String nuevaPassword = request.getPasswordNueva();
        if (nuevaPassword != null && !nuevaPassword.isBlank()) {
            String actual = request.getPasswordActual();
            if (actual == null || actual.isBlank()
                    || !passwordEncoder.matches(actual, usuario.getPassword())) {
                throw new BadRequestException("La contraseña actual es incorrecta");
            }
            if (nuevaPassword.length() < 6) {
                throw new BadRequestException("La nueva contraseña debe tener al menos 6 caracteres");
            }
            usuario.setPassword(passwordEncoder.encode(nuevaPassword));
        }

        usuarioRepository.save(usuario);

        auditoriaService.registrar(usuario, "ACTUALIZAR", "PERFIL", usuario.getId(),
                "Actualización de perfil por " + usuario.getEmail());

        return UsuarioResponse.builder()
                .id(usuario.getId())
                .nombre(usuario.getNombre())
                .email(usuario.getEmail())
                .telefono(usuario.getTelefono())
                .foto(usuario.getFoto())
                .codigoEstudiantil(usuario.getCodigoEstudiantil())
                .facultadId(usuario.getFacultad() != null ? usuario.getFacultad().getId() : null)
                .facultadNombre(usuario.getFacultad() != null ? usuario.getFacultad().getNombre() : null)
                .carreraId(usuario.getCarrera() != null ? usuario.getCarrera().getId() : null)
                .carreraNombre(usuario.getCarrera() != null ? usuario.getCarrera().getNombre() : null)
                .rol(usuario.getRol().getNombre())
                .activo(usuario.getActivo())
                .createdAt(usuario.getCreatedAt())
                .build();
    }
}
