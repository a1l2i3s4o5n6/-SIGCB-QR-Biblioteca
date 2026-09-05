package com.sigcbqr.service;

import com.sigcbqr.exception.BadRequestException;
import com.sigcbqr.exception.ResourceNotFoundException;
import com.sigcbqr.model.dto.request.QrCodigoRequest;
import com.sigcbqr.model.dto.response.QrCodigoResponse;
import com.sigcbqr.model.entity.Libro;
import com.sigcbqr.model.entity.QrCodigo;
import com.sigcbqr.repository.LibroRepository;
import com.sigcbqr.repository.QrCodigoRepository;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.extension.ExtendWith;
import org.mockito.Mock;
import org.mockito.junit.jupiter.MockitoExtension;

import java.time.LocalDateTime;
import java.util.List;
import java.util.Optional;

import static org.junit.jupiter.api.Assertions.assertEquals;
import static org.junit.jupiter.api.Assertions.assertFalse;
import static org.junit.jupiter.api.Assertions.assertThrows;
import static org.junit.jupiter.api.Assertions.assertTrue;
import static org.mockito.ArgumentMatchers.any;
import static org.mockito.ArgumentMatchers.anyLong;
import static org.mockito.ArgumentMatchers.anyString;
import static org.mockito.ArgumentMatchers.eq;
import static org.mockito.Mockito.never;
import static org.mockito.Mockito.verify;
import static org.mockito.Mockito.when;

@ExtendWith(MockitoExtension.class)
class QrCodigoServiceTest {

    @Mock
    private QrCodigoRepository qrCodigoRepository;
    @Mock
    private LibroRepository libroRepository;
    @Mock
    private AuditoriaService auditoriaService;

    private QrCodigoService qrCodigoService;

    @BeforeEach
    void setUp() {
        qrCodigoService = new QrCodigoService(qrCodigoRepository, libroRepository, auditoriaService);
    }

    private Libro libro(Long id) {
        return Libro.builder().id(id).titulo("Libro A").build();
    }

    private QrCodigo qr(Long id, String codigo, boolean activo) {
        return QrCodigo.builder().id(id).libro(libro(1L)).codigo(codigo).imagenUrl("img.png")
                .activo(activo).createdAt(LocalDateTime.of(2026, 9, 1, 10, 0)).build();
    }

    @Test
    void listarDevuelveTodosLosCodigos() {
        when(qrCodigoRepository.findAll()).thenReturn(List.of(qr(1L, "QR-1", true), qr(2L, "QR-2", true)));

        List<QrCodigoResponse> resultados = qrCodigoService.listar();

        assertEquals(2, resultados.size());
        assertEquals("QR-1", resultados.get(0).getCodigo());
        assertEquals("Libro A", resultados.get(0).getLibroTitulo());
    }

    @Test
    void obtenerPorLibroFiltraPorLibroId() {
        when(qrCodigoRepository.findByLibroId(1L)).thenReturn(List.of(qr(1L, "QR-1", true)));

        List<QrCodigoResponse> resultados = qrCodigoService.obtenerPorLibro(1L);

        assertEquals(1, resultados.size());
        assertEquals(1L, resultados.get(0).getLibroId());
    }

    @Test
    void crearConCodigoExplicito() {
        when(libroRepository.findById(1L)).thenReturn(Optional.of(libro(1L)));
        when(qrCodigoRepository.existsByCodigo("QR-1")).thenReturn(false);
        when(qrCodigoRepository.save(any(QrCodigo.class))).thenReturn(qr(1L, "QR-1", true));

        QrCodigoRequest request = QrCodigoRequest.builder().libroId(1L).codigo("QR-1").build();

        QrCodigoResponse response = qrCodigoService.crear(request);

        assertEquals("QR-1", response.getCodigo());
        assertTrue(response.getActivo());
        verify(qrCodigoRepository).save(any(QrCodigo.class));
        verify(auditoriaService).registrar(eq("CREAR"), eq("C\u00d3DIGO QR"), eq(1L), anyString());
    }

    @Test
    void crearGeneraCodigoUnicoCuandoNoSeIndica() {
        when(libroRepository.findById(1L)).thenReturn(Optional.of(libro(1L)));
        when(qrCodigoRepository.existsByCodigo(anyString())).thenReturn(false);
        when(qrCodigoRepository.save(any(QrCodigo.class))).thenAnswer(inv -> inv.getArgument(0));

        QrCodigoResponse response = qrCodigoService.crear(QrCodigoRequest.builder().libroId(1L).build());

        assertTrue(response.getCodigo().startsWith("QR-LIB-1-"));
        verify(qrCodigoRepository).save(any(QrCodigo.class));
    }

    @Test
    void crearRechazaLibroInexistente() {
        when(libroRepository.findById(99L)).thenReturn(Optional.empty());

        assertThrows(ResourceNotFoundException.class,
                () -> qrCodigoService.crear(QrCodigoRequest.builder().libroId(99L).build()));
        verify(qrCodigoRepository, never()).save(any(QrCodigo.class));
    }

    @Test
    void crearRechazaCodigoDuplicado() {
        when(libroRepository.findById(1L)).thenReturn(Optional.of(libro(1L)));
        when(qrCodigoRepository.existsByCodigo("QR-1")).thenReturn(true);

        assertThrows(BadRequestException.class,
                () -> qrCodigoService.crear(QrCodigoRequest.builder().libroId(1L).codigo("QR-1").build()));
        verify(qrCodigoRepository, never()).save(any(QrCodigo.class));
    }

    @Test
    void regenerarAsignaCodigoNuevo() {
        when(qrCodigoRepository.findById(1L)).thenReturn(Optional.of(qr(1L, "QR-0", true)));
        when(qrCodigoRepository.existsByCodigo(anyString())).thenReturn(false);
        when(qrCodigoRepository.save(any(QrCodigo.class))).thenAnswer(inv -> inv.getArgument(0));

        QrCodigoResponse response = qrCodigoService.regenerar(1L);

        assertTrue(response.getCodigo().startsWith("QR-LIB-1-"));
        verify(qrCodigoRepository).save(any(QrCodigo.class));
        verify(auditoriaService).registrar(eq("REGENERAR"), eq("C\u00d3DIGO QR"), eq(1L), anyString());
    }

    @Test
    void regenerarRechazaCodigoInexistente() {
        when(qrCodigoRepository.findById(50L)).thenReturn(Optional.empty());

        assertThrows(ResourceNotFoundException.class, () -> qrCodigoService.regenerar(50L));
    }

    @Test
    void cambiarEstadoActivaUnCodigoInactivo() {
        when(qrCodigoRepository.findById(1L)).thenReturn(Optional.of(qr(1L, "QR-1", false)));
        when(qrCodigoRepository.save(any(QrCodigo.class))).thenAnswer(inv -> inv.getArgument(0));

        QrCodigoResponse response = qrCodigoService.cambiarEstado(1L, true);

        assertTrue(response.getActivo());
        verify(auditoriaService).registrar(eq("ACTIVAR"), eq("C\u00d3DIGO QR"), eq(1L), anyString());
    }

    @Test
    void cambiarEstadoDesactivaUnCodigoActivo() {
        when(qrCodigoRepository.findById(1L)).thenReturn(Optional.of(qr(1L, "QR-1", true)));
        when(qrCodigoRepository.save(any(QrCodigo.class))).thenAnswer(inv -> inv.getArgument(0));

        QrCodigoResponse response = qrCodigoService.cambiarEstado(1L, false);

        assertFalse(response.getActivo());
        verify(auditoriaService).registrar(eq("DESACTIVAR"), eq("C\u00d3DIGO QR"), eq(1L), anyString());
    }

    @Test
    void validarCodigoActivoDevuelveElCodigo() {
        when(qrCodigoRepository.findByCodigo("QR-VALIDO")).thenReturn(Optional.of(qr(1L, "QR-VALIDO", true)));

        QrCodigoResponse response = qrCodigoService.validarCodigo("QR-VALIDO");

        assertEquals("QR-VALIDO", response.getCodigo());
        verify(auditoriaService).registrar(eq("VALIDAR"), eq("C\u00d3DIGO QR"), eq(1L), anyString());
    }

    @Test
    void validarCodigoRechazaCodigoInactivo() {
        when(qrCodigoRepository.findByCodigo("QR-INACTIVO")).thenReturn(Optional.of(qr(1L, "QR-INACTIVO", false)));

        assertThrows(BadRequestException.class, () -> qrCodigoService.validarCodigo("QR-INACTIVO"));
    }

    @Test
    void validarCodigoRechazaCodigoNoRegistrado() {
        when(qrCodigoRepository.findByCodigo("QR-NADA")).thenReturn(Optional.empty());

        assertThrows(ResourceNotFoundException.class, () -> qrCodigoService.validarCodigo("QR-NADA"));
    }
}