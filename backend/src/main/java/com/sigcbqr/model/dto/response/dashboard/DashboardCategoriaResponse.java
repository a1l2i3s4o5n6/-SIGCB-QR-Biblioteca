package com.sigcbqr.model.dto.response.dashboard;

import lombok.*;

@Getter @Setter @NoArgsConstructor @AllArgsConstructor @Builder
public class DashboardCategoriaResponse {

    private String categoria;
    private long cantidad;
    private double porcentaje;
}