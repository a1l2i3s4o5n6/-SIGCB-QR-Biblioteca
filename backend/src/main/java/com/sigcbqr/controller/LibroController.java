package com.sigcbqr.controller;

import com.sigcbqr.model.dto.request.LibroRequest;
import com.sigcbqr.model.dto.response.ApiResponse;
import com.sigcbqr.model.dto.response.LibroResponse;
import com.sigcbqr.model.dto.response.PageResponse;
import com.sigcbqr.service.LibroService;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import jakarta.validation.Valid;
import org.springframework.data.domain.Pageable;
import org.springframework.data.web.PageableDefault;
import org.springframework.http.ResponseEntity;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.web.bind.annotation.*;

@RestController
@RequestMapping("/api/libros")
@Tag(name = "Libros", description = "CRUD de material bibliográfico")
public class LibroController {

    private final LibroService libroService;

    public LibroController(LibroService libroService) {
        this.libroService = libroService;
    }

    @GetMapping
    @Operation(summary = "Listar libros", description = "Obtiene todos los libros activos con paginación")
    public ResponseEntity<PageResponse<LibroResponse>> listar(
            @PageableDefault(size = 10, sort = "titulo") Pageable pageable) {
        var page = libroService.listar(pageable);
        return ResponseEntity.ok(PageResponse.from(page));
    }

    @GetMapping("/buscar")
    @Operation(summary = "Buscar libros", description = "Busca libros por título")
    public ResponseEntity<PageResponse<LibroResponse>> buscar(
            @RequestParam String q,
            @PageableDefault(size = 10) Pageable pageable) {
        var page = libroService.buscar(q, pageable);
        return ResponseEntity.ok(PageResponse.from(page));
    }

    @GetMapping("/{id}")
    @Operation(summary = "Obtener libro", description = "Obtiene un libro por su ID")
    public ResponseEntity<ApiResponse> obtener(@PathVariable Long id) {
        var libro = libroService.obtener(id);
        return ResponseEntity.ok(ApiResponse.success("Libro encontrado", libro));
    }

    @PostMapping
    @PreAuthorize("hasAnyRole('ADMIN', 'BIBLIOTECARIO')")
    @Operation(summary = "Crear libro", description = "Crea un nuevo libro (solo admin/bibliotecario)")
    public ResponseEntity<ApiResponse> crear(@Valid @RequestBody LibroRequest request) {
        var libro = libroService.crear(request);
        return ResponseEntity.ok(ApiResponse.created("Libro creado exitosamente", libro));
    }

    @PutMapping("/{id}")
    @PreAuthorize("hasAnyRole('ADMIN', 'BIBLIOTECARIO')")
    @Operation(summary = "Actualizar libro", description = "Actualiza un libro existente")
    public ResponseEntity<ApiResponse> actualizar(@PathVariable Long id,
                                                   @Valid @RequestBody LibroRequest request) {
        var libro = libroService.actualizar(id, request);
        return ResponseEntity.ok(ApiResponse.success("Libro actualizado", libro));
    }

    @DeleteMapping("/{id}")
    @PreAuthorize("hasRole('ADMIN')")
    @Operation(summary = "Eliminar libro", description = "Desactiva un libro (solo admin)")
    public ResponseEntity<ApiResponse> eliminar(@PathVariable Long id) {
        libroService.eliminar(id);
        return ResponseEntity.ok(ApiResponse.success("Libro desactivado", null));
    }
}
