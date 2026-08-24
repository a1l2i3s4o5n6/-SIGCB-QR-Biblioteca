package com.sigcbqr.model.dto.response;

import lombok.*;

@Getter @Setter @NoArgsConstructor @AllArgsConstructor @Builder
public class InventarioResponse {

    private Long id;
    private String codigoEjemplar;
    private String estado;
    private String ubicacionEstante;
    private Long libroId;
    private String libroTitulo;
}