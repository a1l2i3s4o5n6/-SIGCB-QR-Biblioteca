package com.sigcbqr.security;

import com.sigcbqr.model.entity.JwtBlacklist;
import com.sigcbqr.repository.JwtBlacklistRepository;
import org.springframework.scheduling.annotation.Scheduled;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.time.LocalDateTime;

@Service
public class JwtBlacklistService {

    private final JwtBlacklistRepository repository;

    public JwtBlacklistService(JwtBlacklistRepository repository) {
        this.repository = repository;
    }

    @Transactional
    public void blacklist(String jti, LocalDateTime expiration) {
        if (!repository.existsByJti(jti)) {
            JwtBlacklist entry = JwtBlacklist.builder()
                    .jti(jti)
                    .fechaExpiracion(expiration)
                    .build();
            repository.save(entry);
        }
    }

    public boolean isBlacklisted(String jti) {
        return repository.existsByJti(jti);
    }

    @Scheduled(cron = "0 0 */6 * * *")
    @Transactional
    public void cleanExpiredEntries() {
        repository.deleteByFechaExpiracionBefore(LocalDateTime.now());
    }
}
