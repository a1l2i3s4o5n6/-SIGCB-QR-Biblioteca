package com.sigcbqr.model.dto.response;

import lombok.*;

import java.time.LocalDateTime;

@Getter @Setter @NoArgsConstructor @AllArgsConstructor @Builder
public class PrestamoResponse {

    private Long id;
    private String usuarioNombre;
    private String libroTitulo;
    private String codigoEjemplar;
    private LocalDateTime fechaPrestamo;
    private LocalDateTime fechaVencimiento;
    private LocalDateTime fechaDevolucion;
    private String estado;
    private String observaciones;
}
