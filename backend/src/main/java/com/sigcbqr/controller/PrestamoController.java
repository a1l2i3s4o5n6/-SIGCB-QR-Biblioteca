package com.sigcbqr.controller;

import com.sigcbqr.model.dto.request.PrestamoRequest;
import com.sigcbqr.model.dto.response.ApiResponse;
import com.sigcbqr.model.dto.response.PageResponse;
import com.sigcbqr.model.dto.response.PrestamoResponse;
import com.sigcbqr.service.PrestamoService;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import jakarta.validation.Valid;
import org.springframework.data.domain.Pageable;
import org.springframework.data.web.PageableDefault;
import org.springframework.http.ResponseEntity;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.web.bind.annotation.*;

@RestController
@RequestMapping("/api/prestamos")
@Tag(name = "Préstamos", description = "Gestión de préstamos, devoluciones y renovaciones")
public class PrestamoController {

    private final PrestamoService prestamoService;

    public PrestamoController(PrestamoService prestamoService) {
        this.prestamoService = prestamoService;
    }

    @GetMapping
    @Operation(summary = "Listar préstamos", description = "Obtiene todos los préstamos con paginación")
    @PreAuthorize("hasAnyRole('ADMIN', 'BIBLIOTECARIO')")
    public ResponseEntity<PageResponse<PrestamoResponse>> listar(
            @PageableDefault(size = 10, sort = "fechaPrestamo") Pageable pageable) {
        var page = prestamoService.listar(pageable);
        return ResponseEntity.ok(PageResponse.from(page));
    }

    @GetMapping("/usuario/{usuarioId}")
    @Operation(summary = "Préstamos por usuario", description = "Obtiene los préstamos de un usuario específico")
    public ResponseEntity<PageResponse<PrestamoResponse>> listarPorUsuario(
            @PathVariable Long usuarioId,
            @PageableDefault(size = 10) Pageable pageable) {
        var page = prestamoService.listarPorUsuario(usuarioId, pageable);
        return ResponseEntity.ok(PageResponse.from(page));
    }

    @GetMapping("/{id}")
    @Operation(summary = "Obtener préstamo", description = "Obtiene un préstamo por su ID")
    public ResponseEntity<ApiResponse> obtener(@PathVariable Long id) {
        var prestamo = prestamoService.obtener(id);
        return ResponseEntity.ok(ApiResponse.success("Préstamo encontrado", prestamo));
    }

    @PostMapping
    @PreAuthorize("hasAnyRole('ADMIN', 'BIBLIOTECARIO')")
    @Operation(summary = "Crear préstamo", description = "Registra un nuevo préstamo")
    public ResponseEntity<ApiResponse> crear(@Valid @RequestBody PrestamoRequest request) {
        var prestamo = prestamoService.crear(request);
        return ResponseEntity.ok(ApiResponse.created("Préstamo registrado", prestamo));
    }

    @PutMapping("/{id}/devolver")
    @PreAuthorize("hasAnyRole('ADMIN', 'BIBLIOTECARIO')")
    @Operation(summary = "Devolver libro", description = "Registra la devolución de un préstamo")
    public ResponseEntity<ApiResponse> devolver(@PathVariable Long id) {
        var prestamo = prestamoService.devolver(id);
        return ResponseEntity.ok(ApiResponse.success("Devolución registrada", prestamo));
    }

    @PutMapping("/{id}/renovar")
    @PreAuthorize("hasAnyRole('ADMIN', 'BIBLIOTECARIO')")
    @Operation(summary = "Renovar préstamo", description = "Renueva un préstamo activo")
    public ResponseEntity<ApiResponse> renovar(@PathVariable Long id) {
        var prestamo = prestamoService.renovar(id);
        return ResponseEntity.ok(ApiResponse.success("Préstamo renovado", prestamo));
    }
}
