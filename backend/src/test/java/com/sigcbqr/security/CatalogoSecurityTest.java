package com.sigcbqr.security;

import com.sigcbqr.config.SecurityConfig;
import com.sigcbqr.controller.CatalogoController;
import com.sigcbqr.repository.AutorRepository;
import com.sigcbqr.repository.CategoriaRepository;
import com.sigcbqr.repository.EditorialRepository;
import com.sigcbqr.repository.CarreraRepository;
import com.sigcbqr.repository.FacultadRepository;
import jakarta.servlet.http.HttpServletResponse;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.test.autoconfigure.web.servlet.WebMvcTest;
import org.springframework.boot.test.mock.mockito.MockBean;
import org.springframework.context.annotation.Import;
import org.springframework.http.MediaType;
import org.springframework.security.authentication.UsernamePasswordAuthenticationToken;
import org.springframework.security.core.authority.SimpleGrantedAuthority;
import org.springframework.test.web.servlet.MockMvc;

import java.util.List;

import static org.mockito.ArgumentMatchers.any;
import static org.mockito.Mockito.doAnswer;
import static org.springframework.security.test.web.servlet.request.SecurityMockMvcRequestPostProcessors.authentication;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.delete;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.post;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.put;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.status;

/**
 * Verifica el refuerzo por defense-in-depth del acceso de escritura al catálogo.
 *
 * CatalogoController ya protegía cada endpoint write con @PreAuthorize. Esta
 * prueba confirma además que SecurityConfig bloquea a nivel de URL (requestMatchers
 * por rol), de modo que un rol sin permiso (ESTUDIANTE) recibe 403 incluso si la
 * anotación de método fallara por omisión.
 */
@WebMvcTest(CatalogoController.class)
@Import(SecurityConfig.class)
class CatalogoSecurityTest {

    @Autowired
    private MockMvc mockMvc;

    @MockBean
    private AutorRepository autorRepository;

    @MockBean
    private EditorialRepository editorialRepository;

    @MockBean
    private CategoriaRepository categoriaRepository;

    @MockBean
    private FacultadRepository facultadRepository;

    @MockBean
    private CarreraRepository carreraRepository;

    @MockBean
    private JwtAuthenticationEntryPoint jwtAuthenticationEntryPoint;

    @MockBean
    private JwtTokenProvider tokenProvider;

    @BeforeEach
    void setup401() throws Exception {
        doAnswer(invocation -> {
            HttpServletResponse response = invocation.getArgument(1);
            response.setStatus(HttpServletResponse.SC_UNAUTHORIZED);
            return null;
        }).when(jwtAuthenticationEntryPoint).commence(any(), any(), any());
    }

    private UsernamePasswordAuthenticationToken auth(String rol) {
        UserPrincipal principal = new UserPrincipal(
                7L,
                "estudiante@estudiante.com",
                "x",
                rol,
                true,
                List.of(new SimpleGrantedAuthority("ROLE_" + rol)));
        return new UsernamePasswordAuthenticationToken(principal, null, principal.getAuthorities());
    }

    @Test
    void estudianteNoPuedeCrearAutor() throws Exception {
        mockMvc.perform(post("/api/autores")
                        .with(authentication(auth("ESTUDIANTE")))
                        .contentType(MediaType.APPLICATION_JSON)
                        .content("{\"nombre\":\"Anon\",\"apellido\":\"Autor\"}"))
                .andExpect(status().isForbidden());
    }

    @Test
    void estudianteNoPuedeActualizarAutor() throws Exception {
        mockMvc.perform(put("/api/autores/1")
                        .with(authentication(auth("ESTUDIANTE")))
                        .contentType(MediaType.APPLICATION_JSON)
                        .content("{\"nombre\":\"Anon\",\"apellido\":\"Autor\"}"))
                .andExpect(status().isForbidden());
    }

    @Test
    void estudianteNoPuedeCrearEditorial() throws Exception {
        mockMvc.perform(post("/api/editoriales")
                        .with(authentication(auth("ESTUDIANTE")))
                        .contentType(MediaType.APPLICATION_JSON)
                        .content("{\"nombre\":\"Editorial\"}"))
                .andExpect(status().isForbidden());
    }

    @Test
    void estudianteNoPuedeActualizarEditorial() throws Exception {
        mockMvc.perform(put("/api/editoriales/1")
                        .with(authentication(auth("ESTUDIANTE")))
                        .contentType(MediaType.APPLICATION_JSON)
                        .content("{\"nombre\":\"Editorial\"}"))
                .andExpect(status().isForbidden());
    }

    @Test
    void estudianteNoPuedeEliminarEditorial() throws Exception {
        mockMvc.perform(delete("/api/editoriales/1")
                        .with(authentication(auth("ESTUDIANTE"))))
                .andExpect(status().isForbidden());
    }

    @Test
    void estudianteNoPuedeCrearCategoria() throws Exception {
        mockMvc.perform(post("/api/categorias")
                        .with(authentication(auth("ESTUDIANTE")))
                        .contentType(MediaType.APPLICATION_JSON)
                        .content("{\"nombre\":\"Ficcion\"}"))
                .andExpect(status().isForbidden());
    }

    @Test
    void estudianteNoPuedeActualizarCategoria() throws Exception {
        mockMvc.perform(put("/api/categorias/1")
                        .with(authentication(auth("ESTUDIANTE")))
                        .contentType(MediaType.APPLICATION_JSON)
                        .content("{\"nombre\":\"Ficcion\"}"))
                .andExpect(status().isForbidden());
    }

    @Test
    void estudianteNoPuedeEliminarCategoria() throws Exception {
        mockMvc.perform(delete("/api/categorias/1")
                        .with(authentication(auth("ESTUDIANTE"))))
                .andExpect(status().isForbidden());
    }
}
