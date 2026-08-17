package com.sigcbqr.repository;

import com.sigcbqr.model.entity.Reserva;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.List;

@Repository
public interface ReservaRepository extends JpaRepository<Reserva, Long> {
    Page<Reserva> findByUsuarioId(Long usuarioId, Pageable pageable);
    List<Reserva> findByEstado(String estado);
    long countByEstado(String estado);
    long countByUsuarioIdAndEstado(Long usuarioId, String estado);
    boolean existsByLibroIdAndEstado(Long libroId, String estado);
}
