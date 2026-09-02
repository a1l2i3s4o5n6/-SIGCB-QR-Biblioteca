package com.sigcbqr.model.dto.response.dashboard;

import lombok.*;

@Getter @Setter @NoArgsConstructor @AllArgsConstructor @Builder
public class DashboardSerieDiaResponse {

    private String fecha;
    private long prestamos;
    private long devoluciones;
    private long reservas;
    private long qr;
}