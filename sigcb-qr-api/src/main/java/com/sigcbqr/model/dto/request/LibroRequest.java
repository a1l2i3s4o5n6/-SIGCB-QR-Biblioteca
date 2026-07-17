package com.sigcbqr.model.dto.request;

import jakarta.validation.constraints.NotBlank;
import lombok.*;

import java.util.Set;

@Getter @Setter @NoArgsConstructor @AllArgsConstructor
public class LibroRequest {

    @NotBlank(message = "El título es obligatorio")
    private String titulo;

    private String isbn;
    private Integer anioPublicacion;
    private String edicion;
    private Integer ejemplaresTotales;
    private String ubicacion;
    private String descripcion;
    private Long categoriaId;
    private Long editorialId;
    private Set<Long> autorIds;
}
