package com.sigcbqr.controller;

import com.sigcbqr.model.dto.request.PerfilRequest;
import com.sigcbqr.model.dto.response.ApiResponse;
import com.sigcbqr.model.dto.response.UsuarioResponse;
import com.sigcbqr.security.UserPrincipal;
import com.sigcbqr.service.AuthService;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import jakarta.validation.Valid;
import org.springframework.http.ResponseEntity;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.web.bind.annotation.*;

/**
 * Perfil del usuario autenticado. Cualquier rol autenticado puede ver y
 * actualizar sus propios datos (nombre, correo, teléfono, foto y contraseña).
 */
@RestController
@RequestMapping("/api/perfil")
@Tag(name = "Perfil", description = "Gestión del perfil del usuario autenticado")
public class PerfilController {

    private final AuthService authService;

    public PerfilController(AuthService authService) {
        this.authService = authService;
    }

    @GetMapping
    @Operation(summary = "Mi perfil", description = "Datos del perfil del usuario autenticado")
    public ResponseEntity<ApiResponse> ver(@AuthenticationPrincipal UserPrincipal userPrincipal) {
        var usuario = authService.getCurrentUser(userPrincipal.id());
        var response = UsuarioResponse.builder()
                .id(usuario.getId())
                .nombre(usuario.getNombre())
                .email(usuario.getEmail())
                .telefono(usuario.getTelefono())
                .foto(usuario.getFoto())
                .rol(usuario.getRol().getNombre())
                .activo(usuario.getActivo())
                .createdAt(usuario.getCreatedAt())
                .build();
        return ResponseEntity.ok(ApiResponse.success("Perfil", response));
    }

    @PutMapping
    @Operation(summary = "Actualizar perfil", description = "Actualiza nombre, correo, teléfono, foto y opcionalmente la contraseña")
    public ResponseEntity<ApiResponse> actualizar(@AuthenticationPrincipal UserPrincipal userPrincipal,
                                                  @Valid @RequestBody PerfilRequest request) {
        var response = authService.actualizarPerfil(userPrincipal.id(), request);
        return ResponseEntity.ok(ApiResponse.success("Perfil actualizado", response));
    }
}