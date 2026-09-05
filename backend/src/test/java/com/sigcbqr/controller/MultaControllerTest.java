package com.sigcbqr.controller;

import com.sigcbqr.config.SecurityConfig;
import com.sigcbqr.model.entity.Multa;
import com.sigcbqr.repository.MultaRepository;
import com.sigcbqr.security.JwtAuthenticationEntryPoint;
import com.sigcbqr.security.JwtTokenProvider;
import com.sigcbqr.security.UserPrincipal;
import com.sigcbqr.service.AuditoriaService;
import org.junit.jupiter.api.Test;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.test.autoconfigure.web.servlet.WebMvcTest;
import org.springframework.boot.test.mock.mockito.MockBean;
import org.springframework.context.annotation.Import;
import org.springframework.data.domain.PageImpl;
import org.springframework.data.domain.PageRequest;
import org.springframework.data.domain.Pageable;
import org.springframework.security.authentication.UsernamePasswordAuthenticationToken;
import org.springframework.security.core.authority.SimpleGrantedAuthority;
import org.springframework.test.web.servlet.MockMvc;

import java.math.BigDecimal;
import java.util.List;

import static org.mockito.ArgumentMatchers.any;
import static org.mockito.ArgumentMatchers.eq;
import static org.mockito.Mockito.when;
import static org.springframework.security.test.web.servlet.request.SecurityMockMvcRequestPostProcessors.authentication;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.get;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.status;

/**
 * Regresión: un ESTUDIANTE no debe poder listar multas de todos los usuarios.
 *  - GET /api/multas (listado global) solo para ADMIN/BIBLIOTECARIO (403 para estudiante).
 *  - GET /api/multas/mis (scoped) accesible para cualquier usuario autenticado usando
 *    únicamente su propio id.
 */
@WebMvcTest(MultaController.class)
@Import(SecurityConfig.class)
class MultaControllerTest {

    @Autowired
    private MockMvc mockMvc;

    @MockBean
    private MultaRepository multaRepository;

    @MockBean
    private AuditoriaService auditoriaService;

    @MockBean
    private JwtTokenProvider tokenProvider;

    @MockBean
    private JwtAuthenticationEntryPoint jwtAuthenticationEntryPoint;

    private UsernamePasswordAuthenticationToken auth(String rol, Long id) {
        UserPrincipal principal = new UserPrincipal(
                id,
                rol.equals("ADMIN") ? "admin@biblioteca.com" : "e2e.test@estudiante.com",
                "x",
                rol,
                true,
                List.of(new SimpleGrantedAuthority("ROLE_" + rol)));
        return new UsernamePasswordAuthenticationToken(principal, null, principal.getAuthorities());
    }

    private Multa multa() {
        return Multa.builder()
                .id(1L)
                .monto(new BigDecimal("1000.00"))
                .concepto("Retraso en devolución")
                .pagada(false)
                .build();
    }

    @Test
    void listadoGlobalDeMultasRechazaEstudianteCon403() throws Exception {
        mockMvc.perform(get("/api/multas")
                        .with(authentication(auth("ESTUDIANTE", 7L))))
                .andExpect(status().isForbidden());
    }

    @Test
    void listadoGlobalDeMultasPermiteStaff() throws Exception {
        when(multaRepository.findAll(any(Pageable.class)))
                .thenReturn(new PageImpl<>(List.of(multa()), PageRequest.of(0, 10), 1));

        mockMvc.perform(get("/api/multas")
                        .with(authentication(auth("ADMIN", 1L))))
                .andExpect(status().isOk());
    }

    @Test
    void misMultasUsaSoloElIdDelUsuarioAutenticado() throws Exception {
        when(multaRepository.findByUsuarioId(eq(7L), any(Pageable.class)))
                .thenReturn(new PageImpl<>(List.of(multa()), PageRequest.of(0, 10), 1));

        mockMvc.perform(get("/api/multas/mis")
                        .with(authentication(auth("ESTUDIANTE", 7L))))
                .andExpect(status().isOk());
    }

    @Test
    void multasPorUsuarioRechazaEstudianteCon403() throws Exception {
        mockMvc.perform(get("/api/multas/usuario/7")
                        .with(authentication(auth("ESTUDIANTE", 7L))))
                .andExpect(status().isForbidden());
    }
}
