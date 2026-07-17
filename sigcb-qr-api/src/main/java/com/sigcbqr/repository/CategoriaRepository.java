package com.sigcbqr.repository;

import com.sigcbqr.model.entity.Categoria;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

@Repository
public interface CategoriaRepository extends JpaRepository<Categoria, Long> {
    Page<Categoria> findByActivoTrue(Pageable pageable);
    Page<Categoria> findByNombreContainingIgnoreCase(String nombre, Pageable pageable);
}
