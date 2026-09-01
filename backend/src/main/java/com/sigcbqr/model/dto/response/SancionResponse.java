package com.sigcbqr.model.dto.response;

import lombok.*;

import java.time.LocalDateTime;

@Getter @Setter @NoArgsConstructor @AllArgsConstructor @Builder
public class SancionResponse {

    private Long id;
    private Long usuarioId;
    private String usuarioNombre;
    private String tipo;
    private String motivo;
    private LocalDateTime fechaInicio;
    private LocalDateTime fechaFin;
    private Boolean activa;
    private LocalDateTime createdAt;
}