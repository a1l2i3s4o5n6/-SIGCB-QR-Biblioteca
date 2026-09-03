package com.sigcbqr.controller;

import com.sigcbqr.config.SecurityConfig;
import com.sigcbqr.model.entity.Autor;
import com.sigcbqr.model.entity.Categoria;
import com.sigcbqr.model.entity.Editorial;
import com.sigcbqr.repository.AutorRepository;
import com.sigcbqr.repository.CategoriaRepository;
import com.sigcbqr.repository.EditorialRepository;
import com.sigcbqr.repository.CarreraRepository;
import com.sigcbqr.repository.FacultadRepository;
import com.sigcbqr.security.JwtAuthenticationEntryPoint;
import com.sigcbqr.security.JwtTokenProvider;
import com.sigcbqr.security.UserPrincipal;
import org.junit.jupiter.api.Test;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.test.autoconfigure.web.servlet.WebMvcTest;
import org.springframework.boot.test.mock.mockito.MockBean;
import org.springframework.context.annotation.Import;
import org.springframework.data.domain.PageImpl;
import org.springframework.data.domain.PageRequest;
import org.springframework.http.MediaType;
import org.springframework.security.authentication.UsernamePasswordAuthenticationToken;
import org.springframework.security.core.authority.SimpleGrantedAuthority;
import org.springframework.test.web.servlet.MockMvc;

import java.util.List;
import java.util.Optional;

import static org.mockito.ArgumentMatchers.any;
import static org.mockito.Mockito.when;
import static org.springframework.security.test.web.servlet.request.SecurityMockMvcRequestPostProcessors.authentication;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.delete;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.get;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.post;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.put;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.status;

@WebMvcTest(CatalogoController.class)
@Import(SecurityConfig.class)
class CatalogoControllerTest {

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
    private JwtTokenProvider tokenProvider;

    @MockBean
    private JwtAuthenticationEntryPoint jwtAuthenticationEntryPoint;

    private UsernamePasswordAuthenticationToken auth(String rol, Long id) {
        UserPrincipal principal = new UserPrincipal(
                id,
                rol.equals("ADMIN") ? "admin@biblioteca.com" : "estudiante@estudiante.com",
                "x",
                rol,
                true,
                List.of(new SimpleGrantedAuthority("ROLE_" + rol)));
        return new UsernamePasswordAuthenticationToken(principal, null, principal.getAuthorities());
    }

    private Autor autor() {
        return Autor.builder().id(1L).nombre("Gabriel").apellido("Garcia").activo(true).build();
    }

    @Test
    void listarAutoresPermiteCualquierAutenticado() throws Exception {
        when(autorRepository.findByActivoTrue(any(PageRequest.class)))
                .thenReturn(new PageImpl<>(List.of(autor()), PageRequest.of(0, 20), 1));

        mockMvc.perform(get("/api/autores")
                        .with(authentication(auth("ESTUDIANTE", 7L))))
                .andExpect(status().isOk());
    }

    @Test
    void listarAutoresSinAutenticacionDevuelve401() throws Exception {
        mockMvc.perform(get("/api/autores"))
                .andExpect(status().isUnauthorized());
    }

    @Test
    void crearAutorEstudianteDevuelve403() throws Exception {
        mockMvc.perform(post("/api/autores")
                        .with(authentication(auth("ESTUDIANTE", 7L)))
                        .contentType(MediaType.APPLICATION_JSON)
                        .content("{\"nombre\":\"Gabriel\",\"apellido\":\"Garcia\"}"))
                .andExpect(status().isForbidden());
    }

    @Test
    void crearAutorBibliotecarioDevuelve200() throws Exception {
        when(autorRepository.save(any(Autor.class))).thenReturn(autor());

        mockMvc.perform(post("/api/autores")
                        .with(authentication(auth("BIBLIOTECARIO", 2L)))
                        .contentType(MediaType.APPLICATION_JSON)
                        .content("{\"nombre\":\"Gabriel\",\"apellido\":\"Garcia\"}"))
                .andExpect(status().isOk());
    }

    @Test
    void actualizarAutorEstudianteDevuelve403() throws Exception {
        mockMvc.perform(put("/api/autores/1")
                        .with(authentication(auth("ESTUDIANTE", 7L)))
                        .contentType(MediaType.APPLICATION_JSON)
                        .content("{\"nombre\":\"Gabriel\",\"apellido\":\"Garcia\"}"))
                .andExpect(status().isForbidden());
    }

    @Test
    void actualizarAutorBibliotecarioDevuelve200() throws Exception {
        when(autorRepository.findById(1L)).thenReturn(Optional.of(autor()));
        when(autorRepository.save(any(Autor.class))).thenReturn(autor());

        mockMvc.perform(put("/api/autores/1")
                        .with(authentication(auth("BIBLIOTECARIO", 2L)))
                        .contentType(MediaType.APPLICATION_JSON)
                        .content("{\"nombre\":\"Gabriel\",\"apellido\":\"Garcia\"}"))
                .andExpect(status().isOk());
    }

    @Test
    void eliminarEditorialEstudianteDevuelve403() throws Exception {
        mockMvc.perform(delete("/api/editoriales/1")
                        .with(authentication(auth("ESTUDIANTE", 7L))))
                .andExpect(status().isForbidden());
    }

    @Test
    void eliminarEditorialAdminDevuelve200() throws Exception {
        Editorial editorial = Editorial.builder().id(1L).nombre("Editorial").activo(true).build();
        when(editorialRepository.findById(1L)).thenReturn(Optional.of(editorial));
        when(editorialRepository.save(any(Editorial.class))).thenReturn(editorial);

        mockMvc.perform(delete("/api/editoriales/1")
                        .with(authentication(auth("ADMIN", 1L))))
                .andExpect(status().isOk());
    }

    @Test
    void crearCategoriaEstudianteDevuelve403() throws Exception {
        mockMvc.perform(post("/api/categorias")
                        .with(authentication(auth("ESTUDIANTE", 7L)))
                        .contentType(MediaType.APPLICATION_JSON)
                        .content("{\"nombre\":\"Ficcion\"}"))
                .andExpect(status().isForbidden());
    }

    @Test
    void eliminarCategoriaAdminDevuelve200() throws Exception {
        Categoria categoria = Categoria.builder().id(1L).nombre("Ficcion").activo(true).build();
        when(categoriaRepository.findById(1L)).thenReturn(Optional.of(categoria));
        when(categoriaRepository.save(any(Categoria.class))).thenReturn(categoria);

        mockMvc.perform(delete("/api/categorias/1")
                        .with(authentication(auth("ADMIN", 1L))))
                .andExpect(status().isOk());
    }
}
