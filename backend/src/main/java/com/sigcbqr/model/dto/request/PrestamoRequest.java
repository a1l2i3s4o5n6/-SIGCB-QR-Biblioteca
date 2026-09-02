package com.sigcbqr.model.dto.request;

import jakarta.validation.constraints.NotNull;
import lombok.*;

@Getter @Setter @NoArgsConstructor @AllArgsConstructor
public class PrestamoRequest {

    @NotNull(message = "El usuario es obligatorio")
    private Long usuarioId;

    @NotNull(message = "El inventario es obligatorio")
    private Long inventarioId;

    /**
     * Código QR escaneado/tecleado (opcional). Si se envía, el sistema valida
     * que exista, esté activo y corresponda al libro del ejemplar prestado.
     */
    private String codigoQr;
}
