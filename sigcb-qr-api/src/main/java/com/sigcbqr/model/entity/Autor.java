package com.sigcbqr.model.entity;

import jakarta.persistence.*;
import lombok.*;

import java.time.LocalDateTime;

@Entity
@Table(name = "autores")
@Getter @Setter @NoArgsConstructor @AllArgsConstructor @Builder
public class Autor {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @Column(nullable = false, length = 100)
    private String nombre;

    @Column(nullable = false, length = 100)
    private String apellido;

    @Column(length = 100)
    private String nacionalidad;

    private Boolean activo;

    @Column(name = "created_at")
    private LocalDateTime createdAt;

    public String getNombreCompleto() {
        return nombre + " " + apellido;
    }

    @PrePersist
    protected void onCreate() {
        createdAt = LocalDateTime.now();
        if (activo == null) activo = true;
    }
}
