package com.sigcbqr.model.dto.response;

import lombok.*;

import java.util.Set;

@Getter @Setter @NoArgsConstructor @AllArgsConstructor @Builder
public class LibroResponse {

    private Long id;
    private String titulo;
    private String isbn;
    private Integer anioPublicacion;
    private String edicion;
    private Integer ejemplaresTotales;
    private Integer ejemplaresDisponibles;
    private String ubicacion;
    private String descripcion;
    private String categoria;
    private Long categoriaId;
    private String editorial;
    private Long editorialId;
    private Set<String> autores;
    private Boolean activo;
}
