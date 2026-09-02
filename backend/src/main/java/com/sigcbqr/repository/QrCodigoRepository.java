package com.sigcbqr.repository;

import com.sigcbqr.model.entity.QrCodigo;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.stereotype.Repository;

import java.time.LocalDateTime;
import java.util.List;
import java.util.Optional;

@Repository
public interface QrCodigoRepository extends JpaRepository<QrCodigo, Long> {
    List<QrCodigo> findByLibroId(Long libroId);
    Optional<QrCodigo> findByCodigo(String codigo);
    boolean existsByCodigo(String codigo);
    long countByActivo(Boolean activo);

    @Query("SELECT FUNCTION('date', q.createdAt), COUNT(q) FROM QrCodigo q " +
            "WHERE q.createdAt >= :inicio AND q.createdAt <= :fin " +
            "GROUP BY FUNCTION('date', q.createdAt) ORDER BY FUNCTION('date', q.createdAt)")
    List<Object[]> countPorDia(LocalDateTime inicio, LocalDateTime fin);
}
