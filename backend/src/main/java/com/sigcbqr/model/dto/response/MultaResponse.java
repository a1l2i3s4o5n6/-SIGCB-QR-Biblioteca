package com.sigcbqr.model.dto.response;

import lombok.*;

import java.math.BigDecimal;
import java.time.LocalDateTime;

@Getter @Setter @NoArgsConstructor @AllArgsConstructor @Builder
public class MultaResponse {

    private Long id;
    private Long prestamoId;
    private String usuarioNombre;
    private BigDecimal monto;
    private Boolean pagada;
    private LocalDateTime fechaPago;
    private String concepto;
    private LocalDateTime createdAt;
}