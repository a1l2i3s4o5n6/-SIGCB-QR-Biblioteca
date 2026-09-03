package com.sigcbqr.controller;

import com.sigcbqr.config.SecurityConfig;
import com.sigcbqr.model.dto.request.ReservaRequest;
import com.sigcbqr.model.entity.Libro;
import com.sigcbqr.model.entity.Reserva;
import com.sigcbqr.model.entity.Usuario;
import com.sigcbqr.repository.LibroRepository;
import com.sigcbqr.repository.ReservaRepository;
import com.sigcbqr.repository.UsuarioRepository;
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
import org.springframework.http.MediaType;
import org.springframework.security.authentication.UsernamePasswordAuthenticationToken;
import org.springframework.security.core.authority.SimpleGrantedAuthority;
import org.springframework.test.web.servlet.MockMvc;

import java.time.LocalDateTime;
import java.util.List;

import static org.mockito.ArgumentMatchers.any;
import static org.mockito.ArgumentMatchers.eq;
import static org.mockito.Mockito.when;
import static org.springframework.security.test.web.servlet.request.SecurityMockMvcRequestPostProcessors.authentication;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.get;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.post;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.status;

/**
 * Regresión del flujo "solicitar préstamo" desde QR:
 *  - El listado global de reservas es solo para staff (403 para el estudiante).
 *  - El estudiante puede ver SUS reservas via /mis.
 *  - El estudiante puede crear su propia reserva (200) pero no una para otro usuario (403).
 */
@WebMvcTest(ReservaController.class)
@Import(SecurityConfig.class)
class ReservaControllerTest {

    @Autowired
    private MockMvc mockMvc;

    @MockBean
    private ReservaRepository reservaRepository;

    @MockBean
    private UsuarioRepository usuarioRepository;

    @MockBean
    private LibroRepository libroRepository;

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

    private Reserva reserva() {
        Usuario u = Usuario.builder().id(7L).nombre("Estudiante").build();
        Libro l = Libro.builder().id(1L).titulo("Libro").build();
        return Reserva.builder()
                .id(1L)
                .usuario(u)
                .libro(l)
                .fechaReserva(LocalDateTime.now())
                .fechaVencimiento(LocalDateTime.now().plusDays(2))
                .estado("PENDIENTE")
                .build();
    }

    @Test
    void listadoGlobalDeReservasRechazaEstudianteCon403() throws Exception {
        mockMvc.perform(get("/api/reservas")
                        .with(authentication(auth("ESTUDIANTE", 7L))))
                .andExpect(status().isForbidden());
    }

    @Test
    void listadoGlobalDeReservasPermiteStaff() throws Exception {
        when(reservaRepository.findAll(any(Pageable.class)))
                .thenReturn(new PageImpl<>(List.of(reserva()), PageRequest.of(0, 10), 1));

        mockMvc.perform(get("/api/reservas")
                        .with(authentication(auth("BIBLIOTECARIO", 2L))))
                .andExpect(status().isOk());
    }

    @Test
    void estudiantePuedeVerSusReservas() throws Exception {
        when(reservaRepository.findByUsuarioId(eq(7L), any(Pageable.class)))
                .thenReturn(new PageImpl<>(List.of(reserva()), PageRequest.of(0, 10), 1));

        mockMvc.perform(get("/api/reservas/mis")
                        .with(authentication(auth("ESTUDIANTE", 7L))))
                .andExpect(status().isOk());
    }

    @Test
    void estudiantePuedeCrearSuPropiaReserva() throws Exception {
        when(usuarioRepository.findById(7L)).thenReturn(java.util.Optional.of(reserva().getUsuario()));
        when(libroRepository.findById(1L)).thenReturn(java.util.Optional.of(reserva().getLibro()));
        when(reservaRepository.existsByLibroIdAndEstado(1L, "PENDIENTE")).thenReturn(false);
        when(reservaRepository.save(any(Reserva.class))).thenReturn(reserva());

        ReservaRequest request = new ReservaRequest(7L, 1L);
        mockMvc.perform(post("/api/reservas")
                        .with(authentication(auth("ESTUDIANTE", 7L)))
                        .contentType(MediaType.APPLICATION_JSON)
                        .content("{\"usuarioId\":7,\"libroId\":1}"))
                .andExpect(status().isOk());
    }

    @Test
    void estudianteNoPuedeCrearReservaParaOtroUsuario() throws Exception {
        mockMvc.perform(post("/api/reservas")
                        .with(authentication(auth("ESTUDIANTE", 7L)))
                        .contentType(MediaType.APPLICATION_JSON)
                        .content("{\"usuarioId\":99,\"libroId\":1}"))
                .andExpect(status().isForbidden());
    }
}
