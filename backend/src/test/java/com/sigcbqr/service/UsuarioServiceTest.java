package com.sigcbqr.service;

import com.sigcbqr.exception.BadRequestException;
import com.sigcbqr.exception.ResourceNotFoundException;
import com.sigcbqr.model.dto.request.UsuarioRequest;
import com.sigcbqr.model.dto.response.UsuarioResponse;
import com.sigcbqr.model.entity.Rol;
import com.sigcbqr.model.entity.Usuario;
import com.sigcbqr.repository.RolRepository;
import com.sigcbqr.repository.UsuarioRepository;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.extension.ExtendWith;
import org.mockito.Mock;
import org.mockito.junit.jupiter.MockitoExtension;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.PageImpl;
import org.springframework.data.domain.Pageable;
import org.springframework.security.crypto.password.PasswordEncoder;

import java.util.List;
import java.util.Optional;

import static org.junit.jupiter.api.Assertions.assertEquals;
import static org.junit.jupiter.api.Assertions.assertFalse;
import static org.junit.jupiter.api.Assertions.assertThrows;
import static org.junit.jupiter.api.Assertions.assertTrue;
import static org.mockito.ArgumentMatchers.any;
import static org.mockito.ArgumentMatchers.anyString;
import static org.mockito.ArgumentMatchers.eq;
import static org.mockito.ArgumentMatchers.isNull;
import static org.mockito.Mockito.never;
import static org.mockito.Mockito.verify;
import static org.mockito.Mockito.when;

@ExtendWith(MockitoExtension.class)
class UsuarioServiceTest {

    @Mock
    private UsuarioRepository usuarioRepository;
    @Mock
    private RolRepository rolRepository;
    @Mock
    private PasswordEncoder passwordEncoder;
    @Mock
    private AuditoriaService auditoriaService;

    private UsuarioService usuarioService;

    @BeforeEach
    void setUp() {
        usuarioService = new UsuarioService(usuarioRepository, rolRepository, passwordEncoder, auditoriaService);
    }

    private Rol rol(Long id, String nombre) {
        return Rol.builder().id(id).nombre(nombre).build();
    }

    private Usuario usuario(Long id) {
        return Usuario.builder().id(id).nombre("Ana").email("ana@test.com").password("hash")
                .activo(true).rol(rol(2L, "ADMIN")).build();
    }

    private UsuarioRequest request(String email, String password) {
        UsuarioRequest req = new UsuarioRequest();
        req.setNombre("Ana");
        req.setEmail(email);
        req.setPassword(password);
        req.setTelefono("0990000000");
        req.setRolId(2L);
        req.setActivo(true);
        return req;
    }

    @Test
    void listarDevuelveLaPaginaMapeada() {
        when(usuarioRepository.findAll(any(Pageable.class))).thenReturn(new PageImpl<>(List.of(usuario(1L))));

        Page<UsuarioResponse> pagina = usuarioService.listar(Pageable.unpaged());

        assertEquals(1, pagina.getContent().size());
        assertEquals("ana@test.com", pagina.getContent().get(0).getEmail());
        assertEquals("ADMIN", pagina.getContent().get(0).getRol());
    }

    @Test
    void listarFiltradoNormalizaTextoYRol() {
        when(usuarioRepository.buscarConFiltros(eq("an"), eq("ADMIN"), eq(true), any(Pageable.class)))
                .thenReturn(new PageImpl<>(List.of(usuario(1L))));

        Page<UsuarioResponse> pagina = usuarioService.listarFiltrado(" an ", " admin ", true, Pageable.unpaged());

        assertEquals(1, pagina.getContent().size());
        assertEquals("Ana", pagina.getContent().get(0).getNombre());
    }

    @Test
    void listarFiltradoSinCriteriosManejaNulos() {
        when(usuarioRepository.buscarConFiltros(isNull(), isNull(), isNull(), any(Pageable.class)))
                .thenReturn(new PageImpl<>(List.of(usuario(1L))));

        Page<UsuarioResponse> pagina = usuarioService.listarFiltrado("   ", "   ", null, Pageable.unpaged());

        assertEquals(1, pagina.getContent().size());
    }

    @Test
    void obtenerDevuelveElUsuario() {
        when(usuarioRepository.findById(1L)).thenReturn(Optional.of(usuario(1L)));

        UsuarioResponse response = usuarioService.obtener(1L);

        assertEquals("Ana", response.getNombre());
    }

    @Test
    void obtenerLanzaExcepcionSiNoExiste() {
        when(usuarioRepository.findById(99L)).thenReturn(Optional.empty());

        assertThrows(ResourceNotFoundException.class, () -> usuarioService.obtener(99L));
    }

    @Test
    void crearRechazaCorreoYaRegistrado() {
        when(usuarioRepository.existsByEmail("ana@test.com")).thenReturn(true);

        assertThrows(BadRequestException.class, () -> usuarioService.crear(request("ana@test.com", "clave")));
        verify(usuarioRepository, never()).save(any(Usuario.class));
    }

    @Test
    void crearRechazaContrasenaVacia() {
        when(usuarioRepository.existsByEmail("ana@test.com")).thenReturn(false);

        assertThrows(BadRequestException.class, () -> usuarioService.crear(request("ana@test.com", null)));
        assertThrows(BadRequestException.class, () -> usuarioService.crear(request("ana@test.com", "  ")));
        verify(usuarioRepository, never()).save(any(Usuario.class));
    }

    @Test
    void crearConRolExplicitoCodificaLaContrasena() {
        when(usuarioRepository.existsByEmail("ana@test.com")).thenReturn(false);
        when(rolRepository.findById(2L)).thenReturn(Optional.of(rol(2L, "ADMIN")));
        when(passwordEncoder.encode("clave")).thenReturn("hash-seguro");
        when(usuarioRepository.save(any(Usuario.class))).thenAnswer(inv -> {
            Usuario guardado = inv.getArgument(0);
            guardado.setId(1L);
            return guardado;
        });

        UsuarioResponse response = usuarioService.crear(request("ana@test.com", "clave"));

        assertEquals("ADMIN", response.getRol());
        assertTrue(response.getActivo());
        verify(passwordEncoder).encode("clave");
        verify(auditoriaService).registrar(eq("CREAR"), eq("USUARIO"), eq(1L), anyString());
    }

    @Test
    void crearUsaRolPorDefectoEstudiante() {
        when(usuarioRepository.existsByEmail("ana@test.com")).thenReturn(false);
        when(rolRepository.findById(3L)).thenReturn(Optional.of(rol(3L, "ESTUDIANTE")));
        when(passwordEncoder.encode("clave")).thenReturn("hash");
        when(usuarioRepository.save(any(Usuario.class))).thenAnswer(inv -> {
            Usuario guardado = inv.getArgument(0);
            guardado.setId(2L);
            return guardado;
        });

        UsuarioRequest req = request("ana@test.com", "clave");
        req.setRolId(null);

        UsuarioResponse response = usuarioService.crear(req);

        assertEquals("ESTUDIANTE", response.getRol());
    }

    @Test
    void actualizarModificaDatosContrasenaRolYEstado() {
        Usuario usuario = usuario(1L);
        when(usuarioRepository.findById(1L)).thenReturn(Optional.of(usuario));
        when(rolRepository.findById(2L)).thenReturn(Optional.of(rol(2L, "BIBLIOTECARIO")));
        when(passwordEncoder.encode("nueva")).thenReturn("hash-nuevo");
        when(usuarioRepository.save(any(Usuario.class))).thenAnswer(inv -> inv.getArgument(0));

        UsuarioRequest req = request("nueva-ana@test.com", "nueva");
        req.setActivo(false);

        UsuarioResponse response = usuarioService.actualizar(1L, req);

        assertFalse(response.getActivo());
        assertEquals("nueva-ana@test.com", response.getEmail());
        verify(passwordEncoder).encode("nueva");
        verify(auditoriaService).registrar(eq("ACTUALIZAR"), eq("USUARIO"), eq(1L), anyString());
    }

    @Test
    void actualizarSinContrasenaNiRolMantieneValores() {
        Usuario usuario = usuario(1L);
        when(usuarioRepository.findById(1L)).thenReturn(Optional.of(usuario));
        when(usuarioRepository.save(any(Usuario.class))).thenAnswer(inv -> inv.getArgument(0));

        UsuarioRequest req = new UsuarioRequest();
        req.setNombre("Ana Maria");
        req.setEmail("ana@test.com");
        req.setTelefono(null);

        UsuarioResponse response = usuarioService.actualizar(1L, req);

        assertEquals("Ana Maria", response.getNombre());
        verify(passwordEncoder, never()).encode(anyString());
        verify(auditoriaService).registrar(eq("ACTUALIZAR"), eq("USUARIO"), eq(1L), anyString());
    }

    @Test
    void actualizarLanzaExcepcionSiNoExiste() {
        when(usuarioRepository.findById(99L)).thenReturn(Optional.empty());

        assertThrows(ResourceNotFoundException.class,
                () -> usuarioService.actualizar(99L, request("ana@test.com", "clave")));
    }

    @Test
    void eliminarDesactivaEnLugarDeBorrar() {
        Usuario usuario = usuario(1L);
        when(usuarioRepository.findById(1L)).thenReturn(Optional.of(usuario));
        when(usuarioRepository.save(any(Usuario.class))).thenAnswer(inv -> inv.getArgument(0));

        usuarioService.eliminar(1L);

        assertFalse(usuario.getActivo());
        verify(auditoriaService).registrar(eq("ELIMINAR"), eq("USUARIO"), eq(1L), anyString());
    }

    @Test
    void eliminarLanzaExcepcionSiNoExiste() {
        when(usuarioRepository.findById(99L)).thenReturn(Optional.empty());

        assertThrows(ResourceNotFoundException.class, () -> usuarioService.eliminar(99L));
    }
}