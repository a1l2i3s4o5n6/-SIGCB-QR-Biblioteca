package com.sigcbqr.repository;

import com.sigcbqr.model.entity.Multa;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.stereotype.Repository;

import java.time.LocalDateTime;
import java.util.List;

@Repository
public interface MultaRepository extends JpaRepository<Multa, Long> {
    Page<Multa> findByUsuarioId(Long usuarioId, Pageable pageable);
    List<Multa> findByPagadaFalse();
    long countByPagadaFalse();

    @Query("SELECT COALESCE(SUM(m.monto), 0) FROM Multa m WHERE m.pagada = false")
    double totalMultasPendientes();

    @Query("SELECT COALESCE(SUM(m.monto), 0) FROM Multa m WHERE m.pagada = true AND m.fechaPago BETWEEN :inicio AND :fin")
    double totalCobradoEntre(LocalDateTime inicio, LocalDateTime fin);
}
