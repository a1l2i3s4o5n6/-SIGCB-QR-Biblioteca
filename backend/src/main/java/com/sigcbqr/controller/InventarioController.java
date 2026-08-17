package com.sigcbqr.controller;

import com.sigcbqr.model.dto.response.InventarioResponse;
import com.sigcbqr.model.entity.Inventario;
import com.sigcbqr.repository.InventarioRepository;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

import java.util.List;

@RestController
@RequestMapping("/api/inventario")
@Tag(name = "Inventario", description = "Ejemplares físicos de los libros")
public class InventarioController {

    private final InventarioRepository inventarioRepository;

    public InventarioController(InventarioRepository inventarioRepository) {
        this.inventarioRepository = inventarioRepository;
    }

    @GetMapping("/disponibles")
    @Operation(summary = "Listar ejemplares disponibles", description = "Obtiene los ejemplares DISPONIBLES, opcionalmente filtrados por libro")
    public ResponseEntity<List<InventarioResponse>> listarDisponibles(@RequestParam(required = false) Long libroId) {
        List<Inventario> inventario;

        if (libroId != null) {
            inventario = inventarioRepository.findByLibroId(libroId).stream()
                    .filter(i -> "DISPONIBLE".equals(i.getEstado()))
                    .toList();
        } else {
            inventario = inventarioRepository.findByEstado("DISPONIBLE");
        }

        return ResponseEntity.ok(inventario.stream().map(this::toResponse).toList());
    }

    @GetMapping
    @Operation(summary = "Listar inventario completo", description = "Obtiene todos los ejemplares")
    public ResponseEntity<List<InventarioResponse>> listar(@RequestParam(required = false) String estado) {
        List<Inventario> inventario = (estado != null)
                ? inventarioRepository.findByEstado(estado)
                : inventarioRepository.findAll();

        return ResponseEntity.ok(inventario.stream().map(this::toResponse).toList());
    }

    private InventarioResponse toResponse(Inventario item) {
        return InventarioResponse.builder()
                .id(item.getId())
                .codigoEjemplar(item.getCodigoEjemplar())
                .estado(item.getEstado())
                .ubicacionEstante(item.getUbicacionEstante())
                .libroTitulo(item.getLibro().getTitulo())
                .build();
    }
}