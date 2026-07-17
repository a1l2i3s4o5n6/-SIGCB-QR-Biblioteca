package com.sigcbqr.service;

import com.sigcbqr.exception.BadRequestException;
import com.sigcbqr.exception.ResourceNotFoundException;
import com.sigcbqr.model.dto.request.LibroRequest;
import com.sigcbqr.model.dto.response.LibroResponse;
import com.sigcbqr.model.entity.*;
import com.sigcbqr.repository.*;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.util.Set;
import java.util.stream.Collectors;

@Service
public class LibroService {

    private final LibroRepository libroRepository;
    private final CategoriaRepository categoriaRepository;
    private final EditorialRepository editorialRepository;
    private final AutorRepository autorRepository;

    public LibroService(LibroRepository libroRepository,
                        CategoriaRepository categoriaRepository,
                        EditorialRepository editorialRepository,
                        AutorRepository autorRepository) {
        this.libroRepository = libroRepository;
        this.categoriaRepository = categoriaRepository;
        this.editorialRepository = editorialRepository;
        this.autorRepository = autorRepository;
    }

    public Page<LibroResponse> listar(Pageable pageable) {
        return libroRepository.findByActivoTrue(pageable).map(this::toResponse);
    }

    public Page<LibroResponse> buscar(String query, Pageable pageable) {
        return libroRepository.findByTituloContainingIgnoreCase(query, pageable).map(this::toResponse);
    }

    public LibroResponse obtener(Long id) {
        Libro libro = libroRepository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Libro", id));
        return toResponse(libro);
    }

    @Transactional
    public LibroResponse crear(LibroRequest request) {
        if (request.getEjemplaresTotales() != null && request.getEjemplaresTotales() < 1) {
            throw new BadRequestException("Debe haber al menos 1 ejemplar");
        }

        Libro libro = new Libro();
        libro.setTitulo(request.getTitulo());
        libro.setIsbn(request.getIsbn());
        libro.setAnioPublicacion(request.getAnioPublicacion());
        libro.setEdicion(request.getEdicion());
        libro.setEjemplaresTotales(request.getEjemplaresTotales());
        libro.setEjemplaresDisponibles(request.getEjemplaresTotales());
        libro.setUbicacion(request.getUbicacion());
        libro.setDescripcion(request.getDescripcion());

        if (request.getCategoriaId() != null) {
            Categoria categoria = categoriaRepository.findById(request.getCategoriaId())
                    .orElseThrow(() -> new ResourceNotFoundException("Categoría", request.getCategoriaId()));
            libro.setCategoria(categoria);
        }

        if (request.getEditorialId() != null) {
            Editorial editorial = editorialRepository.findById(request.getEditorialId())
                    .orElseThrow(() -> new ResourceNotFoundException("Editorial", request.getEditorialId()));
            libro.setEditorial(editorial);
        }

        if (request.getAutorIds() != null && !request.getAutorIds().isEmpty()) {
            Set<Autor> autores = request.getAutorIds().stream()
                    .map(id -> autorRepository.findById(id)
                            .orElseThrow(() -> new ResourceNotFoundException("Autor", id)))
                    .collect(Collectors.toSet());
            libro.setAutores(autores);
        }

        libro = libroRepository.save(libro);
        return toResponse(libro);
    }

    @Transactional
    public LibroResponse actualizar(Long id, LibroRequest request) {
        Libro libro = libroRepository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Libro", id));

        libro.setTitulo(request.getTitulo());
        libro.setIsbn(request.getIsbn());
        libro.setAnioPublicacion(request.getAnioPublicacion());
        libro.setEdicion(request.getEdicion());

        if (request.getEjemplaresTotales() != null) {
            int diff = request.getEjemplaresTotales() - libro.getEjemplaresTotales();
            libro.setEjemplaresTotales(request.getEjemplaresTotales());
            libro.setEjemplaresDisponibles(libro.getEjemplaresDisponibles() + diff);
        }

        libro.setUbicacion(request.getUbicacion());
        libro.setDescripcion(request.getDescripcion());

        if (request.getCategoriaId() != null) {
            Categoria categoria = categoriaRepository.findById(request.getCategoriaId())
                    .orElseThrow(() -> new ResourceNotFoundException("Categoría", request.getCategoriaId()));
            libro.setCategoria(categoria);
        }

        if (request.getEditorialId() != null) {
            Editorial editorial = editorialRepository.findById(request.getEditorialId())
                    .orElseThrow(() -> new ResourceNotFoundException("Editorial", request.getEditorialId()));
            libro.setEditorial(editorial);
        }

        if (request.getAutorIds() != null) {
            Set<Autor> autores = request.getAutorIds().stream()
                    .map(aid -> autorRepository.findById(aid)
                            .orElseThrow(() -> new ResourceNotFoundException("Autor", aid)))
                    .collect(Collectors.toSet());
            libro.setAutores(autores);
        }

        libro = libroRepository.save(libro);
        return toResponse(libro);
    }

    @Transactional
    public void eliminar(Long id) {
        Libro libro = libroRepository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Libro", id));
        libro.setActivo(false);
        libroRepository.save(libro);
    }

    private LibroResponse toResponse(Libro libro) {
        return LibroResponse.builder()
                .id(libro.getId())
                .titulo(libro.getTitulo())
                .isbn(libro.getIsbn())
                .anioPublicacion(libro.getAnioPublicacion())
                .edicion(libro.getEdicion())
                .ejemplaresTotales(libro.getEjemplaresTotales())
                .ejemplaresDisponibles(libro.getEjemplaresDisponibles())
                .ubicacion(libro.getUbicacion())
                .descripcion(libro.getDescripcion())
                .categoria(libro.getCategoria() != null ? libro.getCategoria().getNombre() : null)
                .categoriaId(libro.getCategoria() != null ? libro.getCategoria().getId() : null)
                .editorial(libro.getEditorial() != null ? libro.getEditorial().getNombre() : null)
                .editorialId(libro.getEditorial() != null ? libro.getEditorial().getId() : null)
                .autores(libro.getAutores().stream().map(Autor::getNombreCompleto).collect(Collectors.toSet()))
                .activo(libro.getActivo())
                .build();
    }
}
