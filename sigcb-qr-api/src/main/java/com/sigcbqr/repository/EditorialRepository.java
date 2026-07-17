package com.sigcbqr.repository;

import com.sigcbqr.model.entity.Editorial;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

@Repository
public interface EditorialRepository extends JpaRepository<Editorial, Long> {
    Page<Editorial> findByActivoTrue(Pageable pageable);
    Page<Editorial> findByNombreContainingIgnoreCase(String nombre, Pageable pageable);
}
