package com.sigcbqr.controller;

import com.sigcbqr.model.dto.response.AuditoriaResponse;
import com.sigcbqr.model.dto.response.PageResponse;
import com.sigcbqr.repository.AuditoriaRepository;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import org.springframework.data.domain.Pageable;
import org.springframework.data.domain.Sort;
import org.springframework.data.web.PageableDefault;
import org.springframework.http.ResponseEntity;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

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
    @Operation(summary = "Listar auditoría", description = "Obtiene el registro de actividades con paginación")
    @PreAuthorize("hasRole('ADMIN')")
    public ResponseEntity<PageResponse<AuditoriaResponse>> listar(
            @RequestParam(value = "usuarioId", required = false) Long usuarioId,
            @PageableDefault(size = 15, sort = "createdAt", direction = Sort.Direction.DESC) Pageable pageable) {
        var page = (usuarioId != null)
                ? auditoriaRepository.findByUsuarioId(usuarioId, pageable)
                : auditoriaRepository.findAll(pageable);
        return ResponseEntity.ok(PageResponse.from(page.map(this::toResponse)));
    }

    private AuditoriaResponse toResponse(com.sigcbqr.model.entity.Auditoria a) {
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