package com.sigcbqr.controller;

import com.sigcbqr.exception.ResourceNotFoundException;
import com.sigcbqr.model.dto.response.ApiResponse;
import com.sigcbqr.model.dto.response.MultaResponse;
import com.sigcbqr.model.dto.response.PageResponse;
import com.sigcbqr.model.entity.Multa;
import com.sigcbqr.repository.MultaRepository;
import com.sigcbqr.security.UserPrincipal;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import org.springframework.data.domain.Pageable;
import org.springframework.data.web.PageableDefault;
import org.springframework.http.ResponseEntity;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.web.bind.annotation.*;

import java.time.LocalDateTime;

@RestController
@RequestMapping("/api/multas")
@Tag(name = "Multas", description = "Gestión de multas")
public class MultaController {

    private final MultaRepository multaRepository;
    private final com.sigcbqr.service.AuditoriaService auditoriaService;

    public MultaController(MultaRepository multaRepository,
                           com.sigcbqr.service.AuditoriaService auditoriaService) {
        this.multaRepository = multaRepository;
        this.auditoriaService = auditoriaService;
    }

    @GetMapping
    @PreAuthorize("hasAnyRole('ADMIN', 'BIBLIOTECARIO')")
    @Transactional(readOnly = true)
    @Operation(summary = "Listar multas", description = "Obtiene multas con paginación y filtro por estado de pago (solo staff)")
    public ResponseEntity<PageResponse<MultaResponse>> listar(
            @RequestParam(value = "pagada", required = false) Boolean pagada,
            @PageableDefault(size = 10) Pageable pageable) {
        var page = (pagada == null)
                ? multaRepository.findAll(pageable).map(this::toResponse)
                : multaRepository.findByPagada(pagada, pageable).map(this::toResponse);
        return ResponseEntity.ok(PageResponse.from(page));
    }

    @GetMapping("/mis")
    @Transactional(readOnly = true)
    @Operation(summary = "Mis multas", description = "Multas del usuario autenticado")
    public ResponseEntity<PageResponse<MultaResponse>> mis(
            @AuthenticationPrincipal UserPrincipal principal,
            @PageableDefault(size = 10) Pageable pageable) {
        var page = multaRepository.findByUsuarioId(principal.id(), pageable).map(this::toResponse);
        return ResponseEntity.ok(PageResponse.from(page));
    }

    @GetMapping("/usuario/{usuarioId}")
    @PreAuthorize("hasAnyRole('ADMIN', 'BIBLIOTECARIO')")
    @Transactional(readOnly = true)
    @Operation(summary = "Multas por usuario", description = "Obtiene las multas de un usuario (solo staff)")
    public ResponseEntity<PageResponse<MultaResponse>> listarPorUsuario(
            @PathVariable Long usuarioId,
            @PageableDefault(size = 10) Pageable pageable) {
        var page = multaRepository.findByUsuarioId(usuarioId, pageable).map(this::toResponse);
        return ResponseEntity.ok(PageResponse.from(page));
    }

    @PostMapping("/{id}/pagar")
    @PreAuthorize("hasAnyRole('ADMIN', 'BIBLIOTECARIO')")
    @Operation(summary = "Pagar multa", description = "Registra el pago de una multa")
    public ResponseEntity<ApiResponse> pagar(@PathVariable Long id) {
        Multa multa = multaRepository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Multa", id));
        multa.setPagada(true);
        multa.setFechaPago(LocalDateTime.now());
        multaRepository.save(multa);
        auditoriaService.registrar("PAGAR", "MULTA", multa.getId(),
                "Multa de $" + multa.getMonto() + " pagada"
                        + (multa.getUsuario() != null ? " por " + multa.getUsuario().getNombre() : ""));
        return ResponseEntity.ok(ApiResponse.success("Multa pagada", null));
    }

    private MultaResponse toResponse(Multa multa) {
        return MultaResponse.builder()
                .id(multa.getId())
                .prestamoId(multa.getPrestamo() != null ? multa.getPrestamo().getId() : null)
                .usuarioNombre(multa.getUsuario() != null ? multa.getUsuario().getNombre() : null)
                .monto(multa.getMonto())
                .pagada(multa.getPagada())
                .fechaPago(multa.getFechaPago())
                .concepto(multa.getConcepto())
                .createdAt(multa.getCreatedAt())
                .build();
    }
}