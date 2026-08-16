package com.sigcbqr.repository;

import com.sigcbqr.model.entity.Auditoria;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.time.LocalDateTime;

@Repository
public interface AuditoriaRepository extends JpaRepository<Auditoria, Long> {
    Page<Auditoria> findByCreatedAtBetween(LocalDateTime inicio, LocalDateTime fin, Pageable pageable);
    Page<Auditoria> findByUsuarioId(Long usuarioId, Pageable pageable);
}
