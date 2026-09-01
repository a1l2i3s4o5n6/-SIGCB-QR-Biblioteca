package com.sigcbqr.security;

import com.sigcbqr.repository.JwtBlacklistRepository;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.extension.ExtendWith;
import org.mockito.Mock;
import org.mockito.junit.jupiter.MockitoExtension;

import java.time.LocalDateTime;

import static org.junit.jupiter.api.Assertions.assertFalse;
import static org.junit.jupiter.api.Assertions.assertTrue;
import static org.mockito.ArgumentMatchers.any;
import static org.mockito.ArgumentMatchers.eq;
import static org.mockito.Mockito.verify;
import static org.mockito.Mockito.when;

/**
 * Verifica REQ-NF-007: los JWT dados de baja (logout) se registran en la
 * blacklist y no pueden reutilizarse.
 */
@ExtendWith(MockitoExtension.class)
class JwtBlacklistServiceTest {

    @Mock
    private JwtBlacklistRepository repository;

    private JwtBlacklistService service;

    private static final String JTI = "jti-test-123";
    private static final LocalDateTime EXPIRATION = LocalDateTime.now().plusHours(1);

    @BeforeEach
    void setUp() {
        service = new JwtBlacklistService(repository);
    }

    @Test
    void blacklistRegistraElJTIEnElRepositorio() {
        service.blacklist(JTI, EXPIRATION);

        verify(repository).insertIfAbsent(eq(JTI), any(LocalDateTime.class));
    }

    @Test
    void isBlacklistedDevuelveTrueCuandoElJTIEstaRegistrado() {
        when(repository.existsByJti(JTI)).thenReturn(true);

        assertTrue(service.isBlacklisted(JTI));
    }

    @Test
    void isBlacklistedDevuelveFalseCuandoElJTINoEstaRegistrado() {
        when(repository.existsByJti(JTI)).thenReturn(false);

        assertFalse(service.isBlacklisted(JTI));
    }

    @Test
    void cleanExpiredEntriesEliminaLasEntradasVencidas() {
        service.cleanExpiredEntries();

        verify(repository).deleteByFechaExpiracionBefore(any(LocalDateTime.class));
    }
}