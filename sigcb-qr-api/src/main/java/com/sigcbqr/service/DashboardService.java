package com.sigcbqr.service;

import com.sigcbqr.model.dto.response.DashboardResponse;
import com.sigcbqr.repository.*;
import org.springframework.stereotype.Service;

import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.LocalTime;

@Service
public class DashboardService {

    private final PrestamoRepository prestamoRepository;
    private final LibroRepository libroRepository;
    private final UsuarioRepository usuarioRepository;
    private final ReservaRepository reservaRepository;
    private final MultaRepository multaRepository;

    public DashboardService(PrestamoRepository prestamoRepository,
                            LibroRepository libroRepository,
                            UsuarioRepository usuarioRepository,
                            ReservaRepository reservaRepository,
                            MultaRepository multaRepository) {
        this.prestamoRepository = prestamoRepository;
        this.libroRepository = libroRepository;
        this.usuarioRepository = usuarioRepository;
        this.reservaRepository = reservaRepository;
        this.multaRepository = multaRepository;
    }

    public DashboardResponse getStats() {
        LocalDateTime hoyInicio = LocalDate.now().atStartOfDay();
        LocalDateTime hoyFin = LocalDate.now().atTime(LocalTime.MAX);

        return DashboardResponse.builder()
                .librosPrestadosHoy(prestamoRepository.countByFechaPrestamoBetween(hoyInicio, hoyFin))
                .librosDisponibles(libroRepository.countByEjemplaresDisponiblesGreaterThan(0))
                .estudiantesActivos(usuarioRepository.countByActivoTrue())
                .reservasPendientes(reservaRepository.countByEstado("PENDIENTE"))
                .multasPendientes(multaRepository.countByPagadaFalse())
                .totalMultas(multaRepository.totalMultasPendientes())
                .build();
    }
}
