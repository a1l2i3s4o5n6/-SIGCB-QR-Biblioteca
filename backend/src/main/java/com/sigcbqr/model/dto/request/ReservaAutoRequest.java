package com.sigcbqr.model.dto.request;

import jakarta.validation.constraints.NotNull;
import lombok.*;

@Getter @Setter @NoArgsConstructor @AllArgsConstructor
public class ReservaAutoRequest {

    @NotNull(message = "El libro es obligatorio")
    private Long libroId;
}
