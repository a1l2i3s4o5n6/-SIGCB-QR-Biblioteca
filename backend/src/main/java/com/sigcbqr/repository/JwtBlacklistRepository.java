package com.sigcbqr.repository;

import com.sigcbqr.model.entity.JwtBlacklist;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Modifying;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;
import org.springframework.stereotype.Repository;

import java.time.LocalDateTime;
import java.util.Optional;

@Repository
public interface JwtBlacklistRepository extends JpaRepository<JwtBlacklist, Long> {
    Optional<JwtBlacklist> findByJti(String jti);
    boolean existsByJti(String jti);
    void deleteByFechaExpiracionBefore(LocalDateTime now);

    @Modifying
    @Query(value = "INSERT INTO jwt_blacklist (jti, fecha_expiracion) VALUES (:jti, :expiration) ON CONFLICT (jti) DO NOTHING",
            nativeQuery = true)
    int insertIfAbsent(@Param("jti") String jti, @Param("expiration") LocalDateTime expiration);
}
