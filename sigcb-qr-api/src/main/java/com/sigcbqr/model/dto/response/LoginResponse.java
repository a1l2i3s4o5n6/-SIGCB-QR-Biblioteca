package com.sigcbqr.model.dto.response;

import lombok.*;

@Getter @Setter @NoArgsConstructor @AllArgsConstructor @Builder
public class LoginResponse {

    private Long id;
    private String nombre;
    private String email;
    private String rol;
    private String mensaje;
}
