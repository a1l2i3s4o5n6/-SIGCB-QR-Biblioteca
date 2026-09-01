package com.sigcbqr.security;

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
        repository.insertIfAbsent(jti, expiration);
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
