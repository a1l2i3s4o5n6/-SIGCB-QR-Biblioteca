package com.sigcbqr.model.dto.response.dashboard;

import lombok.*;

import java.time.LocalDate;
import java.time.LocalDateTime;
import java.util.List;

@Getter @Setter @NoArgsConstructor @AllArgsConstructor @Builder
public class DashboardResumenResponse {

    private String rol;
    private LocalDateTime generadoEl;
    private LocalDate desde;
    private LocalDate hasta;
    private DashboardKpisResponse kpis;
    private List<DashboardActividadItemResponse> actividadReciente;
    private List<DashboardAlertaResponse> alertas;
    private List<DashboardSerieDiaResponse> actividadPorDia;
    private List<DashboardCategoriaResponse> prestamosPorCategoria;
    private DashboardEstadoSistemaResponse estadoSistema;
    private long notificacionesNoLeidas;
}