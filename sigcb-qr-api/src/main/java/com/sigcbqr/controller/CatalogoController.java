package com.sigcbqr.controller;

import com.sigcbqr.model.dto.response.ApiResponse;
import com.sigcbqr.model.entity.*;
import com.sigcbqr.repository.*;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import org.springframework.data.domain.Pageable;
import org.springframework.data.web.PageableDefault;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

import java.util.List;

@RestController
@RequestMapping("/api")
@Tag(name = "Catálogo", description = "Gestión de autores, editoriales, categorías, facultades y carreras")
public class CatalogoController {

    private final AutorRepository autorRepository;
    private final EditorialRepository editorialRepository;
    private final CategoriaRepository categoriaRepository;
    private final FacultadRepository facultadRepository;
    private final CarreraRepository carreraRepository;

    public CatalogoController(AutorRepository autorRepository,
                              EditorialRepository editorialRepository,
                              CategoriaRepository categoriaRepository,
                              FacultadRepository facultadRepository,
                              CarreraRepository carreraRepository) {
        this.autorRepository = autorRepository;
        this.editorialRepository = editorialRepository;
        this.categoriaRepository = categoriaRepository;
        this.facultadRepository = facultadRepository;
        this.carreraRepository = carreraRepository;
    }

    // === AUTORES ===
    @GetMapping("/autores")
    @Operation(summary = "Listar autores", description = "Obtiene todos los autores")
    public ResponseEntity<?> listarAutores(@PageableDefault(size = 20, sort = "apellido") Pageable pageable) {
        return ResponseEntity.ok(autorRepository.findByActivoTrue(pageable));
    }

    @PostMapping("/autores")
    @Operation(summary = "Crear autor")
    public ResponseEntity<ApiResponse> crearAutor(@RequestBody Autor autor) {
        autor.setActivo(true);
        autor = autorRepository.save(autor);
        return ResponseEntity.ok(ApiResponse.created("Autor creado", autor));
    }

    @PutMapping("/autores/{id}")
    @Operation(summary = "Actualizar autor")
    public ResponseEntity<ApiResponse> actualizarAutor(@PathVariable Long id, @RequestBody Autor request) {
        Autor autor = autorRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Autor no encontrado"));
        autor.setNombre(request.getNombre());
        autor.setApellido(request.getApellido());
        autor.setNacionalidad(request.getNacionalidad());
        autorRepository.save(autor);
        return ResponseEntity.ok(ApiResponse.success("Autor actualizado", autor));
    }

    // === EDITORIALES ===
    @GetMapping("/editoriales")
    @Operation(summary = "Listar editoriales")
    public ResponseEntity<?> listarEditoriales(@PageableDefault(size = 20) Pageable pageable) {
        return ResponseEntity.ok(editorialRepository.findByActivoTrue(pageable));
    }

    @PostMapping("/editoriales")
    @Operation(summary = "Crear editorial")
    public ResponseEntity<ApiResponse> crearEditorial(@RequestBody Editorial editorial) {
        editorial.setActivo(true);
        editorial = editorialRepository.save(editorial);
        return ResponseEntity.ok(ApiResponse.created("Editorial creada", editorial));
    }

    @PutMapping("/editoriales/{id}")
    @Operation(summary = "Actualizar editorial")
    public ResponseEntity<ApiResponse> actualizarEditorial(@PathVariable Long id, @RequestBody Editorial request) {
        Editorial editorial = editorialRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Editorial no encontrada"));
        editorial.setNombre(request.getNombre());
        editorial.setPais(request.getPais());
        editorialRepository.save(editorial);
        return ResponseEntity.ok(ApiResponse.success("Editorial actualizada", editorial));
    }

    @DeleteMapping("/editoriales/{id}")
    @Operation(summary = "Eliminar editorial")
    public ResponseEntity<ApiResponse> eliminarEditorial(@PathVariable Long id) {
        Editorial editorial = editorialRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Editorial no encontrada"));
        editorial.setActivo(false);
        editorialRepository.save(editorial);
        return ResponseEntity.ok(ApiResponse.success("Editorial desactivada", null));
    }

    // === CATEGORÍAS ===
    @GetMapping("/categorias")
    @Operation(summary = "Listar categorías")
    public ResponseEntity<?> listarCategorias(@PageableDefault(size = 20) Pageable pageable) {
        return ResponseEntity.ok(categoriaRepository.findByActivoTrue(pageable));
    }

    @PostMapping("/categorias")
    @Operation(summary = "Crear categoría")
    public ResponseEntity<ApiResponse> crearCategoria(@RequestBody Categoria categoria) {
        categoria.setActivo(true);
        categoria = categoriaRepository.save(categoria);
        return ResponseEntity.ok(ApiResponse.created("Categoría creada", categoria));
    }

    @PutMapping("/categorias/{id}")
    @Operation(summary = "Actualizar categoría")
    public ResponseEntity<ApiResponse> actualizarCategoria(@PathVariable Long id, @RequestBody Categoria request) {
        Categoria categoria = categoriaRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Categoría no encontrada"));
        categoria.setNombre(request.getNombre());
        categoria.setDescripcion(request.getDescripcion());
        categoriaRepository.save(categoria);
        return ResponseEntity.ok(ApiResponse.success("Categoría actualizada", categoria));
    }

    @DeleteMapping("/categorias/{id}")
    @Operation(summary = "Eliminar categoría")
    public ResponseEntity<ApiResponse> eliminarCategoria(@PathVariable Long id) {
        Categoria categoria = categoriaRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Categoría no encontrada"));
        categoria.setActivo(false);
        categoriaRepository.save(categoria);
        return ResponseEntity.ok(ApiResponse.success("Categoría desactivada", null));
    }

    // === FACULTADES ===
    @GetMapping("/facultades")
    @Operation(summary = "Listar facultades")
    public ResponseEntity<List<Facultad>> listarFacultades() {
        return ResponseEntity.ok(facultadRepository.findByActivoTrue());
    }

    // === CARRERAS ===
    @GetMapping("/carreras")
    @Operation(summary = "Listar carreras", description = "Filtrar por facultadId opcional")
    public ResponseEntity<?> listarCarreras(@RequestParam(required = false) Long facultadId) {
        if (facultadId != null) {
            return ResponseEntity.ok(carreraRepository.findByFacultadId(facultadId));
        }
        return ResponseEntity.ok(carreraRepository.findByActivoTrue());
    }
}
