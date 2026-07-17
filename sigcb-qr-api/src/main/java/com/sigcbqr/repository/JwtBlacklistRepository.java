package com.sigcbqr.repository;

import com.sigcbqr.model.entity.JwtBlacklist;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.time.LocalDateTime;
import java.util.Optional;

@Repository
public interface JwtBlacklistRepository extends JpaRepository<JwtBlacklist, Long> {
    Optional<JwtBlacklist> findByJti(String jti);
    boolean existsByJti(String jti);
    void deleteByFechaExpiracionBefore(LocalDateTime now);
}
