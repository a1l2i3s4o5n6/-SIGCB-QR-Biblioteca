package com.sigcbqr.repository;

import com.sigcbqr.model.entity.Auditoria;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;
import org.springframework.stereotype.Repository;

import java.time.LocalDateTime;
import java.util.List;

@Repository
public interface AuditoriaRepository extends JpaRepository<Auditoria, Long> {
    Page<Auditoria> findByCreatedAtBetween(LocalDateTime inicio, LocalDateTime fin, Pageable pageable);
    Page<Auditoria> findByUsuarioIdAndCreatedAtBetween(Long usuarioId, LocalDateTime inicio, LocalDateTime fin, Pageable pageable);
    Page<Auditoria> findByUsuarioId(Long usuarioId, Pageable pageable);

    @Query("SELECT a FROM Auditoria a LEFT JOIN FETCH a.usuario WHERE a.accion NOT IN ('LOGIN', 'VALIDAR') ORDER BY a.createdAt DESC")
    List<Auditoria> findActividadReciente(Pageable pageable);

    @Query("SELECT COUNT(DISTINCT a.entidadId) FROM Auditoria a " +
            "WHERE a.entidad LIKE '%QR%' AND a.accion = :accion " +
            "AND a.createdAt >= :inicio AND a.createdAt <= :fin")
    long countQrEventos(@Param("accion") String accion,
                        @Param("inicio") LocalDateTime inicio,
                        @Param("fin") LocalDateTime fin);
}
