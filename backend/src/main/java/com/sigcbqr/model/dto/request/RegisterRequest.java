package com.sigcbqr.model.dto.request;

import jakarta.validation.constraints.Email;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.Size;
import lombok.*;

/**
 * Datos de entrada del registro de usuario.
 *
 * <p>Los limites maximos NO son decorativos y deben coincidir con los anchos de
 * columna de la tabla {@code usuarios} (V1__schema.sql): nombre VARCHAR(100),
 * email VARCHAR(150), telefono VARCHAR(20).
 *
 * <p>Sin ellos, una cadena mas larga que la columna superaba la validacion y
 * fallaba al insertar, con PSQLException "value too long for type character
 * varying" convertida en 500. Lo detecto OWASP ZAP con su sonda de desbordamiento
 * de bufer. Un dato de entrada demasiado largo es un error del cliente y debe
 * responderse 400 con el motivo, no 500.
 *
 * <p>Si alguna de esas columnas cambia de ancho, estos limites cambian con ella.
 */
@Getter @Setter @NoArgsConstructor @AllArgsConstructor
public class RegisterRequest {

    @NotBlank(message = "El nombre es obligatorio")
    @Size(max = 100, message = "El nombre no puede superar los 100 caracteres")
    private String nombre;

    @NotBlank(message = "El correo es obligatorio")
    @Email(message = "Formato de correo inválido")
    @Size(max = 150, message = "El correo no puede superar los 150 caracteres")
    private String email;

    @NotBlank(message = "La contraseña es obligatoria")
    @Size(min = 6, max = 72, message = "La contraseña debe tener entre 6 y 72 caracteres")
    private String password;

    @Size(max = 20, message = "El teléfono no puede superar los 20 caracteres")
    private String telefono;

    private Long rolId;
}
