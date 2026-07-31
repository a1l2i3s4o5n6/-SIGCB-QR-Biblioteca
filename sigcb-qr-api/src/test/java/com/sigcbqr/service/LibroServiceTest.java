package com.sigcbqr.service;

import com.sigcbqr.exception.ResourceNotFoundException;
import com.sigcbqr.model.dto.request.LibroRequest;
import com.sigcbqr.model.dto.response.LibroResponse;
import com.sigcbqr.model.entity.*;
import com.sigcbqr.repository.*;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.extension.ExtendWith;
import org.mockito.Mock;
import org.mockito.junit.jupiter.MockitoExtension;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.PageImpl;
import org.springframework.data.domain.PageRequest;

import java.util.List;
import java.util.Optional;
import java.util.Set;

import static org.junit.jupiter.api.Assertions.*;
import static org.mockito.ArgumentMatchers.any;
import static org.mockito.Mockito.*;

@ExtendWith(MockitoExtension.class)
class LibroServiceTest {

    @Mock
    private LibroRepository libroRepository;
    @Mock
    private CategoriaRepository categoriaRepository;
    @Mock
    private EditorialRepository editorialRepository;
    @Mock
    private AutorRepository autorRepository;

    private LibroService libroService;

    @BeforeEach
    void setUp() {
        libroService = new LibroService(libroRepository, categoriaRepository, editorialRepository, autorRepository);
    }

    @Test
    void listarLibrosActivos() {
        Libro libro = new Libro();
        libro.setId(1L);
        libro.setTitulo("Test Book");
        libro.setActivo(true);

        Page<Libro> page = new PageImpl<>(List.of(libro));
        when(libroRepository.findByActivoTrue(any(PageRequest.class))).thenReturn(page);

        Page<LibroResponse> result = libroService.listar(PageRequest.of(0, 10));

        assertFalse(result.isEmpty());
        assertEquals("Test Book", result.getContent().get(0).getTitulo());
    }

    @Test
    void obtenerLibroExistente() {
        Libro libro = new Libro();
        libro.setId(1L);
        libro.setTitulo("Test Book");
        when(libroRepository.findById(1L)).thenReturn(Optional.of(libro));

        LibroResponse result = libroService.obtener(1L);
        assertNotNull(result);
        assertEquals("Test Book", result.getTitulo());
    }

    @Test
    void obtenerLibroInexistenteLanzaExcepcion() {
        when(libroRepository.findById(99L)).thenReturn(Optional.empty());
        assertThrows(ResourceNotFoundException.class, () -> libroService.obtener(99L));
    }

    @Test
    void crearLibroConDatosValidos() {
        Categoria categoria = new Categoria();
        categoria.setId(1L);
        categoria.setNombre("Test Cat");

        Editorial editorial = new Editorial();
        editorial.setId(1L);
        editorial.setNombre("Test Ed");

        Autor autor = new Autor();
        autor.setId(1L);
        autor.setNombreCompleto("Test Autor");

        LibroRequest request = new LibroRequest();
        request.setTitulo("Nuevo Libro");
        request.setIsbn("1234567890");
        request.setEjemplaresTotales(5);
        request.setCategoriaId(1L);
        request.setEditorialId(1L);
        request.setAutorIds(Set.of(1L));

        when(categoriaRepository.findById(1L)).thenReturn(Optional.of(categoria));
        when(editorialRepository.findById(1L)).thenReturn(Optional.of(editorial));
        when(autorRepository.findById(1L)).thenReturn(Optional.of(autor));

        Libro savedLibro = new Libro();
        savedLibro.setId(1L);
        savedLibro.setTitulo("Nuevo Libro");
        savedLibro.setIsbn("1234567890");
        savedLibro.setEjemplaresTotales(5);
        savedLibro.setEjemplaresDisponibles(5);
        savedLibro.setCategoria(categoria);
        savedLibro.setEditorial(editorial);
        savedLibro.setAutores(Set.of(autor));
        savedLibro.setActivo(true);

        when(libroRepository.save(any(Libro.class))).thenReturn(savedLibro);

        LibroResponse result = libroService.crear(request);
        assertNotNull(result);
        assertEquals("Nuevo Libro", result.getTitulo());
    }

    @Test
    void eliminarLibroRealizaSoftDelete() {
        Libro libro = new Libro();
        libro.setId(1L);
        libro.setActivo(true);
        when(libroRepository.findById(1L)).thenReturn(Optional.of(libro));

        libroService.eliminar(1L);
        assertFalse(libro.getActivo());
        verify(libroRepository).save(libro);
    }
}
