package com.sigcbqr.service;

import com.sigcbqr.model.entity.Auditoria;
import com.sigcbqr.model.entity.Usuario;
import com.sigcbqr.repository.AuditoriaRepository;
import com.sigcbqr.repository.UsuarioRepository;
import org.springframework.security.core.Authentication;
import org.springframework.security.core.context.SecurityContextHolder;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

@Service
public class AuditoriaService {

    private final AuditoriaRepository auditoriaRepository;
    private final UsuarioRepository usuarioRepository;

    public AuditoriaService(AuditoriaRepository auditoriaRepository,
                            UsuarioRepository usuarioRepository) {
        this.auditoriaRepository = auditoriaRepository;
        this.usuarioRepository = usuarioRepository;
    }

    @Transactional
    public void registrar(String accion, String entidad, Long entidadId, String detalle) {
        Usuario actor = null;
        Authentication auth = SecurityContextHolder.getContext().getAuthentication();
        if (auth != null && auth.isAuthenticated() && !"anonymousUser".equals(auth.getPrincipal())) {
            actor = usuarioRepository.findByEmail(auth.getName()).orElse(null);
        }
        registrar(actor, accion, entidad, entidadId, detalle);
    }

    @Transactional
    public void registrar(Usuario actor, String accion, String entidad, Long entidadId, String detalle) {
        try {
            Auditoria registro = Auditoria.builder()
                    .usuario(actor)
                    .accion(accion)
                    .entidad(entidad)
                    .entidadId(entidadId)
                    .detalle(detalle)
                    .build();

            auditoriaRepository.save(registro);
        } catch (Exception ignored) {
            // La auditoría nunca debe interrumpir la operación principal
        }
    }
}