package com.sigcbqr.service;

import com.sigcbqr.exception.BadRequestException;
import com.sigcbqr.exception.ResourceNotFoundException;
import com.sigcbqr.model.dto.request.NotificacionRequest;
import com.sigcbqr.model.dto.request.SancionRequest;
import com.sigcbqr.model.dto.response.SancionResponse;
import com.sigcbqr.model.entity.Sancion;
import com.sigcbqr.model.entity.Usuario;
import com.sigcbqr.repository.SancionRepository;
import com.sigcbqr.repository.UsuarioRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.util.Set;

@Service
@RequiredArgsConstructor
public class SancionService {

    private static final Set<String> TIPOS_PERMITIDOS =
            Set.of("SUSPENSION", "BLOQUEO_TEMPORAL", "ADVERTENCIA");

    private final SancionRepository sancionRepository;
    private final UsuarioRepository usuarioRepository;
    private final NotificacionService notificacionService;
    private final AuditoriaService auditoriaService;

    @Transactional(readOnly = true)
    public Page<SancionResponse> listar(Boolean activa, Pageable pageable) {
        Page<Sancion> page = (activa == null)
                ? sancionRepository.findAll(pageable)
                : sancionRepository.findByActiva(activa, pageable);
        return page.map(this::toResponse);
    }

    @Transactional(readOnly = true)
    public Page<SancionResponse> listarPorUsuario(Long usuarioId, Pageable pageable) {
        return sancionRepository.findByUsuarioId(usuarioId, pageable).map(this::toResponse);
    }

    @Transactional(readOnly = true)
    public boolean tieneSancionActiva(Long usuarioId) {
        return sancionRepository.existsByUsuarioIdAndActivaTrue(usuarioId);
    }

    @Transactional
    public SancionResponse crear(SancionRequest request) {
        Usuario usuario = usuarioRepository.findById(request.getUsuarioId())
                .orElseThrow(() -> new ResourceNotFoundException("Usuario", request.getUsuarioId()));

        String tipo = request.getTipo().trim().toUpperCase();
        if (!TIPOS_PERMITIDOS.contains(tipo)) {
            throw new BadRequestException(
                    "Tipo de sanción inválido. Permitidos: SUSPENSION, BLOQUEO_TEMPORAL, ADVERTENCIA");
        }
        if (sancionRepository.existsByUsuarioIdAndActivaTrue(request.getUsuarioId())) {
            throw new BadRequestException("El usuario ya tiene una sanción activa");
        }

        Sancion sancion = Sancion.builder()
                .usuario(usuario)
                .tipo(tipo)
                .motivo(request.getMotivo())
                .fechaInicio(request.getFechaInicio())
                .fechaFin(request.getFechaFin())
                .activa(true)
                .build();
        Sancion saved = sancionRepository.save(sancion);

        notificacionService.crear(NotificacionRequest.builder()
                .usuarioId(usuario.getId())
                .titulo("Sanción aplicada")
                .mensaje("Se te ha aplicado una sanción de tipo " + tipo + ".")
                .tipo("SANCION")
                .build());

        auditoriaService.registrar("CREAR", "SANCION", saved.getId(),
                "Sanción " + tipo + " aplicada a " + usuario.getNombre());
        return toResponse(saved);
    }

    @Transactional
    public SancionResponse levantar(Long id) {
        Sancion sancion = sancionRepository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Sanción", id));
        if (!Boolean.TRUE.equals(sancion.getActiva())) {
            throw new BadRequestException("La sanción ya no está activa");
        }

        sancion.setActiva(false);
        Sancion saved = sancionRepository.save(sancion);

        notificacionService.crear(NotificacionRequest.builder()
                .usuarioId(saved.getUsuario().getId())
                .titulo("Sanción levantada")
                .mensaje("Tu sanción de tipo " + saved.getTipo() + " ha sido levantada.")
                .tipo("SANCION")
                .build());

        auditoriaService.registrar("LEVANTAR", "SANCION", id,
                "Sanción " + saved.getTipo() + " levantada a " + saved.getUsuario().getNombre());
        return toResponse(saved);
    }

    private SancionResponse toResponse(Sancion s) {
        return SancionResponse.builder()
                .id(s.getId())
                .usuarioId(s.getUsuario().getId())
                .usuarioNombre(s.getUsuario().getNombre())
                .tipo(s.getTipo())
                .motivo(s.getMotivo())
                .fechaInicio(s.getFechaInicio())
                .fechaFin(s.getFechaFin())
                .activa(s.getActiva())
                .createdAt(s.getCreatedAt())
                .build();
    }
}