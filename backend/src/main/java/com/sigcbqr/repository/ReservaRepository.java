package com.sigcbqr.repository;

import com.sigcbqr.model.entity.Reserva;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.EntityGraph;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;
import org.springframework.stereotype.Repository;

import java.time.LocalDateTime;
import java.util.List;

@Repository
public interface ReservaRepository extends JpaRepository<Reserva, Long> {
    Page<Reserva> findByUsuarioId(Long usuarioId, Pageable pageable);
    List<Reserva> findByEstado(String estado);
    long countByEstado(String estado);
    long countByCreatedAtBetween(LocalDateTime inicio, LocalDateTime fin);
    long countByUsuarioIdAndEstado(Long usuarioId, String estado);

    @Query("SELECT FUNCTION('date', r.createdAt), COUNT(r) FROM Reserva r " +
            "WHERE r.createdAt >= :inicio AND r.createdAt <= :fin " +
            "GROUP BY FUNCTION('date', r.createdAt) ORDER BY FUNCTION('date', r.createdAt)")
    List<Object[]> countReservasPorDia(LocalDateTime inicio, LocalDateTime fin);
    boolean existsByLibroIdAndEstado(Long libroId, String estado);

    @Override
    @EntityGraph(attributePaths = {"usuario", "libro"})
    Page<Reserva> findAll(Pageable pageable);

    @EntityGraph(attributePaths = {"usuario", "libro"})
    @Query("""
        SELECT r FROM Reserva r
        WHERE (CAST(:q AS string) IS NULL OR LOWER(r.usuario.nombre) LIKE LOWER(CONCAT('%', CAST(:q AS string), '%'))
               OR LOWER(r.libro.titulo) LIKE LOWER(CONCAT('%', CAST(:q AS string), '%')))
          AND (CAST(:estado AS string) IS NULL OR r.estado = :estado)
        """)
    Page<Reserva> buscarConFiltros(@Param("q") String q,
                                   @Param("estado") String estado,
                                   Pageable pageable);
}
