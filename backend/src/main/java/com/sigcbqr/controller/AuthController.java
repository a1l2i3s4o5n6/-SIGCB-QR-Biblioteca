package com.sigcbqr.controller;

import com.sigcbqr.model.dto.request.LoginRequest;
import com.sigcbqr.model.dto.request.RegisterRequest;
import com.sigcbqr.model.dto.response.ApiResponse;
import com.sigcbqr.model.dto.response.LoginResponse;
import com.sigcbqr.model.dto.response.UsuarioResponse;
import com.sigcbqr.security.JwtTokenProvider;
import com.sigcbqr.security.UserPrincipal;
import com.sigcbqr.service.AuthService;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.validation.Valid;
import org.springframework.http.ResponseEntity;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.web.bind.annotation.*;

import java.time.LocalDateTime;
import java.util.Date;

@RestController
@RequestMapping("/api/auth")
@Tag(name = "Autenticación", description = "Endpoints de autenticación con JWT en cookie HttpOnly")
public class AuthController {

    private final AuthService authService;
    private final JwtTokenProvider tokenProvider;

    public AuthController(AuthService authService, JwtTokenProvider tokenProvider) {
        this.authService = authService;
        this.tokenProvider = tokenProvider;
    }

    @PostMapping("/login")
    @Operation(summary = "Iniciar sesión", description = "Autentica al usuario y devuelve un JWT en cookie HttpOnly")
    public ResponseEntity<ApiResponse> login(@Valid @RequestBody LoginRequest request,
                                             HttpServletResponse response) {
        LoginResponse loginResponse = authService.login(request);

        String token = tokenProvider.generateToken(
                loginResponse.getId(),
                loginResponse.getEmail(),
                loginResponse.getRol()
        );

        var cookie = tokenProvider.createAccessTokenCookie(token);
        response.addCookie(cookie);

        return ResponseEntity.ok(ApiResponse.success("Inicio de sesión exitoso", loginResponse));
    }

    @PostMapping("/register")
    @Operation(summary = "Registrar usuario", description = "Registra un nuevo usuario y devuelve JWT en cookie")
    public ResponseEntity<ApiResponse> register(@Valid @RequestBody RegisterRequest request,
                                                HttpServletResponse response) {
        LoginResponse loginResponse = authService.register(request);

        String token = tokenProvider.generateToken(
                loginResponse.getId(),
                loginResponse.getEmail(),
                loginResponse.getRol()
        );

        var cookie = tokenProvider.createAccessTokenCookie(token);
        response.addCookie(cookie);

        return ResponseEntity.ok(ApiResponse.success("Registro exitoso", loginResponse));
    }

    @PostMapping("/logout")
    @Operation(summary = "Cerrar sesión", description = "Invalida el JWT actual agregando su JTI a la blacklist")
    public ResponseEntity<ApiResponse> logout(HttpServletRequest request,
                                              HttpServletResponse response) {
        String token = tokenProvider.extractTokenFromCookie(request);
        if (token != null && tokenProvider.validateToken(token)) {
            Date expiration = tokenProvider.getExpirationFromToken(token);
            authService.logout(token, new java.sql.Timestamp(expiration.getTime()).toLocalDateTime());
        }

        var cookie = tokenProvider.createLogoutCookie();
        response.addCookie(cookie);

        return ResponseEntity.ok(ApiResponse.success("Sesión cerrada exitosamente", null));
    }

    @GetMapping("/me")
    @Operation(summary = "Usuario actual", description = "Obtiene los datos del usuario autenticado")
    public ResponseEntity<ApiResponse> me(@AuthenticationPrincipal UserPrincipal userPrincipal) {
        var usuario = authService.getCurrentUser(userPrincipal.id());
        var response = UsuarioResponse.builder()
                .id(usuario.getId())
                .nombre(usuario.getNombre())
                .email(usuario.getEmail())
                .telefono(usuario.getTelefono())
                .rol(usuario.getRol().getNombre())
                .activo(usuario.getActivo())
                .createdAt(usuario.getCreatedAt())
                .build();
        return ResponseEntity.ok(ApiResponse.success("Usuario actual", response));
    }
}
