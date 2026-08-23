package com.sigcbqr.controller;

import com.sigcbqr.model.dto.request.QrCodigoRequest;
import com.sigcbqr.model.dto.response.ApiResponse;
import com.sigcbqr.model.dto.response.QrCodigoResponse;
import com.sigcbqr.service.QrCodigoService;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.ResponseEntity;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.web.bind.annotation.*;

import java.util.List;

@RestController
@RequestMapping("/api/qr-codigos")
@Tag(name = "Códigos QR", description = "Generación y gestión de códigos QR de libros")
@RequiredArgsConstructor
@PreAuthorize("hasAnyRole('ADMIN','BIBLIOTECARIO')")
public class QrCodigoController {

    private final QrCodigoService qrCodigoService;

    @GetMapping
    @Operation(summary = "Listar códigos QR")
    public ResponseEntity<ApiResponse> listar() {
        List<QrCodigoResponse> lista = qrCodigoService.listar();
        return ResponseEntity.ok(ApiResponse.success("Códigos QR", lista));
    }

    @GetMapping("/libro/{libroId}")
    @Operation(summary = "Obtener códigos QR por libro")
    public ResponseEntity<ApiResponse> obtenerPorLibro(@PathVariable Long libroId) {
        return ResponseEntity.ok(ApiResponse.success("Códigos QR del libro", qrCodigoService.obtenerPorLibro(libroId)));
    }

    @PostMapping
    @Operation(summary = "Crear código QR")
    public ResponseEntity<ApiResponse> crear(@Valid @RequestBody QrCodigoRequest request) {
        return ResponseEntity.status(201).body(ApiResponse.created("Código QR creado", qrCodigoService.crear(request)));
    }

    @PutMapping("/{id}/regenerar")
    @Operation(summary = "Regenerar código QR")
    public ResponseEntity<ApiResponse> regenerar(@PathVariable Long id) {
        return ResponseEntity.ok(ApiResponse.success("Código QR regenerado", qrCodigoService.regenerar(id)));
    }

    @PutMapping("/{id}/activo")
    @Operation(summary = "Activar o desactivar código QR")
    public ResponseEntity<ApiResponse> cambiarEstado(@PathVariable Long id, @RequestParam boolean activo) {
        return ResponseEntity.ok(ApiResponse.success("Estado actualizado", qrCodigoService.cambiarEstado(id, activo)));
    }
}
