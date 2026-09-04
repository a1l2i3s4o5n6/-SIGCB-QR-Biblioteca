package com.sigcbqr.controller;

import com.sigcbqr.model.dto.request.PrestamoRequest;
import com.sigcbqr.model.dto.response.ApiResponse;
import com.sigcbqr.model.dto.response.PageResponse;
import com.sigcbqr.model.dto.response.PrestamoResponse;
import com.sigcbqr.security.UserPrincipal;
import com.sigcbqr.service.PrestamoService;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import jakarta.validation.Valid;
import org.springframework.data.domain.Pageable;
import org.springframework.data.web.PageableDefault;
import org.springframework.http.ResponseEntity;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.web.bind.annotation.*;

import java.time.LocalDate;

@RestController
@RequestMapping("/api/prestamos")
@Tag(name = "Préstamos", description = "Gestión de préstamos, devoluciones y renovaciones")
public class PrestamoController {

    private final PrestamoService prestamoService;

    public PrestamoController(PrestamoService prestamoService) {
        this.prestamoService = prestamoService;
    }

    @GetMapping
    @Operation(summary = "Listar préstamos", description = "Obtiene préstamos con paginación, búsqueda y filtros por estado y rango de fechas")
    @PreAuthorize("hasAnyRole('ADMIN', 'BIBLIOTECARIO')")
    public ResponseEntity<PageResponse<PrestamoResponse>> listar(
            @RequestParam(value = "estado", required = false) String estado,
            @RequestParam(value = "q", required = false) String q,
            @RequestParam(value = "desde", required = false) LocalDate desde,
            @RequestParam(value = "hasta", required = false) LocalDate hasta,
            @PageableDefault(size = 10, sort = "fechaPrestamo") Pageable pageable) {
        boolean sinFiltros = estado == null && q == null && desde == null && hasta == null;
        var page = sinFiltros
                ? prestamoService.listar(pageable)
                : prestamoService.listarFiltrado(
                        q,
                        estado,
                        desde != null ? desde.atStartOfDay() : null,
                        hasta != null ? hasta.plusDays(1).atStartOfDay() : null,
                        pageable);
        return ResponseEntity.ok(PageResponse.from(page));
    }

    @GetMapping("/mis")
    @Operation(summary = "Mis pr\u00e9stamos", description = "Obtiene los pr\u00e9stamos del usuario autenticado")
    public ResponseEntity<PageResponse<PrestamoResponse>> mis(
            @AuthenticationPrincipal UserPrincipal principal,
            @PageableDefault(size = 10) Pageable pageable) {
        var page = prestamoService.listarPorUsuario(principal.id(), pageable);
        return ResponseEntity.ok(PageResponse.from(page));
    }

    @GetMapping("/usuario/{usuarioId}")
    @PreAuthorize("hasAnyRole('ADMIN', 'BIBLIOTECARIO')")
    @Operation(summary = "Préstamos por usuario", description = "Obtiene los préstamos de un usuario específico (solo staff)")
    public ResponseEntity<PageResponse<PrestamoResponse>> listarPorUsuario(
            @PathVariable Long usuarioId,
            @PageableDefault(size = 10) Pageable pageable) {
        var page = prestamoService.listarPorUsuario(usuarioId, pageable);
        return ResponseEntity.ok(PageResponse.from(page));
    }

    @GetMapping("/{id}")
    @Operation(summary = "Obtener préstamo", description = "Obtiene un préstamo por su ID")
    public ResponseEntity<ApiResponse> obtener(@AuthenticationPrincipal UserPrincipal principal,
                                               @PathVariable Long id) {
        var prestamo = prestamoService.obtener(id);
        if ("ESTUDIANTE".equals(principal.rol())
                && !principal.id().equals(prestamo.getUsuarioId())) {
            throw new org.springframework.security.access.AccessDeniedException(
                    "No tiene acceso a este préstamo");
        }
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
