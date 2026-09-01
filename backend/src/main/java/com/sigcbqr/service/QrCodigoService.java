package com.sigcbqr.service;

import com.sigcbqr.exception.BadRequestException;
import com.sigcbqr.exception.ResourceNotFoundException;
import com.sigcbqr.model.dto.request.QrCodigoRequest;
import com.sigcbqr.model.dto.response.QrCodigoResponse;
import com.sigcbqr.model.entity.Libro;
import com.sigcbqr.model.entity.QrCodigo;
import com.sigcbqr.repository.LibroRepository;
import com.sigcbqr.repository.QrCodigoRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.security.SecureRandom;
import java.util.List;

@Service
@RequiredArgsConstructor
public class QrCodigoService {

    private final QrCodigoRepository qrCodigoRepository;
    private final LibroRepository libroRepository;
    private final AuditoriaService auditoriaService;
    private static final SecureRandom RANDOM = new SecureRandom();

    @Transactional(readOnly = true)
    public List<QrCodigoResponse> listar() {
        return qrCodigoRepository.findAll().stream().map(this::toResponse).toList();
    }

    @Transactional(readOnly = true)
    public List<QrCodigoResponse> obtenerPorLibro(Long libroId) {
        return qrCodigoRepository.findByLibroId(libroId).stream().map(this::toResponse).toList();
    }

    @Transactional
    public QrCodigoResponse crear(QrCodigoRequest request) {
        Libro libro = libroRepository.findById(request.getLibroId())
                .orElseThrow(() -> new ResourceNotFoundException("Libro no encontrado: " + request.getLibroId()));
        String codigo = (request.getCodigo() != null && !request.getCodigo().isBlank())
                ? request.getCodigo().trim()
                : generarCodigoUnico(libro.getId());
        if (qrCodigoRepository.existsByCodigo(codigo)) {
            throw new BadRequestException("El código QR ya existe");
        }
        QrCodigo qr = QrCodigo.builder()
                .libro(libro)
                .codigo(codigo)
                .imagenUrl(request.getImagenUrl())
                .activo(true)
                .build();
        QrCodigo saved = qrCodigoRepository.save(qr);
        auditoriaService.registrar("CREAR", "CÓDIGO QR", saved.getId(), "Generado para libro #" + libro.getId());
        return toResponse(saved);
    }

    @Transactional
    public QrCodigoResponse regenerar(Long id) {
        QrCodigo qr = qrCodigoRepository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Código QR no encontrado: " + id));
        qr.setCodigo(generarCodigoUnico(qr.getLibro().getId()));
        QrCodigo saved = qrCodigoRepository.save(qr);
        auditoriaService.registrar("REGENERAR", "CÓDIGO QR", id, "Código regenerado");
        return toResponse(saved);
    }

    @Transactional
    public QrCodigoResponse cambiarEstado(Long id, boolean activo) {
        QrCodigo qr = qrCodigoRepository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Código QR no encontrado: " + id));
        qr.setActivo(activo);
        QrCodigo saved = qrCodigoRepository.save(qr);
        auditoriaService.registrar(activo ? "ACTIVAR" : "DESACTIVAR", "CÓDIGO QR", id,
                activo ? "Código activado" : "Código desactivado");
        return toResponse(saved);
    }

    @Transactional
    public QrCodigoResponse validarCodigo(String codigo) {
        QrCodigo qr = qrCodigoRepository.findByCodigo(codigo)
                .orElseThrow(() -> new ResourceNotFoundException("Código QR no encontrado"));
        if (!Boolean.TRUE.equals(qr.getActivo())) {
            throw new BadRequestException("El código QR está inactivo");
        }
        auditoriaService.registrar("VALIDAR", "CÓDIGO QR", qr.getId(),
                "Código QR validado para el libro \"" + qr.getLibro().getTitulo() + "\"");
        return toResponse(qr);
    }

    private String generarCodigoUnico(Long libroId) {
        for (int i = 0; i < 10; i++) {
            String candidato = "QR-LIB-" + libroId + "-" + generarAleatorio(6);
            if (!qrCodigoRepository.existsByCodigo(candidato)) {
                return candidato;
            }
        }
        return "QR-LIB-" + libroId + "-" + System.nanoTime();
    }

    private String generarAleatorio(int longitud) {
        String chars = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789";
        StringBuilder sb = new StringBuilder();
        for (int i = 0; i < longitud; i++) {
            sb.append(chars.charAt(RANDOM.nextInt(chars.length())));
        }
        return sb.toString();
    }

    private QrCodigoResponse toResponse(QrCodigo qr) {
        return QrCodigoResponse.builder()
                .id(qr.getId())
                .libroId(qr.getLibro().getId())
                .libroTitulo(qr.getLibro().getTitulo())
                .codigo(qr.getCodigo())
                .imagenUrl(qr.getImagenUrl())
                .activo(qr.getActivo())
                .createdAt(qr.getCreatedAt())
                .build();
    }
}
