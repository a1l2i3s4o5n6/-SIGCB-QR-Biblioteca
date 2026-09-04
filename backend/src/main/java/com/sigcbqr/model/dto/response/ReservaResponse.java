package com.sigcbqr.model.dto.response;

import lombok.*;

import java.time.LocalDateTime;

@Getter @Setter @NoArgsConstructor @AllArgsConstructor @Builder
public class ReservaResponse {

    private Long id;
    private Long usuarioId;
    private String usuarioNombre;
    private Long libroId;
    private String libroTitulo;
    private LocalDateTime fechaReserva;
    private LocalDateTime fechaVencimiento;
    private String estado;
    private Integer posicionLista;
}