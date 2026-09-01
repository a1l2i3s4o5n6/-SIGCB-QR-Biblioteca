package com.sigcbqr.controller;

import com.sigcbqr.model.dto.response.AuditoriaResponse;
import com.sigcbqr.model.dto.response.PageResponse;
import com.sigcbqr.repository.AuditoriaRepository;
import com.sigcbqr.model.entity.Auditoria;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.domain.Sort;
import org.springframework.data.web.PageableDefault;
import org.springframework.format.annotation.DateTimeFormat;
import org.springframework.http.ResponseEntity;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

import java.time.LocalDate;
import java.time.LocalDateTime;

@RestController
@RequestMapping("/api/auditoria")
@Tag(name = "Auditoría", description = "Registro de actividades del sistema")
public class AuditoriaController {

    private final AuditoriaRepository auditoriaRepository;

    public AuditoriaController(AuditoriaRepository auditoriaRepository) {
        this.auditoriaRepository = auditoriaRepository;
    }

    @GetMapping
    @Transactional(readOnly = true)
    @Operation(summary = "Listar auditoría", description = "Obtiene el registro de actividades con filtros por usuario y rango de fechas (desde/hasta, formato yyyy-MM-dd)")
    @PreAuthorize("hasRole('ADMIN')")
    public ResponseEntity<PageResponse<AuditoriaResponse>> listar(
            @RequestParam(value = "usuarioId", required = false) Long usuarioId,
            @RequestParam(value = "desde", required = false) @DateTimeFormat(iso = DateTimeFormat.ISO.DATE) LocalDate desde,
            @RequestParam(value = "hasta", required = false) @DateTimeFormat(iso = DateTimeFormat.ISO.DATE) LocalDate hasta,
            @PageableDefault(size = 15, sort = "createdAt", direction = Sort.Direction.DESC) Pageable pageable) {
        Page<Auditoria> page;
        if (desde != null && hasta != null) {
            LocalDateTime inicio = desde.atStartOfDay();
            LocalDateTime fin = hasta.plusDays(1).atStartOfDay();
            page = (usuarioId != null)
                    ? auditoriaRepository.findByUsuarioIdAndCreatedAtBetween(usuarioId, inicio, fin, pageable)
                    : auditoriaRepository.findByCreatedAtBetween(inicio, fin, pageable);
        } else {
            page = (usuarioId != null)
                    ? auditoriaRepository.findByUsuarioId(usuarioId, pageable)
                    : auditoriaRepository.findAll(pageable);
        }
        return ResponseEntity.ok(PageResponse.from(page.map(this::toResponse)));
    }

    private AuditoriaResponse toResponse(Auditoria a) {
        return AuditoriaResponse.builder()
                .id(a.getId())
                .usuarioNombre(a.getUsuario() != null ? a.getUsuario().getNombre() : null)
                .accion(a.getAccion())
                .entidad(a.getEntidad())
                .entidadId(a.getEntidadId())
                .detalle(a.getDetalle())
                .createdAt(a.getCreatedAt())
                .build();
    }
}