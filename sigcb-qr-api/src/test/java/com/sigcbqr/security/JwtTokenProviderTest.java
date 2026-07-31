package com.sigcbqr.security;

import com.sigcbqr.repository.JwtBlacklistRepository;
import io.jsonwebtoken.Claims;
import io.jsonwebtoken.Jwts;
import io.jsonwebtoken.io.Decoders;
import io.jsonwebtoken.security.Keys;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import org.springframework.test.util.ReflectionTestUtils;

import javax.crypto.SecretKey;

import static org.junit.jupiter.api.Assertions.*;
import static org.mockito.ArgumentMatchers.any;
import static org.mockito.Mockito.mock;
import static org.mockito.Mockito.when;

class JwtTokenProviderTest {

    private static final String SECRET = "dGVzdFNlY3JldEtleUZvclNpZ2NiUXJQcm9qZWN0VGVzdGluZ1B1cnBvc2VzT25seTEyMw==";
    private static final long EXPIRATION = 3600000L;
    private static final String ISSUER = "sigcbqr-api-test";
    private static final String AUDIENCE = "sigcbqr-frontend-test";

    private JwtTokenProvider tokenProvider;
    private SecretKey secretKey;

    @BeforeEach
    void setUp() {
        JwtBlacklistRepository blacklistRepository = mock(JwtBlacklistRepository.class);
        when(blacklistRepository.existsByJti(any())).thenReturn(false);
        JwtBlacklistService blacklistService = new JwtBlacklistService(blacklistRepository);
        tokenProvider = new JwtTokenProvider(SECRET, EXPIRATION, ISSUER, AUDIENCE, false, blacklistService);
        secretKey = Keys.hmacShaKeyFor(Decoders.BASE64.decode(SECRET));
    }

    @Test
    void generaTokenConTodosLosClaimsEstandar() {
        String token = tokenProvider.generateToken(1L, "test@test.com", "ADMIN");

        Claims claims = Jwts.parser()
                .verifyWith(secretKey)
                .build()
                .parseSignedClaims(token)
                .getPayload();

        assertNotNull(claims.getId(), "Debería tener jti");
        assertEquals(ISSUER, claims.getIssuer(), "Debería tener iss");
        assertEquals("1", claims.getSubject(), "Debería tener sub con userId");
        assertTrue(claims.getAudience().contains(AUDIENCE), "Debería tener aud");
        assertEquals("test@test.com", claims.get("email"), "Debería tener email");
        assertEquals("ADMIN", claims.get("rol"), "Debería tener rol");
        assertNotNull(claims.getIssuedAt(), "Debería tener iat");
        assertNotNull(claims.getNotBefore(), "Debería tener nbf");
        assertNotNull(claims.getExpiration(), "Debería tener exp");
    }

    @Test
    void validateTokenConTokenValido() {
        String token = tokenProvider.generateToken(1L, "test@test.com", "ADMIN");
        assertTrue(tokenProvider.validateToken(token));
    }

    @Test
    void getUserIdFromToken() {
        String token = tokenProvider.generateToken(42L, "user@test.com", "ESTUDIANTE");
        assertEquals(42L, tokenProvider.getUserIdFromToken(token));
    }

    @Test
    void getEmailFromToken() {
        String token = tokenProvider.generateToken(1L, "email@test.com", "ESTUDIANTE");
        assertEquals("email@test.com", tokenProvider.getEmailFromToken(token));
    }

    @Test
    void getRolFromToken() {
        String token = tokenProvider.generateToken(1L, "admin@test.com", "ADMIN");
        assertEquals("ADMIN", tokenProvider.getRolFromToken(token));
    }
}
