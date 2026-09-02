package com.sigcbqr.model.dto.response.dashboard;

import lombok.*;

import java.time.LocalDateTime;

@Getter @Setter @NoArgsConstructor @AllArgsConstructor @Builder
public class DashboardAlertaResponse {

    private String tipo;
    private String prioridad;
    private String descripcion;
    private String detalle;
    private LocalDateTime fecha;
    private String accion;
    private String url;
}