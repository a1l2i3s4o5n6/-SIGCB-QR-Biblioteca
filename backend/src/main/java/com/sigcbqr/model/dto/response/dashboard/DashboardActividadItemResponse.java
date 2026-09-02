package com.sigcbqr.model.dto.response.dashboard;

import lombok.*;

import java.time.LocalDateTime;

@Getter @Setter @NoArgsConstructor @AllArgsConstructor @Builder
public class DashboardActividadItemResponse {

    private Long id;
    private String accion;
    private String entidad;
    private String detalle;
    private String usuarioNombre;
    private LocalDateTime createdAt;
}