package com.sigcbqr.model.dto.response.dashboard;

import lombok.*;

@Getter @Setter @NoArgsConstructor @AllArgsConstructor @Builder
public class DashboardEstadoSistemaResponse {

    private boolean baseDeDatosOperativa;
    private boolean apiOperativa;
    private boolean qrOperativo;
    private boolean respaldoDisponible;
    private String ultimoRespaldo;
}