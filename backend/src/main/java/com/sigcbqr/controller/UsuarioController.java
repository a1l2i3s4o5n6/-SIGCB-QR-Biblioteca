package com.sigcbqr.controller;

import com.sigcbqr.model.dto.request.UsuarioRequest;
import com.sigcbqr.model.dto.response.ApiResponse;
import com.sigcbqr.model.dto.response.PageResponse;
import com.sigcbqr.model.dto.response.UsuarioResponse;
import com.sigcbqr.service.UsuarioService;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import jakarta.validation.Valid;
import org.springframework.data.domain.Pageable;
import org.springframework.data.web.PageableDefault;
import org.springframework.http.ResponseEntity;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.web.bind.annotation.*;

@RestController
@RequestMapping("/api/usuarios")
@Tag(name = "Usuarios", description = "Gestión de usuarios del sistema")
@PreAuthorize("hasRole('ADMIN')")
public class UsuarioController {

    private final UsuarioService usuarioService;

    public UsuarioController(UsuarioService usuarioService) {
        this.usuarioService = usuarioService;
    }

    @GetMapping
    @Operation(summary = "Listar usuarios", description = "Obtiene todos los usuarios con paginación")
    public ResponseEntity<PageResponse<UsuarioResponse>> listar(
            @PageableDefault(size = 10, sort = "nombre") Pageable pageable) {
        var page = usuarioService.listar(pageable);
        return ResponseEntity.ok(PageResponse.from(page));
    }

    @GetMapping("/{id}")
    @Operation(summary = "Obtener usuario", description = "Obtiene un usuario por su ID")
    public ResponseEntity<ApiResponse> obtener(@PathVariable Long id) {
        var usuario = usuarioService.obtener(id);
        return ResponseEntity.ok(ApiResponse.success("Usuario encontrado", usuario));
    }

    @PostMapping
    @Operation(summary = "Crear usuario", description = "Crea un nuevo usuario")
    public ResponseEntity<ApiResponse> crear(@Valid @RequestBody UsuarioRequest request) {
        var usuario = usuarioService.crear(request);
        return ResponseEntity.ok(ApiResponse.created("Usuario creado", usuario));
    }

    @PutMapping("/{id}")
    @Operation(summary = "Actualizar usuario", description = "Actualiza un usuario existente")
    public ResponseEntity<ApiResponse> actualizar(@PathVariable Long id,
                                                   @Valid @RequestBody UsuarioRequest request) {
        var usuario = usuarioService.actualizar(id, request);
        return ResponseEntity.ok(ApiResponse.success("Usuario actualizado", usuario));
    }

    @DeleteMapping("/{id}")
    @Operation(summary = "Eliminar usuario", description = "Desactiva un usuario")
    public ResponseEntity<ApiResponse> eliminar(@PathVariable Long id) {
        usuarioService.eliminar(id);
        return ResponseEntity.ok(ApiResponse.success("Usuario desactivado", null));
    }
}
