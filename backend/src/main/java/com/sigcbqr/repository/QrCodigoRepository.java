package com.sigcbqr.repository;

import com.sigcbqr.model.entity.QrCodigo;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.List;
import java.util.Optional;

@Repository
public interface QrCodigoRepository extends JpaRepository<QrCodigo, Long> {
    List<QrCodigo> findByLibroId(Long libroId);
    Optional<QrCodigo> findByCodigo(String codigo);
    boolean existsByCodigo(String codigo);
}
