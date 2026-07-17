package com.sigcbqr.model.dto.response;

import lombok.*;

@Getter @Setter @NoArgsConstructor @AllArgsConstructor @Builder
public class DashboardResponse {

    private long librosPrestadosHoy;
    private long librosDisponibles;
    private long estudiantesActivos;
    private long reservasPendientes;
    private long multasPendientes;
    private double totalMultas;
}
