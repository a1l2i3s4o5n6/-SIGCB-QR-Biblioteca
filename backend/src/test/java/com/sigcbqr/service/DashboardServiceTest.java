package com.sigcbqr.service;

import com.sigcbqr.model.dto.response.dashboard.DashboardActividadItemResponse;
import com.sigcbqr.model.dto.response.dashboard.DashboardAlertaResponse;
import com.sigcbqr.model.dto.response.dashboard.DashboardResumenResponse;
import com.sigcbqr.model.entity.Auditoria;
import com.sigcbqr.model.entity.Rol;
import com.sigcbqr.model.entity.Usuario;
import com.sigcbqr.repository.AuditoriaRepository;
import com.sigcbqr.repository.InventarioRepository;
import com.sigcbqr.repository.LibroRepository;
import com.sigcbqr.repository.MultaRepository;
import com.sigcbqr.repository.NotificacionRepository;
import com.sigcbqr.repository.PrestamoRepository;
import com.sigcbqr.repository.QrCodigoRepository;
import com.sigcbqr.repository.ReservaRepository;
import com.sigcbqr.repository.SancionRepository;
import com.sigcbqr.repository.UsuarioRepository;
import com.sigcbqr.security.UserPrincipal;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.extension.ExtendWith;
import org.mockito.Mock;
import org.mockito.junit.jupiter.MockitoExtension;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.PageRequest;

import java.time.LocalDate;
import java.time.LocalDateTime;
import java.util.List;
import java.util.Map;

import static org.junit.jupiter.api.Assertions.assertEquals;
import static org.junit.jupiter.api.Assertions.assertFalse;
import static org.junit.jupiter.api.Assertions.assertNotNull;
import static org.junit.jupiter.api.Assertions.assertTrue;
import static org.mockito.ArgumentMatchers.any;
import static org.mockito.ArgumentMatchers.anyBoolean;
import static org.mockito.ArgumentMatchers.anyLong;
import static org.mockito.ArgumentMatchers.eq;
import static org.mockito.Mockito.when;

@ExtendWith(MockitoExtension.class)
class DashboardServiceTest {

    @Mock
    private PrestamoRepository prestamoRepository;
    @Mock
    private LibroRepository libroRepository;
    @Mock
    private UsuarioRepository usuarioRepository;
    @Mock
    private ReservaRepository reservaRepository;
    @Mock
    private MultaRepository multaRepository;
    @Mock
    private SancionRepository sancionRepository;
    @Mock
    private QrCodigoRepository qrCodigoRepository;
    @Mock
    private InventarioRepository inventarioRepository;
    @Mock
    private AuditoriaRepository auditoriaRepository;
    @Mock
    private NotificacionRepository notificacionRepository;

    private DashboardService dashboardService;

    @BeforeEach
    void setUp() {
        dashboardService = new DashboardService(prestamoRepository, libroRepository, usuarioRepository,
                reservaRepository, multaRepository, sancionRepository, qrCodigoRepository,
                inventarioRepository, auditoriaRepository, notificacionRepository);
    }

    private UserPrincipal principal(Long id, String rol) {
        return new UserPrincipal(id, "usuario@test.com", "hash", rol, true, List.of());
    }

    @Test
    void getStatsAgregaLasSeisMetricas() {
        when(prestamoRepository.countByFechaPrestamoBetween(any(LocalDateTime.class), any(LocalDateTime.class))).thenReturn(7L);
        when(libroRepository.countByEjemplaresDisponiblesGreaterThan(0)).thenReturn(12L);
        when(usuarioRepository.countByActivoTrue()).thenReturn(40L);
        when(reservaRepository.countByEstado("PENDIENTE")).thenReturn(3L);
        when(multaRepository.countByPagadaFalse()).thenReturn(5L);
        when(multaRepository.totalMultasPendientes()).thenReturn(78.50D);

        Map<String, Object> stats = dashboardService.getStats();

        assertEquals(7L, stats.get("librosPrestadosHoy"));
        assertEquals(12L, stats.get("librosDisponibles"));
        assertEquals(40L, stats.get("estudiantesActivos"));
        assertEquals(3L, stats.get("reservasPendientes"));
        assertEquals(5L, stats.get("multasPendientes"));
        assertEquals(78.50D, stats.get("totalMultas"));
    }

    @Test
    void contarPrestamosMultasYReservasDelegandoEnRepository() {
        when(prestamoRepository.countByUsuarioIdAndEstado(9L, "ACTIVO")).thenReturn(2L);
        when(multaRepository.countByUsuarioIdAndPagadaFalse(9L)).thenReturn(1L);
        when(reservaRepository.countByUsuarioIdAndEstado(9L, "PENDIENTE")).thenReturn(0L);

        assertEquals(2L, dashboardService.contarPrestamosActivos(9L));
        assertEquals(1L, dashboardService.contarMultasPendientes(9L));
        assertEquals(0L, dashboardService.contarReservasPendientes(9L));
    }

    @Test
    void resumenDeStaffConKpisAlertasSeriesYPorcentajes() {
        stubEstadoSistema();
        when(notificacionRepository.countByUsuarioIdAndLeidaFalse(1L)).thenReturn(3L);

        when(sancionRepository.findByActivaTrue()).thenReturn(List.of());
        when(libroRepository.countByActivoTrue()).thenReturn(10L);
        when(libroRepository.countByCreatedAtBetween(any(LocalDateTime.class), any(LocalDateTime.class))).thenReturn(2L);
        when(libroRepository.countByEjemplaresDisponiblesGreaterThan(0)).thenReturn(8L);
        when(inventarioRepository.countByEstado("DISPONIBLE")).thenReturn(6L);
        when(inventarioRepository.countByEstado("PRESTADO")).thenReturn(4L);
        when(inventarioRepository.countByEstado("DANADO")).thenReturn(1L);
        when(prestamoRepository.countByEstado("ACTIVO")).thenReturn(5L);
        when(prestamoRepository.countByEstado("VENCIDO")).thenReturn(3L);
        when(prestamoRepository.countByEstado("RESERVADO")).thenReturn(1L);
        when(prestamoRepository.countByEstado("DEVUELTO")).thenReturn(20L);
        when(prestamoRepository.countByEstadoAndFechaVencimientoBefore(eq("ACTIVO"), any(LocalDateTime.class))).thenReturn(2L);
        when(prestamoRepository.countByEstadoAndFechaVencimientoBetween(eq("ACTIVO"), any(LocalDateTime.class), any(LocalDateTime.class))).thenReturn(4L);
        when(prestamoRepository.countByFechaPrestamoBetween(any(LocalDateTime.class), any(LocalDateTime.class))).thenReturn(12L);
        when(prestamoRepository.countByFechaDevolucionBetween(any(LocalDateTime.class), any(LocalDateTime.class))).thenReturn(9L);
        when(usuarioRepository.count()).thenReturn(50L);
        when(usuarioRepository.countByCreatedAtBetween(any(LocalDateTime.class), any(LocalDateTime.class))).thenReturn(5L);
        when(usuarioRepository.countByActivoTrue()).thenReturn(40L);
        when(usuarioRepository.countUsuariosConSancionActiva()).thenReturn(2L);
        when(usuarioRepository.findByActivoTrue()).thenReturn(List.of(
                Usuario.builder().id(1L).nombre("Ana").rol(Rol.builder().nombre("ADMIN").build()).build()));
        when(reservaRepository.countByEstado("PENDIENTE")).thenReturn(3L);
        when(reservaRepository.countByEstado("CONFIRMADA")).thenReturn(2L);
        when(reservaRepository.countByEstado("COMPLETADA")).thenReturn(4L);
        when(reservaRepository.countByEstado("CANCELADA")).thenReturn(1L);
        when(reservaRepository.countByCreatedAtBetween(any(LocalDateTime.class), any(LocalDateTime.class))).thenReturn(3L);
        when(multaRepository.countByPagadaFalse()).thenReturn(6L);
        when(multaRepository.totalMultasPendientes()).thenReturn(120.50D);
        when(multaRepository.countByCreatedAtBetween(any(LocalDateTime.class), any(LocalDateTime.class))).thenReturn(4L);
        when(multaRepository.countByPagadaTrueAndFechaPagoBetween(any(LocalDateTime.class), any(LocalDateTime.class))).thenReturn(2L);
        when(sancionRepository.countByActivaFalse()).thenReturn(1L);
        when(sancionRepository.countByCreatedAtBetween(any(LocalDateTime.class), any(LocalDateTime.class))).thenReturn(3L);
        when(qrCodigoRepository.countByActivo(true)).thenReturn(30L);
        when(qrCodigoRepository.countByActivo(false)).thenReturn(5L);
        when(auditoriaRepository.countQrEventos(any(), any(LocalDateTime.class), any(LocalDateTime.class))).thenReturn(1L);
        when(auditoriaRepository.findActividadReciente(any(PageRequest.class))).thenReturn(List.of());
        when(prestamoRepository.countPrestamosPorDia(any(LocalDateTime.class), any(LocalDateTime.class))).thenReturn(List.of());
        when(prestamoRepository.countDevolucionesPorDia(any(LocalDateTime.class), any(LocalDateTime.class))).thenReturn(List.of());
        when(reservaRepository.countReservasPorDia(any(LocalDateTime.class), any(LocalDateTime.class))).thenReturn(List.of());
        when(qrCodigoRepository.countPorDia(any(LocalDateTime.class), any(LocalDateTime.class))).thenReturn(List.of());
        when(prestamoRepository.countPrestamosPorCategoria(any(LocalDateTime.class), any(LocalDateTime.class))).thenReturn(List.of());

        DashboardResumenResponse resumen = dashboardService.resumen(principal(1L, "ADMIN"), null, null);

        assertEquals("ADMIN", resumen.getRol());
        assertEquals(LocalDate.now().minusDays(30), resumen.getDesde());
        assertEquals(LocalDate.now(), resumen.getHasta());
        assertEquals(3L, resumen.getNotificacionesNoLeidas());
        assertEquals(5L, resumen.getKpis().getPrestamosVencidos());
        assertEquals(40L, resumen.getKpis().getUsuariosActivos());
        assertEquals(1, resumen.getKpis().getUsuariosPorRol().size());
        assertEquals(100.0, resumen.getKpis().getUsuariosPorRol().get(0).getPorcentaje());
        assertTrue(resumen.getEstadoSistema().isBaseDeDatosOperativa());
        assertTrue(resumen.getEstadoSistema().isQrOperativo());
        assertEquals(31, resumen.getActividadPorDia().size());
        assertTrue(resumen.getAlertas().stream().anyMatch(a -> "CRITICA".equals(a.getPrioridad())));
    }

    @Test
    void resumenDeEstudianteConAlertasPersonales() {
        stubEstadoSistema();
        when(notificacionRepository.countByUsuarioIdAndLeidaFalse(2L)).thenReturn(0L);
        when(libroRepository.countByEjemplaresDisponiblesGreaterThan(0)).thenReturn(8L);
        when(libroRepository.countByActivoTrue()).thenReturn(10L);
        when(prestamoRepository.countByUsuarioIdAndEstado(2L, "ACTIVO")).thenReturn(3L);
        when(prestamoRepository.countByUsuarioIdAndEstado(2L, "VENCIDO")).thenReturn(1L);
        when(prestamoRepository.countByUsuarioIdAndEstadoAndFechaVencimientoBefore(eq(2L), eq("ACTIVO"), any(LocalDateTime.class))).thenReturn(1L);
        when(prestamoRepository.countByUsuarioIdAndEstadoAndFechaVencimientoBetween(eq(2L), eq("ACTIVO"), any(LocalDateTime.class), any(LocalDateTime.class))).thenReturn(2L);
        when(prestamoRepository.countByUsuarioIdAndFechaDevolucionBetween(eq(2L), any(LocalDateTime.class), any(LocalDateTime.class))).thenReturn(4L);
        when(reservaRepository.countByUsuarioIdAndEstado(2L, "PENDIENTE")).thenReturn(1L);
        when(sancionRepository.countByUsuarioIdAndActivaTrue(2L)).thenReturn(0L);
        when(multaRepository.countByUsuarioIdAndPagadaFalse(2L)).thenReturn(0L);
        when(prestamoRepository.findRecientesPorUsuario(eq(2L), any(PageRequest.class))).thenReturn(List.of());
        when(reservaRepository.findByUsuarioId(eq(2L), any(PageRequest.class))).thenReturn(Page.empty());

        DashboardResumenResponse resumen = dashboardService.resumen(principal(2L, "ESTUDIANTE"),
                LocalDate.of(2026, 8, 1), LocalDate.of(2026, 8, 31));

        assertEquals("ESTUDIANTE", resumen.getRol());
        assertEquals(LocalDate.of(2026, 8, 1), resumen.getDesde());
        assertEquals(LocalDate.of(2026, 8, 31), resumen.getHasta());
        assertEquals(2L, resumen.getKpis().getPrestamosVencidos());
        assertEquals(3L, resumen.getKpis().getPrestamosActivos());
        assertEquals(2L, resumen.getKpis().getPrestamosProximos24h());
        assertEquals(2, resumen.getAlertas().size());
    }

    @Test
    void resumenInvierteRangoCuandoDesdeEsPosteriorAHasta() {
        stubEstadoSistema();
        when(notificacionRepository.countByUsuarioIdAndLeidaFalse(3L)).thenReturn(0L);
        when(libroRepository.countByEjemplaresDisponiblesGreaterThan(0)).thenReturn(8L);
        when(libroRepository.countByActivoTrue()).thenReturn(10L);
        when(prestamoRepository.countByUsuarioIdAndEstado(3L, "ACTIVO")).thenReturn(0L);
        when(prestamoRepository.countByUsuarioIdAndEstado(3L, "VENCIDO")).thenReturn(0L);
        when(prestamoRepository.countByUsuarioIdAndEstadoAndFechaVencimientoBefore(eq(3L), eq("ACTIVO"), any(LocalDateTime.class))).thenReturn(0L);
        when(prestamoRepository.countByUsuarioIdAndEstadoAndFechaVencimientoBetween(eq(3L), eq("ACTIVO"), any(LocalDateTime.class), any(LocalDateTime.class))).thenReturn(0L);
        when(prestamoRepository.countByUsuarioIdAndFechaDevolucionBetween(eq(3L), any(LocalDateTime.class), any(LocalDateTime.class))).thenReturn(0L);
        when(reservaRepository.countByUsuarioIdAndEstado(3L, "PENDIENTE")).thenReturn(0L);
        when(sancionRepository.countByUsuarioIdAndActivaTrue(3L)).thenReturn(0L);
        when(multaRepository.countByUsuarioIdAndPagadaFalse(3L)).thenReturn(0L);
        when(prestamoRepository.findRecientesPorUsuario(eq(3L), any(PageRequest.class))).thenReturn(List.of());
        when(reservaRepository.findByUsuarioId(eq(3L), any(PageRequest.class))).thenReturn(Page.empty());

        DashboardResumenResponse resumen = dashboardService.resumen(principal(3L, "ESTUDIANTE"),
                LocalDate.of(2026, 9, 5), LocalDate.of(2026, 9, 1));

        assertEquals(LocalDate.of(2026, 9, 1), resumen.getDesde());
        assertEquals(LocalDate.of(2026, 9, 5), resumen.getHasta());
        assertTrue(resumen.getAlertas().isEmpty());
    }

    @Test
    void actividadRecienteStaffMapeaAuditoriaConYsinUsuario() {
        Auditoria conUsuario = Auditoria.builder()
                .id(1L).accion("CREAR").entidad("PRESTAMO").detalle("Detalle")
                .usuario(Usuario.builder().id(2L).nombre("Ana").build())
                .createdAt(LocalDateTime.of(2026, 9, 1, 10, 0)).build();
        Auditoria sinUsuario = Auditoria.builder()
                .id(2L).accion("VALIDAR").entidad("CÃ“DIGO QR").detalle("Otro").createdAt(LocalDateTime.now()).build();
        when(auditoriaRepository.findActividadReciente(any(PageRequest.class))).thenReturn(List.of(conUsuario, sinUsuario));

        List<DashboardActividadItemResponse> items = dashboardService.actividadRecienteStaff();

        assertEquals(2, items.size());
        assertEquals("Ana", items.get(0).getUsuarioNombre());
        assertEquals("VALIDAR", items.get(1).getAccion());
        assertFalse(items.get(1).getDetalle().isBlank());
    }

    @Test
    void estadoSistemaReportaBaseDeDatosOperativa() {
        when(prestamoRepository.count()).thenReturn(5L);
        assertTrue(dashboardService.resumen(principal(1L, "ADMIN"), null, null).getEstadoSistema().isBaseDeDatosOperativa());
    }

    private void stubEstadoSistema() {
        when(prestamoRepository.count()).thenReturn(5L);
    }
}
