package com.sigcbqr.controller;

import com.sigcbqr.security.JwtTokenProvider;
import com.sigcbqr.service.ReporteService;
import org.junit.jupiter.api.Test;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.test.autoconfigure.web.servlet.WebMvcTest;
import org.springframework.boot.test.mock.mockito.MockBean;
import org.springframework.security.test.context.support.WithMockUser;
import org.springframework.test.web.servlet.MockMvc;

import java.util.Map;

import static org.mockito.Mockito.when;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.get;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.status;

@WebMvcTest(ReporteController.class)
class ReporteControllerTest {

    @Autowired
    private MockMvc mockMvc;

    @MockBean
    private ReporteService reporteService;

    @MockBean
    private JwtTokenProvider tokenProvider;

    @Test
    @WithMockUser(roles = "BIBLIOTECARIO")
    void prestamosDiariosComoBibliotecario() throws Exception {
        when(reporteService.prestamosDiarios()).thenReturn(Map.of("total", 5));
        mockMvc.perform(get("/api/reportes/prestamos-diarios"))
                .andExpect(status().isOk());
    }

    @Test
    @WithMockUser(roles = "ESTUDIANTE")
    void prestamosDiariosComoEstudianteRetorna403() throws Exception {
        mockMvc.perform(get("/api/reportes/prestamos-diarios"))
                .andExpect(status().isForbidden());
    }
}
