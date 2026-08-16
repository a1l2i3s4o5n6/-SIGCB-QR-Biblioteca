package com.sigcbqr.controller;

import com.sigcbqr.model.dto.response.LibroResponse;
import com.sigcbqr.model.dto.response.PageResponse;
import com.sigcbqr.security.JwtTokenProvider;
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
import static org.mockito.Mockito.when;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.get;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.status;

@WebMvcTest(LibroController.class)
class LibroControllerTest {

    @Autowired
    private MockMvc mockMvc;

    @MockBean
    private LibroService libroService;

    @MockBean
    private JwtTokenProvider tokenProvider;

    @Test
    @WithMockUser
    void listarLibros() throws Exception {
        LibroResponse libro = LibroResponse.builder()
                .id(1L)
                .titulo("Test Book")
                .activo(true)
                .build();

        PageResponse<LibroResponse> pageResponse = PageResponse.from(
                new PageImpl<>(List.of(libro), PageRequest.of(0, 10), 1));

        when(libroService.listar(any())).thenReturn(
                new PageImpl<>(List.of(libro), PageRequest.of(0, 10), 1));

        mockMvc.perform(get("/api/libros"))
                .andExpect(status().isOk());
    }

    @Test
    void listarLibrosSinAutenticacionRetorna401() throws Exception {
        mockMvc.perform(get("/api/libros"))
                .andExpect(status().isUnauthorized());
    }

    @Test
    @WithMockUser
    void obtenerLibroPorId() throws Exception {
        LibroResponse libro = LibroResponse.builder()
                .id(1L)
                .titulo("Test Book")
                .build();

        when(libroService.obtener(1L)).thenReturn(libro);

        mockMvc.perform(get("/api/libros/1"))
                .andExpect(status().isOk());
    }
}
