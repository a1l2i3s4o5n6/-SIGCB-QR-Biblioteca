package com.sigcbqr.repository;

import com.sigcbqr.model.entity.Inventario;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.List;
import java.util.Optional;

@Repository
public interface InventarioRepository extends JpaRepository<Inventario, Long> {
    List<Inventario> findByLibroId(Long libroId);
    Optional<Inventario> findByCodigoEjemplar(String codigoEjemplar);
    List<Inventario> findByEstado(String estado);
    long countByLibroIdAndEstado(Long libroId, String estado);
    long countByEstado(String estado);
}
