package com.sigcbqr.model.dto.response;

import lombok.*;

import java.time.LocalDateTime;

@Getter @Setter @NoArgsConstructor @AllArgsConstructor @Builder
public class QrCodigoResponse {
    private Long id;
    private Long libroId;
    private String libroTitulo;
    private String codigo;
    private String imagenUrl;
    private Boolean activo;
    private LocalDateTime createdAt;
}
