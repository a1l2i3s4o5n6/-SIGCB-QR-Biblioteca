package com.sigcbqr.repository;

import com.sigcbqr.model.entity.Sancion;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

@Repository
public interface SancionRepository extends JpaRepository<Sancion, Long> {
    Page<Sancion> findByUsuarioId(Long usuarioId, Pageable pageable);
    Page<Sancion> findByActiva(Boolean activa, Pageable pageable);
    boolean existsByUsuarioIdAndActivaTrue(Long usuarioId);
}