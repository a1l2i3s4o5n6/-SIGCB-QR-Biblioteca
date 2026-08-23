package com.sigcbqr.model.dto.request;

import jakarta.validation.constraints.NotNull;
import lombok.*;

@Getter @Setter @NoArgsConstructor @AllArgsConstructor @Builder
public class QrCodigoRequest {
    @NotNull(message = "El libro es obligatorio")
    private Long libroId;

    private String codigo;
    private String imagenUrl;
}
