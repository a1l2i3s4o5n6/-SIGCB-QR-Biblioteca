package com.sigcbqr.model.entity;

import jakarta.persistence.*;
import lombok.*;

import java.time.LocalDateTime;

@Entity
@Table(name = "qr_codigos")
@Getter @Setter @NoArgsConstructor @AllArgsConstructor @Builder
public class QrCodigo {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "libro_id", nullable = false)
    private Libro libro;

    @Column(nullable = false, unique = true)
    private String codigo;

    @Column(name = "imagen_url")
    private String imagenUrl;

    private Boolean activo;

    @Column(name = "created_at")
    private LocalDateTime createdAt;

    @PrePersist
    protected void onCreate() {
        createdAt = LocalDateTime.now();
        if (activo == null) activo = true;
    }
}
