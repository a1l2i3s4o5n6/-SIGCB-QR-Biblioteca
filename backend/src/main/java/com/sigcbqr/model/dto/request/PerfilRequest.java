package com.sigcbqr.model.dto.request;

import jakarta.validation.constraints.Email;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.Size;
import lombok.*;

/**
 * Datos editables del perfil del usuario autenticado.
 * El cambio de contraseña es opcional: si se envía {@code passwordNueva},
 * se exige la {@code passwordActual} correcta.
 */
@Getter @Setter @NoArgsConstructor @AllArgsConstructor
public class PerfilRequest {

    @NotBlank(message = "El nombre es obligatorio")
    @Size(max = 100, message = "El nombre es demasiado largo")
    private String nombre;

    @NotBlank(message = "El correo es obligatorio")
    @Email(message = "Correo inválido")
    @Size(max = 150, message = "El correo es demasiado largo")
    private String email;

    @Size(max = 20, message = "El teléfono es demasiado largo")
    private String telefono;

    @Size(max = 255, message = "La URL de la foto es demasiado larga")
    private String foto;

    private String passwordActual;

    @Size(max = 100, message = "La contraseña es demasiado larga")
    private String passwordNueva;
}