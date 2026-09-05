package com.sigcbqr.security;

import com.sigcbqr.config.SecurityConfig;
import com.sigcbqr.controller.AuthController;
import com.sigcbqr.service.AuthService;
import com.sigcbqr.service.RateLimitService;
import org.junit.jupiter.api.DisplayName;
import org.junit.jupiter.api.Test;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.test.autoconfigure.web.servlet.WebMvcTest;
import org.springframework.boot.test.mock.mockito.MockBean;
import org.springframework.context.annotation.Import;
import org.springframework.test.web.servlet.MockMvc;

import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.get;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.post;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.status;

/**
 * Prueba de regresion de GET /api/auth/me.
 *
 * <p>Origen del defecto. SecurityConfig declaraba /api/auth/** como permitAll
 * para dejar pasar el login y el registro, que son publicos por necesidad. Pero
 * /api/auth/me cuelga de esa misma rama, de modo que una peticion SIN token
 * llegaba hasta el controlador; alli @AuthenticationPrincipal valia null y el
 * metodo lanzaba NullPointerException al invocar userPrincipal.id(). El cliente
 * recibia un 500 donde corresponde un 401.
 *
 * <p>Como se encontro, que es la parte instructiva. No lo detecto la auditoria
 * OWASP propia, que ejecuta 51 comprobaciones y las supera todas: esa auditoria
 * sondea rutas cuyo comportamiento correcto ya conoce, y /api/auth/me no estaba
 * entre ellas. Lo encontro OWASP ZAP recorriendo la especificacion OpenAPI, es
 * decir, un instrumento que NO parte de nuestras suposiciones sobre que rutas
 * merece la pena probar.
 *
 * <p>Es el tercer hallazgo de esta entrega con el mismo patron: los ocho
 * endpoints del catalogo sin autorizacion, la ausencia de proteccion CSRF y
 * ahora este. En los tres casos el instrumento propio miraba donde ya sabiamos
 * mirar.
 */
@WebMvcTest(AuthController.class)
@Import({SecurityConfig.class, JwtAuthenticationEntryPoint.class})
class AuthMeEndpointTest {

    @Autowired
    private MockMvc mockMvc;

    @MockBean
    private AuthService authService;

    // AuthController limita la tasa de login/registro; sin este doble el
    // contexto de @WebMvcTest no arranca.
    @MockBean
    private RateLimitService rateLimitService;

    @MockBean
    private JwtTokenProvider tokenProvider;

    // JwtAuthenticationEntryPoint NO se dobla: se importa el componente real.
    // Es quien escribe el 401, y con un doble la peticion no autenticada
    // terminaba en 200, que es exactamente el fallo que esta clase debe
    // detectar. Un doble aqui haria que la prueba pasara con el defecto puesto.

    @Test
    @DisplayName("GET /api/auth/me sin token responde 401, no 500")
    void meSinTokenDevuelve401YNo500() throws Exception {
        mockMvc.perform(get("/api/auth/me"))
                .andExpect(status().isUnauthorized());
    }

    @Test
    @DisplayName("El endurecimiento de /me no cierra el login, que debe seguir siendo publico")
    void loginSigueSiendoPublico() throws Exception {
        // Un cuerpo vacio es invalido, de modo que se espera 400 de validacion.
        // Lo que importa aqui NO es el 400, sino que no sea 401: si la regla de
        // /api/auth/me se hubiera escrito demasiado ancha, el login habria
        // quedado cerrado y nadie podria autenticarse.
        mockMvc.perform(post("/api/auth/login")
                        .contentType("application/json")
                        .content("{}"))
                .andExpect(status().isBadRequest());
    }

    @Test
    @DisplayName("El endurecimiento de /me no cierra el registro")
    void registroSigueSiendoPublico() throws Exception {
        mockMvc.perform(post("/api/auth/register")
                        .contentType("application/json")
                        .content("{}"))
                .andExpect(status().isBadRequest());
    }
}
