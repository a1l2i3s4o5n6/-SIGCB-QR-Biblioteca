package com.sigcbqr.repository;

import com.sigcbqr.model.entity.Libro;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.stereotype.Repository;

import java.util.List;

@Repository
public interface LibroRepository extends JpaRepository<Libro, Long> {
    Page<Libro> findByActivoTrue(Pageable pageable);
    Page<Libro> findByTituloContainingIgnoreCase(String titulo, Pageable pageable);
    Page<Libro> findByCategoriaId(Long categoriaId, Pageable pageable);
    long countByActivoTrue();
    long countByEjemplaresDisponiblesGreaterThan(int min);

    @Query("SELECT l FROM Libro l WHERE l.ejemplaresDisponibles > 0 AND l.activo = true")
    List<Libro> findDisponibles();

    @Query("SELECT l FROM Libro l ORDER BY (l.ejemplaresTotales - l.ejemplaresDisponibles) DESC")
    List<Libro> findMasPrestados(Pageable pageable);
}
