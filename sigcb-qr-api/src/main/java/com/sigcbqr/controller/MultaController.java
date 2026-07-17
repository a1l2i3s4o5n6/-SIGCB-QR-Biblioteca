package com.sigcbqr.controller;

import com.sigcbqr.model.dto.response.ApiResponse;
import com.sigcbqr.model.dto.response.PageResponse;
import com.sigcbqr.model.entity.Multa;
import com.sigcbqr.repository.MultaRepository;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import org.springframework.data.domain.Pageable;
import org.springframework.data.web.PageableDefault;
import org.springframework.http.ResponseEntity;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.web.bind.annotation.*;

import java.time.LocalDateTime;

@RestController
@RequestMapping("/api/multas")
@Tag(name = "Multas", description = "Gestión de multas")
public class MultaController {

    private final MultaRepository multaRepository;

    public MultaController(MultaRepository multaRepository) {
        this.multaRepository = multaRepository;
    }

    @GetMapping
    @Operation(summary = "Listar multas", description = "Obtiene todas las multas con paginación")
    public ResponseEntity<PageResponse<Multa>> listar(
            @PageableDefault(size = 10) Pageable pageable) {
        var page = multaRepository.findAll(pageable);
        return ResponseEntity.ok(PageResponse.from(page));
    }

    @GetMapping("/usuario/{usuarioId}")
    @Operation(summary = "Multas por usuario", description = "Obtiene las multas de un usuario")
    public ResponseEntity<PageResponse<Multa>> listarPorUsuario(
            @PathVariable Long usuarioId,
            @PageableDefault(size = 10) Pageable pageable) {
        var page = multaRepository.findByUsuarioId(usuarioId, pageable);
        return ResponseEntity.ok(PageResponse.from(page));
    }

    @PostMapping("/{id}/pagar")
    @PreAuthorize("hasAnyRole('ADMIN', 'BIBLIOTECARIO')")
    @Operation(summary = "Pagar multa", description = "Registra el pago de una multa")
    public ResponseEntity<ApiResponse> pagar(@PathVariable Long id) {
        Multa multa = multaRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Multa no encontrada"));
        multa.setPagada(true);
        multa.setFechaPago(LocalDateTime.now());
        multaRepository.save(multa);
        return ResponseEntity.ok(ApiResponse.success("Multa pagada", null));
    }
}
