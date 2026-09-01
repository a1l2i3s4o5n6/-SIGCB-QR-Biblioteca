package com.sigcbqr.service;

import com.sigcbqr.exception.BadRequestException;
import com.sigcbqr.model.dto.request.NotificacionRequest;
import com.sigcbqr.model.dto.request.SancionRequest;
import com.sigcbqr.model.dto.response.SancionResponse;
import com.sigcbqr.model.entity.Sancion;
import com.sigcbqr.model.entity.Usuario;
import com.sigcbqr.repository.SancionRepository;
import com.sigcbqr.repository.UsuarioRepository;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.extension.ExtendWith;
import org.mockito.Mock;
import org.mockito.junit.jupiter.MockitoExtension;

import java.time.LocalDateTime;
import java.util.Optional;

import static org.junit.jupiter.api.Assertions.assertEquals;
import static org.junit.jupiter.api.Assertions.assertFalse;
import static org.junit.jupiter.api.Assertions.assertThrows;
import static org.junit.jupiter.api.Assertions.assertTrue;
import static org.mockito.ArgumentMatchers.any;
import static org.mockito.ArgumentMatchers.anyString;
import static org.mockito.ArgumentMatchers.eq;
import static org.mockito.Mockito.never;
import static org.mockito.Mockito.verify;
import static org.mockito.Mockito.when;

@ExtendWith(MockitoExtension.class)
class SancionServiceTest {

    @Mock
    private SancionRepository sancionRepository;
    @Mock
    private UsuarioRepository usuarioRepository;
    @Mock
    private NotificacionService notificacionService;
    @Mock
    private AuditoriaService auditoriaService;

    private SancionService sancionService;

    @BeforeEach
    void setUp() {
        sancionService = new SancionService(sancionRepository, usuarioRepository, notificacionService, auditoriaService);
    }

    private Usuario usuario(Long id) {
        Usuario u = new Usuario();
        u.setId(id);
        u.setNombre("Carlos");
        u.setEmail("carlos@test.com");
        return u;
    }

    @Test
    void crearAplicaSancionYNotifica() {
        Usuario usuario = usuario(2L);
        Sancion guardada = new Sancion();
        guardada.setId(1L);
        guardada.setUsuario(usuario);
        guardada.setTipo("ADVERTENCIA");
        guardada.setMotivo("Retraso");
        guardada.setFechaInicio(LocalDateTime.now());
        guardada.setActiva(true);
        guardada.setCreatedAt(LocalDateTime.now());

        when(usuarioRepository.findById(2L)).thenReturn(Optional.of(usuario));
        when(sancionRepository.existsByUsuarioIdAndActivaTrue(2L)).thenReturn(false);
        when(sancionRepository.save(any(Sancion.class))).thenReturn(guardada);

        SancionRequest request = SancionRequest.builder()
                .usuarioId(2L)
                .tipo("advertencia")
                .motivo("Retraso")
                .fechaInicio(LocalDateTime.now())
                .build();

        SancionResponse response = sancionService.crear(request);

        assertEquals("ADVERTENCIA", response.getTipo());
        assertTrue(response.getActiva());
        verify(sancionRepository).save(any(Sancion.class));
        verify(notificacionService).crear(any(NotificacionRequest.class));
        verify(auditoriaService).registrar(eq("CREAR"), eq("SANCION"), eq(1L), anyString());
    }

    @Test
    void crearRechazaTipoInvalido() {
        when(usuarioRepository.findById(2L)).thenReturn(Optional.of(usuario(2L)));

        SancionRequest request = SancionRequest.builder()
                .usuarioId(2L)
                .tipo("EXPULSION")
                .fechaInicio(LocalDateTime.now())
                .build();

        assertThrows(BadRequestException.class, () -> sancionService.crear(request));
        verify(sancionRepository, never()).save(any(Sancion.class));
    }

    @Test
    void crearRechazaCuandoYaHaySancionActiva() {
        when(usuarioRepository.findById(2L)).thenReturn(Optional.of(usuario(2L)));
        when(sancionRepository.existsByUsuarioIdAndActivaTrue(2L)).thenReturn(true);

        SancionRequest request = SancionRequest.builder()
                .usuarioId(2L)
                .tipo("SUSPENSION")
                .fechaInicio(LocalDateTime.now())
                .build();

        assertThrows(BadRequestException.class, () -> sancionService.crear(request));
    }

    @Test
    void levantarDesactivaYNotifica() {
        Usuario usuario = usuario(2L);
        Sancion sancion = new Sancion();
        sancion.setId(7L);
        sancion.setUsuario(usuario);
        sancion.setTipo("SUSPENSION");
        sancion.setActiva(true);

        when(sancionRepository.findById(7L)).thenReturn(Optional.of(sancion));
        when(sancionRepository.save(any(Sancion.class))).thenReturn(sancion);

        SancionResponse response = sancionService.levantar(7L);

        assertFalse(response.getActiva());
        verify(notificacionService).crear(any(NotificacionRequest.class));
        verify(auditoriaService).registrar(eq("LEVANTAR"), eq("SANCION"), eq(7L), anyString());
    }
}