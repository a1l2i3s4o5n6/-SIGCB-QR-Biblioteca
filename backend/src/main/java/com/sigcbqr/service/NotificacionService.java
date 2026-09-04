package com.sigcbqr.service;

import com.sigcbqr.exception.ResourceNotFoundException;
import com.sigcbqr.model.dto.request.NotificacionRequest;
import com.sigcbqr.model.dto.response.NotificacionResponse;
import com.sigcbqr.model.entity.Notificacion;
import com.sigcbqr.model.entity.Usuario;
import com.sigcbqr.repository.NotificacionRepository;
import com.sigcbqr.repository.UsuarioRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.util.StringUtils;

import java.util.List;

@Service
@RequiredArgsConstructor
public class NotificacionService {

    private final NotificacionRepository notificacionRepository;
    private final UsuarioRepository usuarioRepository;
    private final AuditoriaService auditoriaService;

    @Transactional(readOnly = true)
    public Page<NotificacionResponse> listarPorUsuario(Long usuarioId, Pageable pageable) {
        return notificacionRepository.findByUsuarioId(usuarioId, pageable).map(this::toResponse);
    }

    @Transactional(readOnly = true)
    public Page<NotificacionResponse> listarTodas(Boolean leida, Pageable pageable) {
        Page<Notificacion> page = (leida == null)
                ? notificacionRepository.findAll(pageable)
                : notificacionRepository.findByLeida(leida, pageable);
        return page.map(this::toResponse);
    }

    @Transactional(readOnly = true)
    public long contarNoLeidas(Long usuarioId) {
        return notificacionRepository.countByUsuarioIdAndLeidaFalse(usuarioId);
    }

    @Transactional(readOnly = true)
    public NotificacionResponse obtenerPorId(Long id) {
        Notificacion notificacion = notificacionRepository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Notificación", id));
        return toResponse(notificacion);
    }

    @Transactional
    public NotificacionResponse crear(NotificacionRequest request) {
        Usuario usuario = usuarioRepository.findById(request.getUsuarioId())
                .orElseThrow(() -> new ResourceNotFoundException("Usuario", request.getUsuarioId()));

        Notificacion notificacion = Notificacion.builder()
                .usuario(usuario)
                .titulo(request.getTitulo().trim())
                .mensaje(request.getMensaje().trim())
                .tipo(StringUtils.hasText(request.getTipo()) ? request.getTipo().trim().toUpperCase() : "INFO")
                .prioridad(StringUtils.hasText(request.getPrioridad())
                        ? request.getPrioridad().trim().toUpperCase() : "NORMAL")
                .leida(false)
                .build();

        Notificacion saved = notificacionRepository.save(notificacion);
        auditoriaService.registrar("CREAR", "NOTIFICACION", saved.getId(),
                "Notificación \"" + saved.getTitulo() + "\" para " + usuario.getNombre());
        return toResponse(saved);
    }

    @Transactional
    public void marcarLeida(Long id) {
        Notificacion notificacion = notificacionRepository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Notificación", id));
        notificacion.setLeida(true);
        notificacionRepository.save(notificacion);
    }

    @Transactional
    public long marcarTodasLeidas(Long usuarioId) {
        List<Notificacion> pendientes = notificacionRepository.findByUsuarioIdAndLeidaFalse(usuarioId);
        pendientes.forEach(n -> n.setLeida(true));
        notificacionRepository.saveAll(pendientes);
        return pendientes.size();
    }

    private NotificacionResponse toResponse(Notificacion n) {
        return NotificacionResponse.builder()
                .id(n.getId())
                .usuarioId(n.getUsuario().getId())
                .usuarioNombre(n.getUsuario().getNombre())
                .titulo(n.getTitulo())
                .mensaje(n.getMensaje())
                .leida(n.getLeida())
                .tipo(n.getTipo())
                .prioridad(n.getPrioridad())
                .createdAt(n.getCreatedAt())
                .build();
    }
}