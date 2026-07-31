package com.sigcbqr.security;

import jakarta.servlet.FilterChain;
import jakarta.servlet.http.Cookie;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.extension.ExtendWith;
import org.mockito.Mock;
import org.mockito.junit.jupiter.MockitoExtension;

import static org.mockito.Mockito.*;

@ExtendWith(MockitoExtension.class)
class JwtAuthenticationFilterTest {

    @Mock
    private JwtTokenProvider tokenProvider;
    @Mock
    private HttpServletRequest request;
    @Mock
    private HttpServletResponse response;
    @Mock
    private FilterChain filterChain;

    private JwtAuthenticationFilter filter;

    @BeforeEach
    void setUp() {
        filter = new JwtAuthenticationFilter(tokenProvider);
    }

    @Test
    void doFilterSinCookieContinuaLaCadena() throws Exception {
        when(request.getCookies()).thenReturn(null);
        filter.doFilterInternal(request, response, filterChain);
        verify(filterChain).doFilter(request, response);
    }

    @Test
    void doFilterSinAccessTokenCookieContinuaLaCadena() throws Exception {
        Cookie[] cookies = { new Cookie("other", "value") };
        when(request.getCookies()).thenReturn(cookies);
        when(tokenProvider.extractTokenFromCookie(request)).thenReturn(null);
        filter.doFilterInternal(request, response, filterChain);
        verify(filterChain).doFilter(request, response);
    }

    @Test
    void doFilterConAccessTokenCookieInvalido() throws Exception {
        Cookie[] cookies = { new Cookie("access_token", "invalid-token") };
        when(request.getCookies()).thenReturn(cookies);
        when(tokenProvider.extractTokenFromCookie(request)).thenReturn("invalid-token");
        when(tokenProvider.validateToken("invalid-token")).thenReturn(false);

        filter.doFilterInternal(request, response, filterChain);
        verify(filterChain).doFilter(request, response);
    }
}
