package com.sigcbqr.repository;

import com.sigcbqr.model.entity.Prestamo;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.stereotype.Repository;

import java.time.LocalDateTime;
import java.util.List;

@Repository
public interface PrestamoRepository extends JpaRepository<Prestamo, Long> {
    Page<Prestamo> findByUsuarioIdOrderByFechaPrestamoDesc(Long usuarioId, Pageable pageable);
    Page<Prestamo> findByEstado(String estado, Pageable pageable);
    List<Prestamo> findByEstadoAndFechaVencimientoBefore(String estado, LocalDateTime fecha);
    long countByEstado(String estado);

    @Query("SELECT COUNT(p) FROM Prestamo p WHERE p.fechaPrestamo >= :inicio AND p.fechaPrestamo <= :fin")
    long countByFechaPrestamoBetween(LocalDateTime inicio, LocalDateTime fin);

    @Query("SELECT p FROM Prestamo p WHERE p.fechaPrestamo >= :inicio AND p.fechaPrestamo <= :fin")
    List<Prestamo> findByFechaPrestamoBetween(LocalDateTime inicio, LocalDateTime fin);

    long countByUsuarioIdAndEstado(Long usuarioId, String estado);

    @Query("SELECT p.usuario.id, COUNT(p) as cnt FROM Prestamo p GROUP BY p.usuario.id ORDER BY cnt DESC")
    List<Object[]> findTopUsuariosByPrestamos(Pageable pageable);
}
