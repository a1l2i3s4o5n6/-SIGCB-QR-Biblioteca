package com.sigcbqr.controller;

import com.sigcbqr.model.entity.Auditoria;
import com.sigcbqr.repository.AuditoriaRepository;
import com.sigcbqr.security.JwtTokenProvider;
import org.junit.jupiter.api.Test;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.test.autoconfigure.web.servlet.WebMvcTest;
import org.springframework.boot.test.mock.mockito.MockBean;
import org.springframework.data.domain.PageImpl;
import org.springframework.data.domain.Pageable;
import org.springframework.data.domain.PageRequest;
import org.springframework.security.test.context.support.WithMockUser;
import org.springframework.test.web.servlet.MockMvc;

import java.time.LocalDateTime;
import java.util.List;

import static org.mockito.ArgumentMatchers.any;
import static org.mockito.ArgumentMatchers.eq;
import static org.mockito.Mockito.when;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.get;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.jsonPath;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.status;

@WebMvcTest(AuditoriaController.class)
class AuditoriaControllerTest {

    @Autowired
    private MockMvc mockMvc;

    @MockBean
    private AuditoriaRepository auditoriaRepository;

    @MockBean
    private JwtTokenProvider tokenProvider;

    private Auditoria auditoria() {
        return Auditoria.builder()
                .id(1L)
                .accion("CREAR")
                .entidad("Libro")
                .entidadId(10L)
                .detalle("Registro de ejemplo")
                .createdAt(LocalDateTime.of(2026, 1, 15, 10, 0))
                .build();
    }

    @Test
    @WithMockUser(roles = "ADMIN")
    void listarPorRangoDeFechas() throws Exception {
        when(auditoriaRepository.findByCreatedAtBetween(any(), any(), any()))
                .thenReturn(new PageImpl<>(List.of(auditoria()), PageRequest.of(0, 15), 1));

        mockMvc.perform(get("/api/auditoria")
                        .param("desde", "2026-01-01")
                        .param("hasta", "2026-01-31"))
                .andExpect(status().isOk())
                .andExpect(jsonPath("$.content[0].accion").value("CREAR"))
                .andExpect(jsonPath("$.content[0].entidad").value("Libro"));
    }

    @Test
    @WithMockUser(roles = "ADMIN")
    void listarPorUsuarioYRangoDeFechas() throws Exception {
        when(auditoriaRepository.findByUsuarioIdAndCreatedAtBetween(eq(3L), any(), any(), any()))
                .thenReturn(new PageImpl<>(List.of(auditoria()), PageRequest.of(0, 15), 1));

        mockMvc.perform(get("/api/auditoria")
                        .param("usuarioId", "3")
                        .param("desde", "2026-01-01")
                        .param("hasta", "2026-01-31"))
                .andExpect(status().isOk())
                .andExpect(jsonPath("$.totalElements").value(1));
    }

    @Test
    @WithMockUser(roles = "ADMIN")
    void listarSinRangoDevuelveTodos() throws Exception {
        when(auditoriaRepository.findAll(any(Pageable.class)))
                .thenReturn(new PageImpl<>(List.of(auditoria()), PageRequest.of(0, 15), 1));

        mockMvc.perform(get("/api/auditoria"))
                .andExpect(status().isOk())
                .andExpect(jsonPath("$.content").isArray());
    }

    @Test
    @WithMockUser(roles = "ADMIN")
    void rangoConFechaInvalidaRetorna400() throws Exception {
        mockMvc.perform(get("/api/auditoria")
                        .param("desde", "2026-13-99")
                        .param("hasta", "2026-01-31"))
                .andExpect(status().isBadRequest());
    }

    @Test
    void sinAutenticacionRetorna401() throws Exception {
        mockMvc.perform(get("/api/auditoria"))
                .andExpect(status().isUnauthorized());
    }
}