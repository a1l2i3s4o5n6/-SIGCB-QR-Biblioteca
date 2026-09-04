package com.sigcbqr.service;

import com.sigcbqr.model.dto.response.dashboard.*;
import com.sigcbqr.model.entity.Auditoria;
import com.sigcbqr.model.entity.Prestamo;
import com.sigcbqr.model.entity.Reserva;
import com.sigcbqr.model.entity.Sancion;
import com.sigcbqr.model.entity.Usuario;
import com.sigcbqr.repository.*;
import com.sigcbqr.security.UserPrincipal;
import org.springframework.data.domain.PageRequest;
import org.springframework.data.domain.Sort;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.sql.Date;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.LocalTime;
import java.util.*;

@Service
public class DashboardService {

    private static final long HORAS_AVISO_PRESTAMO = 24;
    private static final long DIAS_AVISO_SANCION = 3;

    private final PrestamoRepository prestamoRepository;
    private final LibroRepository libroRepository;
    private final UsuarioRepository usuarioRepository;
    private final ReservaRepository reservaRepository;
    private final MultaRepository multaRepository;
    private final SancionRepository sancionRepository;
    private final QrCodigoRepository qrCodigoRepository;
    private final InventarioRepository inventarioRepository;
    private final AuditoriaRepository auditoriaRepository;
    private final NotificacionRepository notificacionRepository;

    public DashboardService(PrestamoRepository prestamoRepository,
                            LibroRepository libroRepository,
                            UsuarioRepository usuarioRepository,
                            ReservaRepository reservaRepository,
                            MultaRepository multaRepository,
                            SancionRepository sancionRepository,
                            QrCodigoRepository qrCodigoRepository,
                            InventarioRepository inventarioRepository,
                            AuditoriaRepository auditoriaRepository,
                            NotificacionRepository notificacionRepository) {
        this.prestamoRepository = prestamoRepository;
        this.libroRepository = libroRepository;
        this.usuarioRepository = usuarioRepository;
        this.reservaRepository = reservaRepository;
        this.multaRepository = multaRepository;
        this.sancionRepository = sancionRepository;
        this.qrCodigoRepository = qrCodigoRepository;
        this.inventarioRepository = inventarioRepository;
        this.auditoriaRepository = auditoriaRepository;
        this.notificacionRepository = notificacionRepository;
    }

    @Transactional(readOnly = true)
    public Map<String, Object> getStats() {
        LocalDateTime inicio = LocalDateTime.now().minusDays(1);
        LocalDateTime fin = LocalDateTime.now();
        Map<String, Object> stats = new LinkedHashMap<>();
        stats.put("librosPrestadosHoy", prestamoRepository.countByFechaPrestamoBetween(inicio, fin));
        stats.put("librosDisponibles", libroRepository.countByEjemplaresDisponiblesGreaterThan(0));
        stats.put("estudiantesActivos", usuarioRepository.countByActivoTrue());
        stats.put("reservasPendientes", reservaRepository.countByEstado("PENDIENTE"));
        stats.put("multasPendientes", multaRepository.countByPagadaFalse());
        stats.put("totalMultas", multaRepository.totalMultasPendientes());
        return stats;
    }

    @Transactional(readOnly = true)
    public long contarPrestamosActivos(Long usuarioId) {
        return prestamoRepository.countByUsuarioIdAndEstado(usuarioId, "ACTIVO");
    }

    @Transactional(readOnly = true)
    public long contarMultasPendientes(Long usuarioId) {
        return multaRepository.countByUsuarioIdAndPagadaFalse(usuarioId);
    }

    @Transactional(readOnly = true)
    public long contarReservasPendientes(Long usuarioId) {
        return reservaRepository.countByUsuarioIdAndEstado(usuarioId, "PENDIENTE");
    }

    @Transactional(readOnly = true)
    public DashboardResumenResponse resumen(UserPrincipal principal, LocalDate desde, LocalDate hasta) {
        LocalDate desdeOk = (desde != null) ? desde : LocalDate.now().minusDays(30);
        LocalDate hastaOk = (hasta != null) ? hasta : LocalDate.now();
        if (desdeOk.isAfter(hastaOk)) {
            LocalDate tmp = desdeOk;
            desdeOk = hastaOk;
            hastaOk = tmp;
        }

        boolean staff = "ADMIN".equals(principal.rol()) || "BIBLIOTECARIO".equals(principal.rol());

        DashboardResumenResponse.DashboardResumenResponseBuilder base = DashboardResumenResponse.builder()
                .rol(principal.rol())
                .generadoEl(LocalDateTime.now())
                .desde(desdeOk)
                .hasta(hastaOk)
                .alertas(new ArrayList<>())
                .actividadPorDia(new ArrayList<>())
                .prestamosPorCategoria(new ArrayList<>())
                .estadoSistema(estadoSistema())
                .notificacionesNoLeidas(notificacionRepository.countByUsuarioIdAndLeidaFalse(principal.id()));

        if (staff) {
            base.kpis(kpisStaff(desdeOk, hastaOk));
            base.actividadReciente(actividadRecienteStaff());
            base.alertas(alertasStaff(desdeOk, hastaOk));
            base.actividadPorDia(seriesPorDia(desdeOk, hastaOk));
            base.prestamosPorCategoria(prestamosPorCategoria(desdeOk, hastaOk));
        } else {
            base.kpis(kpisEstudiante(principal.id(), desdeOk, hastaOk));
            base.actividadReciente(actividadRecienteEstudiante(principal.id()));
            base.alertas(alertasEstudiante(principal.id()));
        }
        return base.build();
    }

    private DashboardKpisResponse kpisStaff(LocalDate desde, LocalDate hasta) {
        LocalDateTime inicio = desde.atStartOfDay();
        LocalDateTime fin = hasta.atTime(LocalTime.MAX);
        LocalDateTime ahora = LocalDateTime.now();

        List<Sancion> sancionesActivas = sancionRepository.findByActivaTrue();

        return DashboardKpisResponse.builder()
                .librosRegistrados(libroRepository.countByActivoTrue())
                .librosNuevosPeriodo(libroRepository.countByCreatedAtBetween(inicio, fin))
                .librosDisponibles(libroRepository.countByEjemplaresDisponiblesGreaterThan(0))
                .ejemplaresDisponibles(inventarioRepository.countByEstado("DISPONIBLE"))
                .ejemplaresPrestados(inventarioRepository.countByEstado("PRESTADO"))
                .ejemplaresDanados(inventarioRepository.countByEstado("DANADO"))
                .prestamosActivos(prestamoRepository.countByEstado("ACTIVO"))
                .prestamosVencidos(prestamoRepository.countByEstado("VENCIDO")
                        + prestamoRepository.countByEstadoAndFechaVencimientoBefore("ACTIVO", ahora))
                .prestamosReservados(prestamoRepository.countByEstado("RESERVADO"))
                .prestamosDevueltos(prestamoRepository.countByEstado("DEVUELTO"))
                .prestamosProximos24h(prestamoRepository.countByEstadoAndFechaVencimientoBetween(
                        "ACTIVO", ahora, ahora.plusHours(HORAS_AVISO_PRESTAMO)))
                .prestamosProximos7dias(prestamoRepository.countByEstadoAndFechaVencimientoBetween(
                        "ACTIVO", ahora, ahora.plusDays(7)))
                .prestamosRegistradosPeriodo(prestamoRepository.countByFechaPrestamoBetween(inicio, fin))
                .prestamosDevueltosPeriodo(prestamoRepository.countByFechaDevolucionBetween(inicio, fin))
                .usuariosRegistrados(usuarioRepository.count())
                .usuariosNuevosPeriodo(usuarioRepository.countByCreatedAtBetween(inicio, fin))
                .usuariosActivos(usuarioRepository.countByActivoTrue())
                .usuariosConSancionActiva(usuarioRepository.countUsuariosConSancionActiva())
                .reservasPendientes(reservaRepository.countByEstado("PENDIENTE"))
                .reservasConfirmadas(reservaRepository.countByEstado("CONFIRMADA"))
                .reservasCompletadas(reservaRepository.countByEstado("COMPLETADA"))
                .reservasCanceladas(reservaRepository.countByEstado("CANCELADA"))
                .reservasCreadasPeriodo(reservaRepository.countByCreatedAtBetween(inicio, fin))
                .usuariosPorRol(usuariosPorRol())
                .multasPendientes(multaRepository.countByPagadaFalse())
                .totalMultasPendientes(multaRepository.totalMultasPendientes())
                .multasGeneradasPeriodo(multaRepository.countByCreatedAtBetween(inicio, fin))
                .multasPagadasPeriodo(multaRepository.countByPagadaTrueAndFechaPagoBetween(inicio, fin))
                .sancionesActivas(sancionesActivas.size())
                .sancionesVencidas(sancionesActivas.stream()
                        .filter(s -> s.getFechaFin() != null && s.getFechaFin().isBefore(LocalDateTime.now()))
                        .count())
                .sancionesProximas(sancionesActivas.stream()
                        .filter(s -> s.getFechaFin() != null
                                && !s.getFechaFin().isBefore(LocalDateTime.now())
                                && !s.getFechaFin().isAfter(LocalDateTime.now().plusDays(DIAS_AVISO_SANCION)))
                        .count())
                .sancionesResueltas(sancionRepository.countByActivaFalse())
                .sancionesNuevasPeriodo(sancionRepository.countByCreatedAtBetween(inicio, fin))
                .qrActivos(qrCodigoRepository.countByActivo(true))
                .qrInactivos(qrCodigoRepository.countByActivo(false))
                .qrCreadosPeriodo(auditoriaRepository.countQrEventos("CREAR", inicio, fin))
                .qrRegeneradosPeriodo(auditoriaRepository.countQrEventos("REGENERAR", inicio, fin))
                .qrActivadosPeriodo(auditoriaRepository.countQrEventos("ACTIVAR", inicio, fin))
                .qrDesactivadosPeriodo(auditoriaRepository.countQrEventos("DESACTIVAR", inicio, fin))
                .build();
    }

    private DashboardKpisResponse kpisEstudiante(Long usuarioId, LocalDate desde, LocalDate hasta) {
        LocalDateTime inicio = desde.atStartOfDay();
        LocalDateTime fin = hasta.atTime(LocalTime.MAX);
        LocalDateTime ahora = LocalDateTime.now();

        return DashboardKpisResponse.builder()
                .librosDisponibles(libroRepository.countByEjemplaresDisponiblesGreaterThan(0))
                .librosRegistrados(libroRepository.countByActivoTrue())
                .prestamosActivos(prestamoRepository.countByUsuarioIdAndEstado(usuarioId, "ACTIVO"))
                .prestamosVencidos(prestamoRepository.countByUsuarioIdAndEstado(usuarioId, "VENCIDO")
                        + prestamoRepository.countByUsuarioIdAndEstadoAndFechaVencimientoBefore(usuarioId, "ACTIVO", ahora))
                .prestamosProximos24h(prestamoRepository.countByUsuarioIdAndEstadoAndFechaVencimientoBetween(
                        usuarioId, "ACTIVO", ahora, ahora.plusHours(HORAS_AVISO_PRESTAMO)))
                .prestamosDevueltosPeriodo(prestamoRepository.countByUsuarioIdAndFechaDevolucionBetween(usuarioId, inicio, fin))
                .reservasPendientes(reservaRepository.countByUsuarioIdAndEstado(usuarioId, "PENDIENTE"))
                .sancionesActivas(sancionRepository.countByUsuarioIdAndActivaTrue(usuarioId))
                .multasPendientes(multaRepository.countByUsuarioIdAndPagadaFalse(usuarioId))
                .build();
    }

    @Transactional(readOnly = true)
    public List<DashboardActividadItemResponse> actividadRecienteStaff() {
        return auditoriaRepository.findActividadReciente(PageRequest.of(0, 12))
                .stream().map(this::toActividadItem).toList();
    }

    private List<DashboardActividadItemResponse> actividadRecienteEstudiante(Long usuarioId) {
        List<DashboardActividadItemResponse> items = new ArrayList<>();

        prestamoRepository.findRecientesPorUsuario(usuarioId, PageRequest.of(0, 5))
                .forEach(p -> items.add(DashboardActividadItemResponse.builder()
                        .id(p.getId())
                        .accion("DEVUELTO".equals(p.getEstado()) ? "DEVOLVER" : "CREAR")
                        .entidad("PRESTAMO")
                        .detalle(p.getInventario().getLibro().getTitulo())
                        .usuarioNombre(p.getUsuario().getNombre())
                        .createdAt(p.getFechaPrestamo())
                        .build()));

        reservaRepository.findByUsuarioId(usuarioId, PageRequest.of(0, 5).withSort(Sort.by("createdAt").descending()))
                .stream().map(r -> DashboardActividadItemResponse.builder()
                        .id(r.getId())
                        .accion("CREAR")
                        .entidad("RESERVA")
                        .detalle(r.getLibro().getTitulo())
                        .usuarioNombre(r.getUsuario().getNombre())
                        .createdAt(r.getCreatedAt())
                        .build())
                .forEach(items::add);

        items.sort(Comparator.comparing(DashboardActividadItemResponse::getCreatedAt,
                Comparator.nullsLast(Comparator.reverseOrder())));
        return items.stream().limit(8).toList();
    }

    private DashboardActividadItemResponse toActividadItem(Auditoria a) {
        return DashboardActividadItemResponse.builder()
                .id(a.getId())
                .accion(a.getAccion())
                .entidad(a.getEntidad())
                .detalle(a.getDetalle())
                .usuarioNombre(a.getUsuario() != null ? a.getUsuario().getNombre() : null)
                .createdAt(a.getCreatedAt())
                .build();
    }

    private List<DashboardAlertaResponse> alertasStaff(LocalDate desde, LocalDate hasta) {
        DashboardKpisResponse k = kpisStaff(desde, hasta);
        List<DashboardAlertaResponse> alertas = new ArrayList<>();

        if (k.getPrestamosVencidos() > 0) {
            alertas.add(alerta("PELIGRO", "CRITICA",
                    k.getPrestamosVencidos() + " pr\u00e9stamo(s) vencido(s)",
                    "Requieren de devoluci\u00f3n o renovaci\u00f3n inmediata.", "/prestamos", "Ver pr\u00e9stamos"));
        }
        if (k.getSancionesVencidas() > 0) {
            alertas.add(alerta("PELIGRO", "CRITICA",
                    k.getSancionesVencidas() + " sanci\u00f3n(es) vencida(s) requieren revisi\u00f3n",
                    "Sanciones activas que han superado su fecha de fin.", "/sanciones", "Ver sanciones"));
        }
        if (k.getPrestamosProximos24h() > 0) {
            alertas.add(alerta("ADVERTENCIA", "ALTA",
                    k.getPrestamosProximos24h() + " pr\u00e9stamo(s) vencen en las pr\u00f3ximas 24 horas",
                    "Verifique devoluciones o renovaciones pendientes.", "/prestamos", "Ver pr\u00e9stamos"));
        }
        if (k.getSancionesActivas() > k.getSancionesVencidas()) {
            alertas.add(alerta("ADVERTENCIA", "ALTA",
                    (k.getSancionesActivas() - k.getSancionesVencidas()) + " sanci\u00f3n(es) activa(s) pendientes de resoluci\u00f3n",
                    "Revise y levante las sanciones correspondientes.", "/sanciones", "Ver sanciones"));
        }
        if (k.getMultasPendientes() > 0) {
            alertas.add(alerta("INFORMACION", "MEDIA",
                    k.getMultasPendientes() + " multa(s) pendiente(s) por $" + String.format(Locale.US, "%.2f", k.getTotalMultasPendientes()),
                    "Cobros pendientes por devoluciones fuera de plazo.", "/multas", "Ver multas"));
        }
        if (k.getEjemplaresDanados() > 0) {
            alertas.add(alerta("INFORMACION", "MEDIA",
                    k.getEjemplaresDanados() + " ejemplar(es) reportado(s) como da\u00f1ado(s)",
                    "Requieren revisi\u00f3n o reparaci\u00f3n en inventario.", "/catalogo", "Ver cat\u00e1logo"));
        }
        if (k.getReservasPendientes() > 0) {
            alertas.add(alerta("INFORMACION", "MEDIA",
                    k.getReservasPendientes() + " reserva(s) pendiente(s) de atenci\u00f3n",
                    "Reservas creadas que a\u00fan no se confirman ni se despachan.", "/reservas", "Ver reservas"));
        }
        if (k.getQrInactivos() > 0) {
            alertas.add(alerta("INFORMACION", "BAJA",
                    k.getQrInactivos() + " c\u00f3digo(s) QR inactivo(s)",
                    "C\u00f3digos desactivados que pueden requerir reactivaci\u00f3n.", "/qr-codigos", "Ver QR"));
        }
        return alertas;
    }

    private List<DashboardAlertaResponse> alertasEstudiante(Long usuarioId) {
        LocalDateTime ahora = LocalDateTime.now();
        List<DashboardAlertaResponse> alertas = new ArrayList<>();

        long vencidos = prestamoRepository.countByUsuarioIdAndEstadoAndFechaVencimientoBefore(usuarioId, "ACTIVO", ahora);
        if (vencidos > 0) {
            alertas.add(alerta("PELIGRO", "CRITICA",
                    "Tienes " + vencidos + " pr\u00e9stamo(s) vencido(s)",
                    "Devuelve o renueva los libros para evitar multas.", "/prestamos", "Ver mis pr\u00e9stamos"));
        }
        long proximos = prestamoRepository.countByUsuarioIdAndEstadoAndFechaVencimientoBetween(
                usuarioId, "ACTIVO", ahora, ahora.plusHours(HORAS_AVISO_PRESTAMO));
        if (proximos > 0) {
            alertas.add(alerta("ADVERTENCIA", "ALTA",
                    proximos + " pr\u00e9stamo(s) vence(n) en las pr\u00f3ximas 24 horas", "", "/prestamos", "Ver mis pr\u00e9stamos"));
        }
        long sanciones = sancionRepository.countByUsuarioIdAndActivaTrue(usuarioId);
        if (sanciones > 0) {
            alertas.add(alerta("PELIGRO", "CRITICA",
                    "Tienes " + sanciones + " sanci\u00f3n(es) activa(s)",
                    "Consulta el detalle en el m\u00f3dulo de sanciones.", "/sanciones", "Ver sanciones"));
        }
        long multas = multaRepository.countByUsuarioIdAndPagadaFalse(usuarioId);
        if (multas > 0) {
            alertas.add(alerta("INFORMACION", "MEDIA",
                    "Tienes " + multas + " multa(s) pendiente(s) de pago", "", "/multas", "Ver multas"));
        }
        return alertas;
    }

    private List<DashboardRolCantidadResponse> usuariosPorRol() {
        Map<String, Long> porRol = new LinkedHashMap<>();
        for (Usuario u : usuarioRepository.findByActivoTrue()) {
            porRol.merge(u.getRol().getNombre(), 1L, Long::sum);
        }
        long total = porRol.values().stream().mapToLong(Long::longValue).sum();
        List<DashboardRolCantidadResponse> resultado = new ArrayList<>();
        for (Map.Entry<String, Long> e : porRol.entrySet()) {
            double porcentaje = total > 0 ? Math.round((e.getValue() * 10000.0) / total) / 100.0 : 0;
            resultado.add(DashboardRolCantidadResponse.builder()
                    .rol(e.getKey())
                    .cantidad(e.getValue())
                    .porcentaje(porcentaje)
                    .build());
        }
        return resultado;
    }

    private DashboardAlertaResponse alerta(String tipo, String prioridad, String descripcion, String detalle,
                                           String url, String accion) {
        return DashboardAlertaResponse.builder()
                .tipo(tipo)
                .prioridad(prioridad)
                .descripcion(descripcion)
                .detalle(detalle)
                .fecha(LocalDateTime.now())
                .accion(accion)
                .url(url)
                .build();
    }

    private List<DashboardSerieDiaResponse> seriesPorDia(LocalDate desde, LocalDate hasta) {
        LocalDateTime inicio = desde.atStartOfDay();
        LocalDateTime fin = hasta.atTime(LocalTime.MAX);

        Map<LocalDate, Long> prestamos = agruparPorDia(prestamoRepository.countPrestamosPorDia(inicio, fin));
        Map<LocalDate, Long> devoluciones = agruparPorDia(prestamoRepository.countDevolucionesPorDia(inicio, fin));
        Map<LocalDate, Long> reservas = agruparPorDia(reservaRepository.countReservasPorDia(inicio, fin));
        Map<LocalDate, Long> qr = agruparPorDia(qrCodigoRepository.countPorDia(inicio, fin));

        List<DashboardSerieDiaResponse> serie = new ArrayList<>();
        for (LocalDate d = desde; !d.isAfter(hasta); d = d.plusDays(1)) {
            serie.add(DashboardSerieDiaResponse.builder()
                    .fecha(d.toString())
                    .prestamos(prestamos.getOrDefault(d, 0L))
                    .devoluciones(devoluciones.getOrDefault(d, 0L))
                    .reservas(reservas.getOrDefault(d, 0L))
                    .qr(qr.getOrDefault(d, 0L))
                    .build());
        }
        return serie;
    }

    private Map<LocalDate, Long> agruparPorDia(List<Object[]> filas) {
        Map<LocalDate, Long> mapa = new HashMap<>();
        if (filas == null) {
            return mapa;
        }
        for (Object[] fila : filas) {
            LocalDate dia = toLocalDate(fila[0]);
            if (dia != null) {
                mapa.put(dia, ((Number) fila[1]).longValue());
            }
        }
        return mapa;
    }

    private LocalDate toLocalDate(Object valor) {
        if (valor instanceof Date date) {
            return date.toLocalDate();
        }
        if (valor instanceof LocalDate localDate) {
            return localDate;
        }
        return null;
    }

    private List<DashboardCategoriaResponse> prestamosPorCategoria(LocalDate desde, LocalDate hasta) {
        LocalDateTime inicio = desde.atStartOfDay();
        LocalDateTime fin = hasta.atTime(LocalTime.MAX);

        List<DashboardCategoriaResponse> resultado = new ArrayList<>();
        List<Object[]> filas = prestamoRepository.countPrestamosPorCategoria(inicio, fin);
        long total = filas.stream().mapToLong(f -> ((Number) f[1]).longValue()).sum();
        for (Object[] fila : filas) {
            String categoria = (String) fila[0];
            long cantidad = ((Number) fila[1]).longValue();
            double porcentaje = total > 0 ? Math.round((cantidad * 10000.0) / total) / 100.0 : 0;
            resultado.add(DashboardCategoriaResponse.builder()
                    .categoria(categoria)
                    .cantidad(cantidad)
                    .porcentaje(porcentaje)
                    .build());
        }
        return resultado;
    }

    private DashboardEstadoSistemaResponse estadoSistema() {
        DashboardEstadoSistemaResponse estado =
                DashboardEstadoSistemaResponse.builder()
                        .baseDeDatosOperativa(false)
                        .apiOperativa(true)
                        .qrOperativo(false)
                        .respaldoDisponible(false)
                        .ultimoRespaldo(null)
                        .build();
        try {
            prestamoRepository.count();
            estado.setBaseDeDatosOperativa(true);
            estado.setQrOperativo(true);
        } catch (Exception ignored) {
            estado.setBaseDeDatosOperativa(false);
        }
        return estado;
    }
}