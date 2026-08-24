package com.sigcbqr.repository;

import com.sigcbqr.model.entity.Usuario;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;
import org.springframework.stereotype.Repository;

import java.util.Optional;

@Repository
public interface UsuarioRepository extends JpaRepository<Usuario, Long> {
    Optional<Usuario> findByEmail(String email);
    boolean existsByEmail(String email);
    long countByActivoTrue();

    @Query("""
        SELECT u FROM Usuario u
        WHERE (CAST(:q AS string) IS NULL OR LOWER(u.nombre) LIKE LOWER(CONCAT('%', CAST(:q AS string), '%'))
               OR LOWER(u.email) LIKE LOWER(CONCAT('%', CAST(:q AS string), '%'))
               OR LOWER(COALESCE(u.telefono, '')) LIKE LOWER(CONCAT('%', CAST(:q AS string), '%')))
          AND (CAST(:rol AS string) IS NULL OR u.rol.nombre = :rol)
          AND (:activo IS NULL OR u.activo = :activo)
        """)
    Page<Usuario> buscarConFiltros(@Param("q") String q,
                                   @Param("rol") String rol,
                                   @Param("activo") Boolean activo,
                                   Pageable pageable);
}
