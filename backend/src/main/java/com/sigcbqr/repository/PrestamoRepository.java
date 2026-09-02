package com.sigcbqr.repository;

import com.sigcbqr.model.entity.Prestamo;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.jpa.repository.query.Procedure;
import org.springframework.data.repository.query.Param;
import org.springframework.stereotype.Repository;

import java.time.LocalDateTime;
import java.util.List;

@Repository
public interface PrestamoRepository extends JpaRepository<Prestamo, Long> {
    Page<Prestamo> findByUsuarioIdOrderByFechaPrestamoDesc(Long usuarioId, Pageable pageable);
    Page<Prestamo> findByEstado(String estado, Pageable pageable);
    List<Prestamo> findByEstadoAndFechaVencimientoBefore(String estado, LocalDateTime fecha);
    long countByEstado(String estado);
    long countByEstadoAndFechaVencimientoBefore(String estado, LocalDateTime fecha);
    long countByEstadoAndFechaVencimientoBetween(String estado, LocalDateTime inicio, LocalDateTime fin);
    long countByUsuarioIdAndEstadoAndFechaVencimientoBefore(Long usuarioId, String estado, LocalDateTime fecha);
    long countByUsuarioIdAndEstadoAndFechaVencimientoBetween(Long usuarioId, String estado, LocalDateTime inicio, LocalDateTime fin);
    List<Prestamo> findByEstadoAndFechaVencimientoBetween(String estado, LocalDateTime inicio, LocalDateTime fin);

    @Query("SELECT COUNT(p) FROM Prestamo p WHERE p.fechaDevolucion >= :inicio AND p.fechaDevolucion <= :fin")
    long countByFechaDevolucionBetween(LocalDateTime inicio, LocalDateTime fin);

    @Query("SELECT FUNCTION('date', p.fechaPrestamo), COUNT(p) FROM Prestamo p " +
            "WHERE p.fechaPrestamo >= :inicio AND p.fechaPrestamo <= :fin " +
            "GROUP BY FUNCTION('date', p.fechaPrestamo) ORDER BY FUNCTION('date', p.fechaPrestamo)")
    List<Object[]> countPrestamosPorDia(LocalDateTime inicio, LocalDateTime fin);

    @Query("SELECT FUNCTION('date', p.fechaDevolucion), COUNT(p) FROM Prestamo p " +
            "WHERE p.estado = 'DEVUELTO' AND p.fechaDevolucion >= :inicio AND p.fechaDevolucion <= :fin " +
            "GROUP BY FUNCTION('date', p.fechaDevolucion) ORDER BY FUNCTION('date', p.fechaDevolucion)")
    List<Object[]> countDevolucionesPorDia(LocalDateTime inicio, LocalDateTime fin);

    @Query("SELECT p.inventario.libro.categoria.nombre, COUNT(p) FROM Prestamo p " +
            "WHERE p.inventario.libro.categoria IS NOT NULL AND p.fechaPrestamo >= :inicio AND p.fechaPrestamo <= :fin " +
            "GROUP BY p.inventario.libro.categoria.nombre ORDER BY COUNT(p) DESC")
    List<Object[]> countPrestamosPorCategoria(LocalDateTime inicio, LocalDateTime fin);

    @Query("""
        SELECT p FROM Prestamo p
        WHERE (CAST(:q AS string) IS NULL OR LOWER(p.usuario.nombre) LIKE LOWER(CONCAT('%', CAST(:q AS string), '%'))
               OR LOWER(p.inventario.libro.titulo) LIKE LOWER(CONCAT('%', CAST(:q AS string), '%'))
               OR LOWER(p.inventario.codigoEjemplar) LIKE LOWER(CONCAT('%', CAST(:q AS string), '%')))
          AND (CAST(:estado AS string) IS NULL OR p.estado = :estado)
          AND p.fechaPrestamo >= :desde
          AND p.fechaPrestamo < :hasta
        """)
    Page<Prestamo> buscarConFiltros(@Param("q") String q,
                                    @Param("estado") String estado,
                                    @Param("desde") LocalDateTime desde,
                                    @Param("hasta") LocalDateTime hasta,
                                    Pageable pageable);

    @Query("SELECT COUNT(p) FROM Prestamo p WHERE p.fechaPrestamo >= :inicio AND p.fechaPrestamo <= :fin")
    long countByFechaPrestamoBetween(LocalDateTime inicio, LocalDateTime fin);

    @Query("SELECT p FROM Prestamo p WHERE p.fechaPrestamo >= :inicio AND p.fechaPrestamo <= :fin")
    List<Prestamo> findByFechaPrestamoBetween(LocalDateTime inicio, LocalDateTime fin);

    long countByUsuarioIdAndEstado(Long usuarioId, String estado);

    @Query("SELECT COUNT(p) FROM Prestamo p WHERE p.usuario.id = :usuarioId " +
            "AND p.fechaDevolucion >= :inicio AND p.fechaDevolucion <= :fin")
    long countByUsuarioIdAndFechaDevolucionBetween(@Param("usuarioId") Long usuarioId,
                                                   @Param("inicio") LocalDateTime inicio,
                                                   @Param("fin") LocalDateTime fin);

    @Query("SELECT p FROM Prestamo p LEFT JOIN FETCH p.usuario LEFT JOIN FETCH p.inventario i LEFT JOIN FETCH i.libro " +
            "WHERE p.usuario.id = :usuarioId ORDER BY p.fechaPrestamo DESC")
    List<Prestamo> findRecientesPorUsuario(@Param("usuarioId") Long usuarioId, Pageable pageable);

    @Query("SELECT p.usuario.id, COUNT(p) as cnt FROM Prestamo p GROUP BY p.usuario.id ORDER BY cnt DESC")
    List<Object[]> findTopUsuariosByPrestamos(Pageable pageable);

    // Stored procedures
    @Procedure("sp_crear_prestamo")
    Long crearPrestamo(@Param("p_usuario_id") Long usuarioId,
                       @Param("p_inventario_id") Long inventarioId,
                       @Param("p_dias_prestamo") Integer diasPrestamo);

    @Procedure("sp_devolver_prestamo")
    Boolean devolverPrestamo(@Param("p_prestamo_id") Long prestamoId);

    @Procedure("sp_renovar_prestamo")
    Long renovarPrestamo(@Param("p_prestamo_id") Long prestamoId,
                         @Param("p_dias_renovacion") Integer diasRenovacion);

    @Procedure("sp_reporte_prestamos_diarios")
    List<Object[]> reportePrestamosDiarios(@Param("p_fecha") LocalDateTime fecha);
}
