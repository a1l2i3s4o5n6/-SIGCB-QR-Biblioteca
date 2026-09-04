package com.sigcbqr.controller;

import com.sigcbqr.config.SecurityConfig;
import com.sigcbqr.model.dto.request.PerfilRequest;
import com.sigcbqr.model.dto.response.UsuarioResponse;
import com.sigcbqr.model.entity.Rol;
import com.sigcbqr.model.entity.Usuario;
import com.sigcbqr.security.JwtAuthenticationEntryPoint;
import com.sigcbqr.security.JwtAuthenticationFilter;
import com.sigcbqr.security.JwtTokenProvider;
import com.sigcbqr.security.UserPrincipal;
import com.sigcbqr.service.AuthService;
import com.fasterxml.jackson.databind.ObjectMapper;
import jakarta.servlet.FilterChain;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.test.autoconfigure.web.servlet.WebMvcTest;
import org.springframework.boot.test.mock.mockito.MockBean;
import org.springframework.context.annotation.Import;
import org.springframework.http.MediaType;
import org.springframework.security.core.authority.SimpleGrantedAuthority;
import org.springframework.test.web.servlet.MockMvc;

import java.util.List;

import static org.mockito.ArgumentMatchers.any;
import static org.mockito.ArgumentMatchers.eq;
import static org.mockito.Mockito.doAnswer;
import static org.mockito.Mockito.when;
import static org.springframework.security.test.web.servlet.request.SecurityMockMvcRequestPostProcessors.user;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.get;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.put;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.jsonPath;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.status;

@WebMvcTest(PerfilController.class)
@Import({SecurityConfig.class, JwtAuthenticationEntryPoint.class})
class PerfilControllerTest {

    @Autowired
    private MockMvc mockMvc;

    @Autowired
    private ObjectMapper objectMapper;

    @MockBean
    private AuthService authService;

    @MockBean
    private JwtTokenProvider tokenProvider;

    @MockBean
    private JwtAuthenticationFilter jwtAuthenticationFilter;

    private final UserPrincipal principal = new UserPrincipal(
            1L, "admin@biblioteca.com", "hash", "ADMIN", true,
            List.of(new SimpleGrantedAuthority("ROLE_ADMIN")));

    @BeforeEach
    void dejarPasarLaCadenaDeFiltros() throws Exception {
        doAnswer(invocation -> {
            FilterChain chain = invocation.getArgument(2);
            chain.doFilter(invocation.getArgument(0), invocation.getArgument(1));
            return null;
        }).when(jwtAuthenticationFilter)
                .doFilter(any(jakarta.servlet.ServletRequest.class),
                        any(jakarta.servlet.ServletResponse.class),
                        any(FilterChain.class));
    }

    @Test
    void verPerfilAutenticado() throws Exception {
        Rol rol = new Rol();
        rol.setId(1L);
        rol.setNombre("ADMIN");
        var usuario = Usuario.builder()
                .id(1L)
                .nombre("Admin")
                .email("admin@biblioteca.com")
                .telefono("0999999999")
                .foto("/uploads/avatar.jpg")
                .activo(true)
                .rol(rol)
                .build();
        when(authService.getCurrentUser(1L)).thenReturn(usuario);

        mockMvc.perform(get("/api/perfil").with(user(principal)))
                .andExpect(status().isOk())
                .andExpect(jsonPath("$.data.foto").value("/uploads/avatar.jpg"))
                .andExpect(jsonPath("$.data.nombre").value("Admin"));
    }

    @Test
    void actualizarPerfilAutenticado() throws Exception {
        UsuarioResponse updated = UsuarioResponse.builder()
                .id(1L)
                .nombre("Admin Editado")
                .email("admin@biblioteca.com")
                .foto("/uploads/nuevo.jpg")
                .rol("ADMIN")
                .activo(true)
                .build();
        when(authService.actualizarPerfil(eq(1L), any(PerfilRequest.class))).thenReturn(updated);

        PerfilRequest request = new PerfilRequest();
        request.setNombre("Admin Editado");
        request.setEmail("admin@biblioteca.com");
        request.setFoto("/uploads/nuevo.jpg");

        mockMvc.perform(put("/api/perfil").with(user(principal))
                        .contentType(MediaType.APPLICATION_JSON)
                        .content(objectMapper.writeValueAsString(request)))
                .andExpect(status().isOk())
                .andExpect(jsonPath("$.message").value("Perfil actualizado"))
                .andExpect(jsonPath("$.data.nombre").value("Admin Editado"));
    }

    @Test
    void actualizarPerfilSinAutenticacionDevuelveNoAutorizado() throws Exception {
        mockMvc.perform(get("/api/perfil"))
                .andExpect(status().isUnauthorized());
    }
}