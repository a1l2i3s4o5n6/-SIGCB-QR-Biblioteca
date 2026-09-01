package com.sigcbqr.model.dto.request;

import jakarta.validation.constraints.NotBlank;
import lombok.*;

@Getter @Setter @NoArgsConstructor @AllArgsConstructor @Builder
public class ValidarQrRequest {

    @NotBlank(message = "El código es obligatorio")
    private String codigo;
}