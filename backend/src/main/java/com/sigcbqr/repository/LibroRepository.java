package com.sigcbqr.repository;

import com.sigcbqr.model.entity.Libro;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.jpa.repository.query.Procedure;
import org.springframework.data.repository.query.Param;
import org.springframework.stereotype.Repository;

import java.util.List;

@Repository
public interface LibroRepository extends JpaRepository<Libro, Long> {
    Page<Libro> findByActivoTrue(Pageable pageable);
    Page<Libro> findByTituloContainingIgnoreCase(String titulo, Pageable pageable);
    Page<Libro> findByCategoriaId(Long categoriaId, Pageable pageable);
    long countByActivoTrue();
    long countByEjemplaresDisponiblesGreaterThan(int min);

    @Query("""
        SELECT l FROM Libro l
        WHERE l.activo = true
          AND (CAST(:q AS string) IS NULL OR LOWER(l.titulo) LIKE LOWER(CONCAT('%', CAST(:q AS string), '%'))
               OR LOWER(COALESCE(l.isbn, '')) LIKE LOWER(CONCAT('%', CAST(:q AS string), '%')))
          AND (:categoriaId IS NULL OR l.categoria.id = :categoriaId)
          AND (:editorialId IS NULL OR l.editorial.id = :editorialId)
          AND (:anio IS NULL OR l.anioPublicacion = :anio)
          AND (:soloDisponibles = false OR l.ejemplaresDisponibles > 0)
        """)
    Page<Libro> buscarConFiltros(@Param("q") String q,
                                 @Param("categoriaId") Long categoriaId,
                                 @Param("editorialId") Long editorialId,
                                 @Param("anio") Integer anio,
                                 @Param("soloDisponibles") boolean soloDisponibles,
                                 Pageable pageable);

    @Query("SELECT l FROM Libro l WHERE l.ejemplaresDisponibles > 0 AND l.activo = true")
    List<Libro> findDisponibles();

    @Query("SELECT l FROM Libro l ORDER BY (l.ejemplaresTotales - l.ejemplaresDisponibles) DESC")
    List<Libro> findMasPrestados(Pageable pageable);

    // Stored procedures
    @Procedure("sp_reporte_libros_mas_prestados")
    List<Object[]> reporteLibrosMasPrestados(@Param("p_limit") Integer limit);

    @Procedure("fn_libros_disponibles")
    List<Object[]> fnLibrosDisponibles();
}
