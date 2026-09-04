package com.sigcbqr.service;

import com.sigcbqr.model.dto.request.NotificacionRequest;
import com.sigcbqr.model.dto.response.NotificacionResponse;
import com.sigcbqr.model.entity.Notificacion;
import com.sigcbqr.model.entity.Usuario;
import com.sigcbqr.repository.NotificacionRepository;
import com.sigcbqr.repository.UsuarioRepository;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.extension.ExtendWith;
import org.mockito.Mock;
import org.mockito.junit.jupiter.MockitoExtension;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.PageImpl;
import org.springframework.data.domain.PageRequest;
import org.springframework.data.domain.Pageable;

import java.util.List;
import java.util.Optional;

import static org.junit.jupiter.api.Assertions.assertEquals;
import static org.junit.jupiter.api.Assertions.assertThrows;
import static org.mockito.ArgumentMatchers.any;
import static org.mockito.ArgumentMatchers.anyString;
import static org.mockito.ArgumentMatchers.eq;
import static org.mockito.Mockito.verify;
import static org.mockito.Mockito.when;

@ExtendWith(MockitoExtension.class)
class NotificacionServiceTest {

    @Mock
    private NotificacionRepository notificacionRepository;
    @Mock
    private UsuarioRepository usuarioRepository;
    @Mock
    private AuditoriaService auditoriaService;

    private NotificacionService notificacionService;

    @BeforeEach
    void setUp() {
        notificacionService = new NotificacionService(notificacionRepository, usuarioRepository, auditoriaService);
    }

    private Usuario usuario(Long id) {
        Usuario u = new Usuario();
        u.setId(id);
        u.setNombre("Test User");
        u.setEmail("test@test.com");
        return u;
    }

    private Notificacion notificacion(Long id, Usuario usuario) {
        Notificacion n = new Notificacion();
        n.setId(id);
        n.setUsuario(usuario);
        n.setTitulo("Bienvenida");
        n.setMensaje("Hola");
        n.setLeida(false);
        n.setTipo("INFO");
        return n;
    }

    @Test
    void listarPorUsuarioDevuelvePaginado() {
        Page<Notificacion> page = new PageImpl<>(List.of(notificacion(10L, usuario(1L))),
                PageRequest.of(0, 10), 1);
        when(notificacionRepository.findByUsuarioId(eq(1L), any(Pageable.class))).thenReturn(page);

        Page<NotificacionResponse> result = notificacionService.listarPorUsuario(1L, PageRequest.of(0, 10));

        assertEquals(1, result.getTotalElements());
        assertEquals("Bienvenida", result.getContent().get(0).getTitulo());
        assertEquals("Test User", result.getContent().get(0).getUsuarioNombre());
    }

    @Test
    void contarNoLeidasDelegaEnRepo() {
        when(notificacionRepository.countByUsuarioIdAndLeidaFalse(1L)).thenReturn(3L);

        assertEquals(3L, notificacionService.contarNoLeidas(1L));
    }

    @Test
    void crearGuardaYRegistraAuditoria() {
        Usuario usuario = usuario(1L);
        Notificacion guardada = notificacion(5L, usuario);
        guardada.setTitulo("Aviso");
        when(usuarioRepository.findById(1L)).thenReturn(Optional.of(usuario));
        when(notificacionRepository.save(any(Notificacion.class))).thenReturn(guardada);

        NotificacionRequest request = NotificacionRequest.builder()
                .usuarioId(1L)
                .titulo("Aviso")
                .mensaje("Mensaje")
                .tipo("info")
                .build();

        NotificacionResponse response = notificacionService.crear(request);

        assertEquals("Aviso", response.getTitulo());
        verify(notificacionRepository).save(any(Notificacion.class));
        verify(auditoriaService).registrar(eq("CREAR"), eq("NOTIFICACION"), eq(5L), anyString());
    }

    @Test
    void marcarTodasLeidasMarcaLasPendientes() {
        Notificacion n1 = notificacion(1L, usuario(1L));
        Notificacion n2 = notificacion(2L, usuario(1L));
        when(notificacionRepository.findByUsuarioIdAndLeidaFalse(1L)).thenReturn(List.of(n1, n2));

        long marcadas = notificacionService.marcarTodasLeidas(1L);

        assertEquals(2, marcadas);
        assertEquals(true, n1.getLeida());
        assertEquals(true, n2.getLeida());
        verify(notificacionRepository).saveAll(any());
    }

    @Test
    void marcarLeidaSiEsPropiaMarcaCuandoPertenece() {
        Usuario dueno = usuario(1L);
        Notificacion notif = notificacion(7L, dueno);
        when(notificacionRepository.findById(7L)).thenReturn(Optional.of(notif));

        notificacionService.marcarLeidaSiEsPropia(7L, 1L);

        assertEquals(true, notif.getLeida());
        verify(notificacionRepository).save(notif);
    }

    @Test
    void marcarLeidaSiEsPropiaLanzaSiNoPertenece() {
        Usuario dueno = usuario(1L);
        Notificacion notif = notificacion(7L, dueno);
        when(notificacionRepository.findById(7L)).thenReturn(Optional.of(notif));

        assertThrows(org.springframework.security.access.AccessDeniedException.class,
                () -> notificacionService.marcarLeidaSiEsPropia(7L, 999L));

        assertEquals(false, notif.getLeida());
    }
}