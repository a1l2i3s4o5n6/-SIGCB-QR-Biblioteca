package com.sigcbqr.security;

import com.sigcbqr.repository.JwtBlacklistRepository;
import jakarta.servlet.http.Cookie;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.DisplayName;
import org.junit.jupiter.api.Test;

import static org.junit.jupiter.api.Assertions.assertEquals;
import static org.junit.jupiter.api.Assertions.assertTrue;
import static org.mockito.ArgumentMatchers.any;
import static org.mockito.Mockito.mock;
import static org.mockito.Mockito.when;

/**
 * Prueba de regresion de la defensa contra CSRF (ADR-0010).
 *
 * <p>Por que existe esta clase. SecurityConfig deshabilita la proteccion CSRF de
 * Spring. Eso es inocuo en una API que autentica por cabecera Authorization,
 * porque un sitio de terceros no puede hacer que el navegador anada esa
 * cabecera. Pero este sistema autentica por COOKIE (ADR-0003), y el navegador
 * adjunta la cookie de forma automatica tambien en peticiones originadas por
 * otro sitio. Lo unico que impide el ataque es el atributo SameSite=Strict.
 *
 * <p>Es decir: toda la defensa depende de una linea en JwtTokenProvider. Si
 * alguien la retira en una refactorizacion, el sistema queda expuesto y nada lo
 * avisa. Estas pruebas son ese aviso.
 *
 * <p>Se comprueban las DOS cookies. Es tentador vigilar solo la de acceso, pero
 * la de cierre de sesion importa igual: si le faltase el atributo, un tercero
 * podria forzar el cierre de sesion de un usuario desde otra pagina.
 */
class CsrfDefenseTest {

    private static final String SECRET =
            "dGVzdFNlY3JldEtleUZvclNpZ2NiUXJQcm9qZWN0VGVzdGluZ1B1cnBvc2VzT25seTEyMw==";
    private static final long EXPIRATION = 3600000L;
    private static final String ISSUER = "sigcbqr-api-test";
    private static final String AUDIENCE = "sigcbqr-frontend-test";

    private JwtTokenProvider tokenProvider;

    @BeforeEach
    void setUp() {
        JwtBlacklistRepository blacklistRepository = mock(JwtBlacklistRepository.class);
        when(blacklistRepository.existsByJti(any())).thenReturn(false);
        JwtBlacklistService blacklistService = new JwtBlacklistService(blacklistRepository);
        tokenProvider = new JwtTokenProvider(
                SECRET, EXPIRATION, ISSUER, AUDIENCE, false, blacklistService);
    }

    @Test
    @DisplayName("La cookie de acceso lleva SameSite=Strict; es la unica defensa contra CSRF")
    void cookieDeAccesoLlevaSameSiteStrict() {
        Cookie cookie = tokenProvider.createAccessTokenCookie(
                tokenProvider.generateToken(1L, "test@test.com", "ADMIN"));

        assertEquals("Strict", cookie.getAttribute("SameSite"),
                "Sin SameSite=Strict la API queda expuesta a CSRF: la proteccion "
                        + "de Spring esta deshabilitada (ADR-0010) y el token viaja en cookie. "
                        + "Si se retira este atributo hay que habilitar CSRF en SecurityConfig.");
    }

    @Test
    @DisplayName("La cookie de acceso lleva HttpOnly; es la defensa contra XSS (ADR-0003)")
    void cookieDeAccesoLlevaHttpOnly() {
        Cookie cookie = tokenProvider.createAccessTokenCookie(
                tokenProvider.generateToken(1L, "test@test.com", "ADMIN"));

        assertTrue(cookie.isHttpOnly(),
                "Sin HttpOnly el token seria legible desde JavaScript y un XSS bastaria "
                        + "para robarlo, que es justo lo que el ADR-0003 evita.");
    }

    @Test
    @DisplayName("La cookie de cierre de sesion tambien lleva SameSite=Strict")
    void cookieDeCierreDeSesionLlevaSameSiteStrict() {
        Cookie cookie = tokenProvider.createLogoutCookie();

        assertEquals("Strict", cookie.getAttribute("SameSite"),
                "Sin el atributo, un tercero podria forzar el cierre de sesion de un "
                        + "usuario desde otra pagina.");
    }

    @Test
    @DisplayName("La cookie de cierre de sesion caduca de inmediato (maxAge = 0)")
    void cookieDeCierreDeSesionCaducaDeInmediato() {
        Cookie cookie = tokenProvider.createLogoutCookie();

        assertEquals(0, cookie.getMaxAge(),
                "Un maxAge distinto de 0 dejaria la cookie viva en el navegador tras "
                        + "cerrar sesion.");
        assertTrue(cookie.isHttpOnly(),
                "La cookie de borrado debe conservar los mismos atributos de seguridad "
                        + "que la que sustituye.");
    }
}
