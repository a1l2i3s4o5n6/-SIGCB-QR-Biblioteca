package com.sigcbqr.service;

import com.sigcbqr.model.entity.Inventario;
import com.sigcbqr.model.entity.Libro;
import com.sigcbqr.model.entity.Notificacion;
import com.sigcbqr.model.entity.Prestamo;
import com.sigcbqr.model.entity.Sancion;
import com.sigcbqr.model.entity.Usuario;
import com.sigcbqr.repository.NotificacionRepository;
import com.sigcbqr.repository.PrestamoRepository;
import com.sigcbqr.repository.SancionRepository;
import com.sigcbqr.repository.UsuarioRepository;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.extension.ExtendWith;
import org.mockito.Mock;
import org.mockito.junit.jupiter.MockitoExtension;

import java.time.LocalDateTime;
import java.util.List;

import static org.mockito.ArgumentMatchers.any;
import static org.mockito.ArgumentMatchers.anyLong;
import static org.mockito.ArgumentMatchers.anyString;
import static org.mockito.ArgumentMatchers.argThat;
import static org.mockito.ArgumentMatchers.eq;
import static org.mockito.Mockito.never;
import static org.mockito.Mockito.times;
import static org.mockito.Mockito.verify;
import static org.mockito.Mockito.when;

@ExtendWith(MockitoExtension.class)
class NotificacionProgramadaServiceTest {

    @Mock
    private PrestamoRepository prestamoRepository;
    @Mock
    private SancionRepository sancionRepository;
    @Mock
    private UsuarioRepository usuarioRepository;
    @Mock
    private NotificacionRepository notificacionRepository;

    private NotificacionProgramadaService servicio;

    @BeforeEach
    void setUp() {
        servicio = new NotificacionProgramadaService(prestamoRepository, sancionRepository,
                usuarioRepository, notificacionRepository);
    }

    private Usuario usuario(Long id) {
        return Usuario.builder().id(id).nombre("Usuario " + id).email("u" + id + "@test.com").build();
    }

    private Prestamo prestamo(Usuario usuario, LocalDateTime vencimiento, String estado) {
        return Prestamo.builder()
                .id(usuario.getId())
                .usuario(usuario)
                .inventario(Inventario.builder().libro(Libro.builder().titulo("Libro X").build()).build())
                .fechaVencimiento(vencimiento)
                .estado(estado)
                .build();
    }

    private Sancion sancion(Usuario usuario, String tipo, LocalDateTime fechaFin) {
        return Sancion.builder().id(usuario.getId()).usuario(usuario).tipo(tipo).fechaInicio(LocalDateTime.now())
                .fechaFin(fechaFin).activa(true).build();
    }

    @Test
    void marcaPrestamoVencidoYNotificaAlUsuario() {
        Usuario usuario = usuario(1L);
        Prestamo vencido = prestamo(usuario, LocalDateTime.now().minusHours(3), "ACTIVO");

        when(prestamoRepository.findByEstadoAndFechaVencimientoBefore(eq("ACTIVO"), any(LocalDateTime.class)))
                .thenReturn(List.of(vencido));
        when(prestamoRepository.findByEstadoAndFechaVencimientoBetween(eq("ACTIVO"),
                any(LocalDateTime.class), any(LocalDateTime.class))).thenReturn(List.of());
        when(sancionRepository.findByActivaTrue()).thenReturn(List.of());
        when(notificacionRepository.countByUsuarioIdAndTituloContainingAndCreatedAtAfter(
                anyLong(), anyString(), any(LocalDateTime.class))).thenReturn(0L);

        servicio.procesarVencimientos();

        verify(prestamoRepository).save(argThat(p -> "VENCIDO".equals(p.getEstado())));
        verify(notificacionRepository).save(argThat(n -> "Pr\u00e9stamo vencido".equals(n.getTitulo())));
    }

    @Test
    void dentroDeLaVentanaDeDeduplicacionNoReplicaLaNotificacion() {
        Usuario usuario = usuario(1L);
        Prestamo vencido = prestamo(usuario, LocalDateTime.now().minusHours(3), "ACTIVO");

        when(prestamoRepository.findByEstadoAndFechaVencimientoBefore(eq("ACTIVO"), any(LocalDateTime.class)))
                .thenReturn(List.of(vencido));
        when(prestamoRepository.findByEstadoAndFechaVencimientoBetween(eq("ACTIVO"),
                any(LocalDateTime.class), any(LocalDateTime.class))).thenReturn(List.of());
        when(sancionRepository.findByActivaTrue()).thenReturn(List.of());
        when(notificacionRepository.countByUsuarioIdAndTituloContainingAndCreatedAtAfter(
                anyLong(), anyString(), any(LocalDateTime.class))).thenReturn(1L);

        servicio.procesarVencimientos();

        verify(prestamoRepository).save(any(Prestamo.class));
        verify(notificacionRepository, never()).save(any(Notificacion.class));
    }

    @Test
    void notificaVencimientoProximoSancionVencidaAlStaffYSancionPorCaducarAlUsuario() {
        Usuario estudiante = usuario(1L);
        Prestamo proximo = prestamo(estudiante, LocalDateTime.now().plusHours(12), "ACTIVO");
        Usuario admin = usuario(5L);
        Usuario biblio = usuario(6L);
        Usuario enSancion = usuario(7L);
        Usuario porCaducar = usuario(8L);

        when(prestamoRepository.findByEstadoAndFechaVencimientoBefore(eq("ACTIVO"), any(LocalDateTime.class)))
                .thenReturn(List.of());
        when(prestamoRepository.findByEstadoAndFechaVencimientoBetween(eq("ACTIVO"),
                any(LocalDateTime.class), any(LocalDateTime.class))).thenReturn(List.of(proximo));
        when(sancionRepository.findByActivaTrue()).thenReturn(List.of(
                sancion(enSancion, "SUSPENSION", LocalDateTime.now().minusDays(1)),
                sancion(porCaducar, "ADVERTENCIA", LocalDateTime.now().plusDays(2)),
                sancion(enSancion, "ADVERTENCIA", null),
                sancion(enSancion, "SUSPENSION", LocalDateTime.now().plusDays(30))));
        when(usuarioRepository.findByRol_Nombre("ADMIN")).thenReturn(List.of(admin));
        when(usuarioRepository.findByRol_Nombre("BIBLIOTECARIO")).thenReturn(List.of(biblio));
        when(notificacionRepository.countByUsuarioIdAndTituloContainingAndCreatedAtAfter(
                anyLong(), anyString(), any(LocalDateTime.class))).thenReturn(0L);

        servicio.procesarVencimientos();

        verify(notificacionRepository, times(4)).save(any(Notificacion.class));
        verify(notificacionRepository).save(argThat(n -> "Tu pr\u00e9stamo vence pronto".equals(n.getTitulo())));
        verify(notificacionRepository, times(2)).save(argThat(n -> "Sanci\u00f3n vencida por revisar".equals(n.getTitulo())));
        verify(notificacionRepository).save(argThat(n -> "Tu sanci\u00f3n termina pronto".equals(n.getTitulo())));
        verify(sancionRepository).findByActivaTrue();
    }

    @Test
    void sinPrestamosNiSancionesNoCreaNotificacion() {
        when(prestamoRepository.findByEstadoAndFechaVencimientoBefore(eq("ACTIVO"), any(LocalDateTime.class)))
                .thenReturn(List.of());
        when(prestamoRepository.findByEstadoAndFechaVencimientoBetween(eq("ACTIVO"),
                any(LocalDateTime.class), any(LocalDateTime.class))).thenReturn(List.of());
        when(sancionRepository.findByActivaTrue()).thenReturn(List.of());

        servicio.procesarVencimientos();

        verify(notificacionRepository, never()).save(any(Notificacion.class));
    }
}