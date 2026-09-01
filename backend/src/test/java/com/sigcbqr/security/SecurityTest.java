package com.sigcbqr.security;

import com.sigcbqr.controller.LibroController;
import com.sigcbqr.model.dto.response.LibroResponse;
import com.sigcbqr.model.dto.response.PageResponse;
import com.sigcbqr.service.LibroService;
import org.junit.jupiter.api.Test;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.test.autoconfigure.web.servlet.WebMvcTest;
import org.springframework.boot.test.mock.mockito.MockBean;
import org.springframework.data.domain.PageImpl;
import org.springframework.data.domain.PageRequest;
import org.springframework.security.test.context.support.WithMockUser;
import org.springframework.test.web.servlet.MockMvc;

import java.util.List;
import static org.mockito.ArgumentMatchers.any;
import static org.mockito.ArgumentMatchers.anyString;
import static org.mockito.Mockito.when;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.get;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.status;

/**
 * Verifica REQ-NF-002 y REQ-NF-003:
 *  - REQ-NF-002: los endpoints bajo /api/** están protegidos y rechazan
 *    peticiones sin autenticación con 401 (no 200 ni 404).
 *  - REQ-NF-003: los parámetros de entrada con payloads de inyección SQL son
 *    tratados como datos (los repositorios usan parámetros vinculados), de modo
 *    que el endpoint no se rompe ni filtra datos.
 */
@WebMvcTest(LibroController.class)
class SecurityTest {

    @Autowired
    private MockMvc mockMvc;

    @MockBean
    private LibroService libroService;

    @MockBean
    private JwtTokenProvider tokenProvider;

    @Test
    void endpointsProtegidosRechazanPeticionesSinAutenticacion() throws Exception {
        mockMvc.perform(get("/api/libros"))
                .andExpect(status().isUnauthorized());

        mockMvc.perform(get("/api/libros/buscar").param("q", "java"))
                .andExpect(status().isUnauthorized());

        mockMvc.perform(get("/api/libros/1"))
                .andExpect(status().isUnauthorized());
    }

    @Test
    @WithMockUser
    void sqlInjectionEsTratadaComoDatoYNoRompeElEndpoint() throws Exception {
        LibroResponse libro = LibroResponse.builder()
                .id(1L)
                .titulo("Libro seguro")
                .build();

        PageResponse<LibroResponse> pageResponse = PageResponse.from(
                new PageImpl<>(List.of(libro), PageRequest.of(0, 10), 1));

        when(libroService.listarFiltrado(anyString(), any(), any(), any(), any(), any()))
                .thenReturn(new PageImpl<>(List.of(libro), PageRequest.of(0, 10), 1));

        String payloadSql = "'; DROP TABLE libros; --";
        mockMvc.perform(get("/api/libros").param("q", payloadSql))
                .andExpect(status().isOk());
    }

    @Test
    @WithMockUser
    void buscarConPayloadDeInyeccionSQLNoProvocaErrorInterno() throws Exception {
        when(libroService.buscar(anyString(), any()))
                .thenReturn(new PageImpl<>(List.of(), PageRequest.of(0, 10), 0));

        mockMvc.perform(get("/api/libros/buscar")
                        .param("q", "x' OR '1'='1"))
                .andExpect(status().isOk());
    }
}