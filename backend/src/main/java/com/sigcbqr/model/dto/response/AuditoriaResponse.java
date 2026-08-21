package com.sigcbqr.model.dto.response;

import lombok.Builder;
import lombok.Getter;
import lombok.Setter;

import java.time.LocalDateTime;

@Getter @Setter @Builder
public class AuditoriaResponse {
    private Long id;
    private String usuarioNombre;
    private String accion;
    private String entidad;
    private Long entidadId;
    private String detalle;
    private LocalDateTime createdAt;
}