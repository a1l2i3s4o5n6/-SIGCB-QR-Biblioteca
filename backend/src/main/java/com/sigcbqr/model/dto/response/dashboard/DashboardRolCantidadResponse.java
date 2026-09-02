package com.sigcbqr.model.dto.response.dashboard;

import lombok.*;

@Getter @Setter @NoArgsConstructor @AllArgsConstructor @Builder
public class DashboardRolCantidadResponse {
    private String rol;
    private long cantidad;
    private double porcentaje;
}