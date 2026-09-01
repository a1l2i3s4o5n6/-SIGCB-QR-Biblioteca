package com.sigcbqr.controller;

import com.sigcbqr.model.dto.request.SancionRequest;
import com.sigcbqr.model.dto.response.ApiResponse;
import com.sigcbqr.model.dto.response.PageResponse;
import com.sigcbqr.model.dto.response.SancionResponse;
import com.sigcbqr.security.UserPrincipal;
import com.sigcbqr.service.SancionService;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.data.domain.Pageable;
import org.springframework.data.web.PageableDefault;
import org.springframework.http.ResponseEntity;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.web.bind.annotation.*;

@RestController
@RequestMapping("/api/sanciones")
@Tag(name = "Sanciones", description = "Gestión de sanciones a usuarios")
@RequiredArgsConstructor
public class SancionController {

    private final SancionService sancionService;

    @GetMapping
    @PreAuthorize("hasAnyRole('ADMIN', 'BIBLIOTECARIO')")
    @Operation(summary = "Listar sanciones", description = "Sanciones paginadas con filtro opcional por estado")
    public ResponseEntity<PageResponse<SancionResponse>> listar(
            @RequestParam(required = false) Boolean activa,
            @PageableDefault(size = 10) Pageable pageable) {
        return ResponseEntity.ok(PageResponse.from(sancionService.listar(activa, pageable)));
    }

    @GetMapping("/mis")
    @Operation(summary = "Mis sanciones", description = "Sanciones del usuario autenticado")
    public ResponseEntity<PageResponse<SancionResponse>> mis(
            @AuthenticationPrincipal UserPrincipal principal,
            @PageableDefault(size = 10) Pageable pageable) {
        return ResponseEntity.ok(PageResponse.from(sancionService.listarPorUsuario(principal.id(), pageable)));
    }

    @GetMapping("/usuario/{usuarioId}")
    @PreAuthorize("hasAnyRole('ADMIN', 'BIBLIOTECARIO')")
    @Operation(summary = "Sanciones por usuario")
    public ResponseEntity<PageResponse<SancionResponse>> listarPorUsuario(
            @PathVariable Long usuarioId,
            @PageableDefault(size = 10) Pageable pageable) {
        return ResponseEntity.ok(PageResponse.from(sancionService.listarPorUsuario(usuarioId, pageable)));
    }

    @PostMapping
    @PreAuthorize("hasAnyRole('ADMIN', 'BIBLIOTECARIO')")
    @Operation(summary = "Aplicar sanción")
    public ResponseEntity<ApiResponse> crear(@Valid @RequestBody SancionRequest request) {
        return ResponseEntity.status(201).body(ApiResponse.created(
                "Sanción aplicada", sancionService.crear(request)));
    }

    @PutMapping("/{id}/levantar")
    @PreAuthorize("hasAnyRole('ADMIN', 'BIBLIOTECARIO')")
    @Operation(summary = "Levantar sanción")
    public ResponseEntity<ApiResponse> levantar(@PathVariable Long id) {
        return ResponseEntity.ok(ApiResponse.success("Sanción levantada", sancionService.levantar(id)));
    }
}