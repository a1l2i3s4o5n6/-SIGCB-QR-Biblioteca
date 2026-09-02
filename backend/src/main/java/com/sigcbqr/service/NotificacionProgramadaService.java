package com.sigcbqr.service;

import com.sigcbqr.model.entity.Notificacion;
import com.sigcbqr.model.entity.Prestamo;
import com.sigcbqr.model.entity.Sancion;
import com.sigcbqr.model.entity.Usuario;
import com.sigcbqr.repository.NotificacionRepository;
import com.sigcbqr.repository.PrestamoRepository;
import com.sigcbqr.repository.SancionRepository;
import com.sigcbqr.repository.UsuarioRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.scheduling.annotation.Scheduled;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.time.LocalDateTime;
import java.util.ArrayList;
import java.util.List;

@Service
@RequiredArgsConstructor
public class NotificacionProgramadaService {

    private static final long HORAS_AVISO_PRESTAMO = 24;
    private static final long DIAS_AVISO_SANCION = 3;
    private static final long VENTANA_DEDUPLICACION_HORAS = 6;

    private final PrestamoRepository prestamoRepository;
    private final SancionRepository sancionRepository;
    private final UsuarioRepository usuarioRepository;
    private final NotificacionRepository notificacionRepository;

    @Scheduled(cron = "0 0 */6 * * *")
    @Transactional
    public void procesarVencimientos() {
        LocalDateTime ahora = LocalDateTime.now();
        marcarPrestamosVencidos(ahora);
        notificarPrestamosProximosAVencer(ahora);
        notificarSanciones(ahora);
    }

    private void marcarPrestamosVencidos(LocalDateTime ahora) {
        List<Prestamo> vencidos = prestamoRepository.findByEstadoAndFechaVencimientoBefore("ACTIVO", ahora);
        for (Prestamo p : vencidos) {
            p.setEstado("VENCIDO");
            prestamoRepository.save(p);
            crearNotificacion(new NotificacionRecurso(p.getUsuario(), "PRESTAMO",
                    "Pr\u00e9stamo vencido",
                    "El pr\u00e9stamo de \"" + p.getInventario().getLibro().getTitulo()
                            + "\" ha vencido. Devu\u00e9lvelo o ren\u00f3valo para evitar multas."));
        }
    }

    private void notificarPrestamosProximosAVencer(LocalDateTime ahora) {
        List<Prestamo> proximos =
                prestamoRepository.findByEstadoAndFechaVencimientoBetween("ACTIVO", ahora, ahora.plusHours(HORAS_AVISO_PRESTAMO));
        for (Prestamo p : proximos) {
            crearNotificacion(new NotificacionRecurso(p.getUsuario(), "PRESTAMO",
                    "Tu pr\u00e9stamo vence pronto",
                    "El pr\u00e9stamo de \"" + p.getInventario().getLibro().getTitulo()
                            + "\" vence el " + p.getFechaVencimiento().toLocalDate()));
        }
    }

    private void notificarSanciones(LocalDateTime ahora) {
        List<Sancion> activas = sancionRepository.findByActivaTrue();
        for (Sancion s : activas) {
            if (s.getFechaFin() == null) {
                continue;
            }
            if (s.getFechaFin().isBefore(ahora)) {
                notificarStaff(new NotificacionRecurso(s.getUsuario(), "SANCION",
                        "Sanci\u00f3n vencida por revisar",
                        "La sanci\u00f3n " + s.getTipo() + " de " + s.getUsuario().getNombre()
                                + " super\u00f3 su fecha de fin (" + s.getFechaFin().toLocalDate() + ")."));
            } else if (!s.getFechaFin().isAfter(ahora.plusDays(DIAS_AVISO_SANCION))) {
                crearNotificacion(new NotificacionRecurso(s.getUsuario(), "SANCION",
                        "Tu sanci\u00f3n termina pronto",
                        "Tu sanci\u00f3n de tipo " + s.getTipo() + " termina el " + s.getFechaFin().toLocalDate() + "."));
            }
        }
    }

    private void notificarStaff(NotificacionRecurso recurso) {
        List<Usuario> staff = new ArrayList<>(usuarioRepository.findByRol_Nombre("ADMIN"));
        staff.addAll(usuarioRepository.findByRol_Nombre("BIBLIOTECARIO"));
        for (Usuario u : staff) {
            crearNotificacion(recurso.conDestinatario(u));
        }
    }

    private void crearNotificacion(NotificacionRecurso recurso) {
        boolean reciente = notificacionRepository
                .countByUsuarioIdAndTituloContainingAndCreatedAtAfter(
                        recurso.usuario().getId(), recurso.titulo().substring(0, Math.min(20, recurso.titulo().length())),
                        LocalDateTime.now().minusHours(VENTANA_DEDUPLICACION_HORAS)) > 0;
        if (reciente) {
            return;
        }
        notificacionRepository.save(Notificacion.builder()
                .usuario(recurso.usuario())
                .titulo(recurso.titulo())
                .mensaje(recurso.mensaje())
                .tipo(recurso.tipo())
                .leida(false)
                .build());
    }

    private record NotificacionRecurso(Usuario usuario, String tipo, String titulo, String mensaje) {
        NotificacionRecurso conDestinatario(Usuario destinatario) {
            return new NotificacionRecurso(destinatario, tipo, titulo, mensaje);
        }
    }
}