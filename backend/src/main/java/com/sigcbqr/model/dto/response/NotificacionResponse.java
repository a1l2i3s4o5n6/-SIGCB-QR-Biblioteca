package com.sigcbqr.model.dto.response;

import lombok.*;

import java.time.LocalDateTime;

@Getter @Setter @NoArgsConstructor @AllArgsConstructor @Builder
public class NotificacionResponse {

    private Long id;
    private Long usuarioId;
    private String usuarioNombre;
    private String titulo;
    private String mensaje;
    private Boolean leida;
    private String tipo;
    private LocalDateTime createdAt;
}