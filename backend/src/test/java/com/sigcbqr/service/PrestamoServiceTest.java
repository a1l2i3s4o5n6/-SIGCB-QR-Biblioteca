package com.sigcbqr.service;

import com.sigcbqr.exception.BadRequestException;
import com.sigcbqr.exception.ResourceNotFoundException;
import com.sigcbqr.model.dto.request.PrestamoRequest;
import com.sigcbqr.model.entity.*;
import com.sigcbqr.repository.*;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.extension.ExtendWith;
import org.mockito.Mock;
import org.mockito.junit.jupiter.MockitoExtension;

import java.util.Optional;

import static org.junit.jupiter.api.Assertions.*;
import static org.mockito.ArgumentMatchers.*;
import static org.mockito.Mockito.*;

@ExtendWith(MockitoExtension.class)
class PrestamoServiceTest {

    @Mock
    private PrestamoRepository prestamoRepository;
    @Mock
    private UsuarioRepository usuarioRepository;
    @Mock
    private InventarioRepository inventarioRepository;
    @Mock
    private LibroRepository libroRepository;
    @Mock
    private SancionRepository sancionRepository;
    @Mock
    private AuditoriaService auditoriaService;
    @Mock
    private QrCodigoRepository qrCodigoRepository;

    private PrestamoService prestamoService;

    @BeforeEach
    void setUp() {
        prestamoService = new PrestamoService(prestamoRepository, usuarioRepository, inventarioRepository, libroRepository, sancionRepository, auditoriaService, qrCodigoRepository);
    }

    @Test
    void crearPrestamoConDatosValidos() {
        Usuario usuario = new Usuario();
        usuario.setId(1L);

        Inventario inventario = new Inventario();
        inventario.setId(1L);
        inventario.setEstado("DISPONIBLE");

        Libro libro = new Libro();
        libro.setId(1L);
        libro.setEjemplaresDisponibles(5);
        inventario.setLibro(libro);

        PrestamoRequest request = new PrestamoRequest();
        request.setUsuarioId(1L);
        request.setInventarioId(1L);

        when(usuarioRepository.findById(1L)).thenReturn(Optional.of(usuario));
        when(inventarioRepository.findById(1L)).thenReturn(Optional.of(inventario));
        when(prestamoRepository.countByUsuarioIdAndEstado(1L, "ACTIVO")).thenReturn(0L);
        when(prestamoRepository.save(any(Prestamo.class))).thenAnswer(i -> i.getArgument(0));

        var response = prestamoService.crear(request);
        assertNotNull(response);
        verify(prestamoRepository).save(any(Prestamo.class));
    }

    @Test
    void crearPrestamoConSancionActivaLanzaExcepcion() {
        Usuario usuario = new Usuario();
        usuario.setId(1L);

        Inventario inventario = new Inventario();
        inventario.setId(1L);
        inventario.setEstado("DISPONIBLE");

        PrestamoRequest request = new PrestamoRequest();
        request.setUsuarioId(1L);
        request.setInventarioId(1L);

        when(usuarioRepository.findById(1L)).thenReturn(Optional.of(usuario));
        when(inventarioRepository.findById(1L)).thenReturn(Optional.of(inventario));
        when(prestamoRepository.countByUsuarioIdAndEstado(1L, "ACTIVO")).thenReturn(0L);
        when(sancionRepository.existsByUsuarioIdAndActivaTrue(1L)).thenReturn(true);

        assertThrows(BadRequestException.class, () -> prestamoService.crear(request));
        verify(prestamoRepository, never()).save(any(Prestamo.class));
    }

    @Test
    void crearPrestamoConInventarioNoDisponibleLanzaExcepcion() {
        Usuario usuario = new Usuario();
        usuario.setId(1L);

        Inventario inventario = new Inventario();
        inventario.setId(1L);
        inventario.setEstado("PRESTADO");

        PrestamoRequest request = new PrestamoRequest();
        request.setUsuarioId(1L);
        request.setInventarioId(1L);

        when(usuarioRepository.findById(1L)).thenReturn(Optional.of(usuario));
        when(inventarioRepository.findById(1L)).thenReturn(Optional.of(inventario));

        assertThrows(BadRequestException.class, () -> prestamoService.crear(request));
    }

    @Test
    void devolverPrestamoConExito() {
        Usuario usuario = new Usuario();
        usuario.setId(1L);
        usuario.setNombre("Test User");

        Prestamo prestamo = new Prestamo();
        prestamo.setId(1L);
        prestamo.setEstado("ACTIVO");
        prestamo.setUsuario(usuario);
        prestamo.setFechaVencimiento(java.time.LocalDateTime.now().plusDays(7));

        Inventario inventario = new Inventario();
        inventario.setId(1L);
        inventario.setEstado("PRESTADO");
        inventario.setCodigoEjemplar("E-001");

        Libro libro = new Libro();
        libro.setId(1L);
        libro.setTitulo("Test Book");
        libro.setEjemplaresDisponibles(3);
        inventario.setLibro(libro);
        prestamo.setInventario(inventario);

        when(prestamoRepository.findById(1L)).thenReturn(Optional.of(prestamo));
        when(prestamoRepository.save(any(Prestamo.class))).thenAnswer(i -> i.getArgument(0));

        var response = prestamoService.devolver(1L);
        assertNotNull(response);
        verify(inventarioRepository).save(any(Inventario.class));
        verify(libroRepository).save(any(Libro.class));
        verify(prestamoRepository).save(any(Prestamo.class));
    }

    @Test
    void crearPrestamoConCodigoQrValido() {
        Usuario usuario = new Usuario();
        usuario.setId(1L);

        Libro libro = new Libro();
        libro.setId(1L);
        libro.setEjemplaresDisponibles(5);

        Inventario inventario = new Inventario();
        inventario.setId(1L);
        inventario.setEstado("DISPONIBLE");
        inventario.setLibro(libro);

        QrCodigo qr = QrCodigo.builder()
                .id(1L)
                .libro(libro)
                .codigo("QR-978-0132350884")
                .activo(true)
                .build();

        PrestamoRequest request = new PrestamoRequest();
        request.setUsuarioId(1L);
        request.setInventarioId(1L);
        request.setCodigoQr("QR-978-0132350884");

        when(usuarioRepository.findById(1L)).thenReturn(Optional.of(usuario));
        when(inventarioRepository.findById(1L)).thenReturn(Optional.of(inventario));
        when(qrCodigoRepository.findByCodigo("QR-978-0132350884")).thenReturn(Optional.of(qr));
        when(prestamoRepository.countByUsuarioIdAndEstado(1L, "ACTIVO")).thenReturn(0L);
        when(prestamoRepository.save(any(Prestamo.class))).thenAnswer(i -> i.getArgument(0));

        var response = prestamoService.crear(request);
        assertNotNull(response);
        verify(prestamoRepository).save(any(Prestamo.class));
    }

    @Test
    void crearPrestamoConCodigoQrInexistenteLanzaExcepcion() {
        Usuario usuario = new Usuario();
        usuario.setId(1L);

        Libro libro = new Libro();
        libro.setId(1L);

        Inventario inventario = new Inventario();
        inventario.setId(1L);
        inventario.setEstado("DISPONIBLE");
        inventario.setLibro(libro);

        PrestamoRequest request = new PrestamoRequest();
        request.setUsuarioId(1L);
        request.setInventarioId(1L);
        request.setCodigoQr("QR-INEXISTENTE");

        when(usuarioRepository.findById(1L)).thenReturn(Optional.of(usuario));
        when(inventarioRepository.findById(1L)).thenReturn(Optional.of(inventario));
        when(qrCodigoRepository.findByCodigo("QR-INEXISTENTE")).thenReturn(Optional.empty());

        assertThrows(BadRequestException.class, () -> prestamoService.crear(request));
    }

    @Test
    void crearPrestamoConCodigoQrDesactivadoLanzaExcepcion() {
        Usuario usuario = new Usuario();
        usuario.setId(1L);

        Libro libro = new Libro();
        libro.setId(1L);

        Inventario inventario = new Inventario();
        inventario.setId(1L);
        inventario.setEstado("DISPONIBLE");
        inventario.setLibro(libro);

        QrCodigo qr = QrCodigo.builder()
                .id(1L)
                .libro(libro)
                .codigo("QR-DESACTIVADO")
                .activo(false)
                .build();

        PrestamoRequest request = new PrestamoRequest();
        request.setUsuarioId(1L);
        request.setInventarioId(1L);
        request.setCodigoQr("QR-DESACTIVADO");

        when(usuarioRepository.findById(1L)).thenReturn(Optional.of(usuario));
        when(inventarioRepository.findById(1L)).thenReturn(Optional.of(inventario));
        when(qrCodigoRepository.findByCodigo("QR-DESACTIVADO")).thenReturn(Optional.of(qr));

        assertThrows(BadRequestException.class, () -> prestamoService.crear(request));
    }

    @Test
    void crearPrestamoConCodigoQrDeOtroLibroLanzaExcepcion() {
        Usuario usuario = new Usuario();
        usuario.setId(1L);

        Libro libroPrestado = new Libro();
        libroPrestado.setId(1L);

        Inventario inventario = new Inventario();
        inventario.setId(1L);
        inventario.setEstado("DISPONIBLE");
        inventario.setLibro(libroPrestado);

        Libro libroDelQr = new Libro();
        libroDelQr.setId(2L);

        QrCodigo qr = QrCodigo.builder()
                .id(1L)
                .libro(libroDelQr)
                .codigo("QR-OTRO-LIBRO")
                .activo(true)
                .build();

        PrestamoRequest request = new PrestamoRequest();
        request.setUsuarioId(1L);
        request.setInventarioId(1L);
        request.setCodigoQr("QR-OTRO-LIBRO");

        when(usuarioRepository.findById(1L)).thenReturn(Optional.of(usuario));
        when(inventarioRepository.findById(1L)).thenReturn(Optional.of(inventario));
        when(qrCodigoRepository.findByCodigo("QR-OTRO-LIBRO")).thenReturn(Optional.of(qr));

        assertThrows(BadRequestException.class, () -> prestamoService.crear(request));
    }
}
