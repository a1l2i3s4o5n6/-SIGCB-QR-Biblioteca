package com.sigcbqr.model.dto.response;

import lombok.*;

import java.time.LocalDateTime;

@Getter @Setter @NoArgsConstructor @AllArgsConstructor @Builder
public class UsuarioResponse {

    private Long id;
    private String nombre;
    private String email;
    private String telefono;
    private String rol;
    private Boolean activo;
    private LocalDateTime createdAt;
}
