package com.sigcbqr.service;

import com.sigcbqr.exception.BadRequestException;
import com.sigcbqr.exception.ResourceNotFoundException;
import com.sigcbqr.model.dto.request.PrestamoRequest;
import com.sigcbqr.model.dto.response.PrestamoResponse;
import com.sigcbqr.model.entity.*;
import com.sigcbqr.repository.*;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.time.LocalDateTime;

@Service
public class PrestamoService {

    private static final int MAX_PRESTAMOS_ACTIVOS = 5;
    private static final int DIAS_PRESTAMO = 7;
    private static final int MAX_RENOVACIONES = 2;

    private final PrestamoRepository prestamoRepository;
    private final UsuarioRepository usuarioRepository;
    private final InventarioRepository inventarioRepository;
    private final LibroRepository libroRepository;
    private final SancionRepository sancionRepository;
    private final ReservaRepository reservaRepository;
    private final NotificacionService notificacionService;
    private final AuditoriaService auditoriaService;
    private final QrCodigoRepository qrCodigoRepository;

    public PrestamoService(PrestamoRepository prestamoRepository,
                           UsuarioRepository usuarioRepository,
                           InventarioRepository inventarioRepository,
                           LibroRepository libroRepository,
                           SancionRepository sancionRepository,
                           ReservaRepository reservaRepository,
                           NotificacionService notificacionService,
                           AuditoriaService auditoriaService,
                           QrCodigoRepository qrCodigoRepository) {
        this.prestamoRepository = prestamoRepository;
        this.usuarioRepository = usuarioRepository;
        this.inventarioRepository = inventarioRepository;
        this.libroRepository = libroRepository;
        this.sancionRepository = sancionRepository;
        this.reservaRepository = reservaRepository;
        this.notificacionService = notificacionService;
        this.auditoriaService = auditoriaService;
        this.qrCodigoRepository = qrCodigoRepository;
    }

    public Page<PrestamoResponse> listar(Pageable pageable) {
        return prestamoRepository.findAll(pageable).map(this::toResponse);
    }

    public Page<PrestamoResponse> listarFiltrado(String q, String estado,
                                                 LocalDateTime desde, LocalDateTime hasta,
                                                 Pageable pageable) {
        String texto = (q != null && !q.isBlank()) ? q.trim() : null;
        String est = (estado != null && !estado.isBlank()) ? estado.trim().toUpperCase() : null;
        LocalDateTime desdeSeguro = desde != null ? desde : LocalDateTime.of(1900, 1, 1, 0, 0);
        LocalDateTime hastaSegura = hasta != null ? hasta : LocalDateTime.of(2100, 1, 1, 0, 0);
        return prestamoRepository.buscarConFiltros(texto, est, desdeSeguro, hastaSegura, pageable).map(this::toResponse);
    }

    public Page<PrestamoResponse> listarPorEstado(String estado, Pageable pageable) {
        return prestamoRepository.findByEstado(estado, pageable).map(this::toResponse);
    }

    public Page<PrestamoResponse> listarPorUsuario(Long usuarioId, Pageable pageable) {
        return prestamoRepository.findByUsuarioIdOrderByFechaPrestamoDesc(usuarioId, pageable)
                .map(this::toResponse);
    }

    public PrestamoResponse obtener(Long id) {
        Prestamo prestamo = prestamoRepository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Préstamo", id));
        return toResponse(prestamo);
    }

    @Transactional
    public PrestamoResponse crear(PrestamoRequest request) {
        Usuario usuario = usuarioRepository.findById(request.getUsuarioId())
                .orElseThrow(() -> new ResourceNotFoundException("Usuario", request.getUsuarioId()));

        Inventario inventario = inventarioRepository.findById(request.getInventarioId())
                .orElseThrow(() -> new ResourceNotFoundException("Ejemplar", request.getInventarioId()));

        if (!"DISPONIBLE".equals(inventario.getEstado())) {
            throw new BadRequestException("El ejemplar no está disponible");
        }

        validarCodigoQr(request, inventario);

        long prestamosActivos = prestamoRepository.countByUsuarioIdAndEstado(request.getUsuarioId(), "ACTIVO");
        if (prestamosActivos >= MAX_PRESTAMOS_ACTIVOS) {
            throw new BadRequestException("El usuario tiene demasiados préstamos activos");
        }

        if (sancionRepository.existsByUsuarioIdAndActivaTrue(request.getUsuarioId())) {
            throw new BadRequestException("El usuario tiene una sanción activa y no puede realizar nuevos préstamos");
        }

        Prestamo prestamo = Prestamo.builder()
                .usuario(usuario)
                .inventario(inventario)
                .fechaPrestamo(LocalDateTime.now())
                .fechaVencimiento(LocalDateTime.now().plusDays(DIAS_PRESTAMO))
                .estado("ACTIVO")
                .build();

        inventario.setEstado("PRESTADO");
        inventarioRepository.save(inventario);

        Libro libro = inventario.getLibro();
        libro.setEjemplaresDisponibles(libro.getEjemplaresDisponibles() - 1);
        libroRepository.save(libro);

        prestamo = prestamoRepository.save(prestamo);
        auditoriaService.registrar("CREAR", "PRESTAMO", prestamo.getId(),
                "Préstamo de \"" + libro.getTitulo() + "\" a " + usuario.getNombre());
        completarReservaSiExiste(libro, usuario, prestamo);
        return toResponse(prestamo);
    }

    private void completarReservaSiExiste(Libro libro, Usuario usuario, Prestamo prestamo) {
        reservaRepository.findByLibroIdAndUsuarioIdAndEstado(libro.getId(), usuario.getId(), "PENDIENTE")
                .ifPresent(reserva -> {
                    reserva.setEstado("COMPLETADA");
                    reservaRepository.save(reserva);
                    auditoriaService.registrar("COMPLETAR", "RESERVA", reserva.getId(),
                            "Solicitud de \"" + libro.getTitulo() + "\" atendida con préstamo #" + prestamo.getId());
                    notificacionService.notificar(usuario.getId(), "Tu solicitud fue atendida",
                            "Tu solicitud de \"" + libro.getTitulo() + "\" fue completada. Ya puedes retirar el libro (préstamo #" + prestamo.getId() + ").",
                            "EXITO");
                });
    }

    @Transactional
    public PrestamoResponse devolver(Long id) {
        Prestamo prestamo = prestamoRepository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Préstamo", id));

        if ("DEVUELTO".equals(prestamo.getEstado())) {
            throw new BadRequestException("El préstamo ya fue devuelto");
        }

        prestamo.setFechaDevolucion(LocalDateTime.now());
        prestamo.setEstado("DEVUELTO");

        Inventario inventario = prestamo.getInventario();
        inventario.setEstado("DISPONIBLE");
        inventarioRepository.save(inventario);

        Libro libro = inventario.getLibro();
        libro.setEjemplaresDisponibles(libro.getEjemplaresDisponibles() + 1);
        libroRepository.save(libro);

        // Generar multa si está vencido
        if (prestamo.getFechaVencimiento().isBefore(LocalDateTime.now())) {
            // Multa lógica here - handled by MultaService
        }

        prestamo = prestamoRepository.save(prestamo);
        auditoriaService.registrar("DEVOLVER", "PRESTAMO", prestamo.getId(),
                "Devolución de \"" + inventario.getLibro().getTitulo() + "\" de " + prestamo.getUsuario().getNombre());
        return toResponse(prestamo);
    }

    private void validarCodigoQr(PrestamoRequest request, Inventario inventario) {
        String codigoQr = request.getCodigoQr();
        if (codigoQr == null || codigoQr.isBlank()) {
            return;
        }

        QrCodigo qr = qrCodigoRepository.findByCodigo(codigoQr.trim())
                .orElseThrow(() -> new BadRequestException("El código QR '" + codigoQr.trim() + "' no está registrado"));

        if (!Boolean.TRUE.equals(qr.getActivo())) {
            throw new BadRequestException("El código QR '" + codigoQr.trim() + "' está desactivado");
        }

        if (!qr.getLibro().getId().equals(inventario.getLibro().getId())) {
            throw new BadRequestException("El código QR no corresponde al libro del ejemplar seleccionado");
        }
    }

    @Transactional
    public PrestamoResponse solicitarRenovacion(Long id) {
        Prestamo prestamo = prestamoRepository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Préstamo", id));

        if (!"ACTIVO".equals(prestamo.getEstado())) {
            throw new BadRequestException("Solo se pueden solicitar renovaciones de préstamos activos");
        }

        Integer renovaciones = prestamo.getNumRenovaciones() != null ? prestamo.getNumRenovaciones() : 0;
        if (renovaciones >= MAX_RENOVACIONES) {
            throw new BadRequestException("El préstamo ya alcanzó el límite de renovaciones permitidas");
        }

        prestamo.setEstado("RENOVACION_PENDIENTE");
        prestamo.setObservaciones("Solicitud de renovación en espera de aprobación");
        prestamo = prestamoRepository.save(prestamo);

        auditoriaService.registrar("SOLICITAR_RENOVACION", "PRESTAMO", prestamo.getId(),
                "Solicitud de renovación del préstamo #" + prestamo.getId()
                        + " (" + prestamo.getUsuario().getNombre() + ")");
        return toResponse(prestamo);
    }

    @Transactional
    public PrestamoResponse aprobarRenovacion(Long id) {
        Prestamo prestamo = prestamoRepository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Préstamo", id));

        if (!"RENOVACION_PENDIENTE".equals(prestamo.getEstado())) {
            throw new BadRequestException("El préstamo no tiene una solicitud de renovación pendiente");
        }

        prestamo.setEstado("RENOVADO");
        prestamo.setObservaciones("Renovado - nueva fecha: " + LocalDateTime.now().plusDays(DIAS_PRESTAMO));
        prestamoRepository.save(prestamo);

        Prestamo nuevoPrestamo = Prestamo.builder()
                .usuario(prestamo.getUsuario())
                .inventario(prestamo.getInventario())
                .fechaPrestamo(LocalDateTime.now())
                .fechaVencimiento(LocalDateTime.now().plusDays(DIAS_PRESTAMO))
                .estado("ACTIVO")
                .numRenovaciones(prestamo.getNumRenovaciones() != null
                        ? prestamo.getNumRenovaciones() + 1 : 1)
                .observaciones("Renovación aprobada del préstamo #" + prestamo.getId())
                .build();

        nuevoPrestamo = prestamoRepository.save(nuevoPrestamo);
        auditoriaService.registrar("APROBAR_RENOVACION", "PRESTAMO", nuevoPrestamo.getId(),
                "Renovación aprobada del préstamo #" + prestamo.getId()
                        + " (" + prestamo.getUsuario().getNombre() + ")");
        return toResponse(nuevoPrestamo);
    }

    @Transactional
    public PrestamoResponse rechazarRenovacion(Long id) {
        Prestamo prestamo = prestamoRepository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Préstamo", id));

        if (!"RENOVACION_PENDIENTE".equals(prestamo.getEstado())) {
            throw new BadRequestException("El préstamo no tiene una solicitud de renovación pendiente");
        }

        prestamo.setEstado("ACTIVO");
        prestamo.setObservaciones("Solicitud de renovación rechazada");
        prestamo = prestamoRepository.save(prestamo);

        auditoriaService.registrar("RECHAZAR_RENOVACION", "PRESTAMO", prestamo.getId(),
                "Solicitud de renovación rechazada del préstamo #" + prestamo.getId()
                        + " (" + prestamo.getUsuario().getNombre() + ")");
        return toResponse(prestamo);
    }

    @Transactional
    public PrestamoResponse renovar(Long id) {
        Prestamo prestamo = prestamoRepository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Préstamo", id));

        if (!"ACTIVO".equals(prestamo.getEstado())) {
            throw new BadRequestException("Solo se pueden renovar préstamos activos");
        }

        prestamo.setEstado("RENOVADO");
        prestamo.setObservaciones("Renovado - nueva fecha: " + LocalDateTime.now().plusDays(DIAS_PRESTAMO));
        prestamoRepository.save(prestamo);

        Prestamo nuevoPrestamo = Prestamo.builder()
                .usuario(prestamo.getUsuario())
                .inventario(prestamo.getInventario())
                .fechaPrestamo(LocalDateTime.now())
                .fechaVencimiento(LocalDateTime.now().plusDays(DIAS_PRESTAMO))
                .estado("ACTIVO")
                .numRenovaciones(prestamo.getNumRenovaciones() != null
                        ? prestamo.getNumRenovaciones() + 1 : 1)
                .observaciones("Renovación del préstamo #" + prestamo.getId())
                .build();

        nuevoPrestamo = prestamoRepository.save(nuevoPrestamo);
        auditoriaService.registrar("RENOVAR", "PRESTAMO", nuevoPrestamo.getId(),
                "Renovación del préstamo #" + prestamo.getId() + " (" + prestamo.getUsuario().getNombre() + ")");
        return toResponse(nuevoPrestamo);
    }

    private PrestamoResponse toResponse(Prestamo prestamo) {
        return PrestamoResponse.builder()
                .id(prestamo.getId())
                .usuarioId(prestamo.getUsuario().getId())
                .usuarioNombre(prestamo.getUsuario().getNombre())
                .libroTitulo(prestamo.getInventario().getLibro().getTitulo())
                .codigoEjemplar(prestamo.getInventario().getCodigoEjemplar())
                .fechaPrestamo(prestamo.getFechaPrestamo())
                .fechaVencimiento(prestamo.getFechaVencimiento())
                .fechaDevolucion(prestamo.getFechaDevolucion())
                .estado(prestamo.getEstado())
                .observaciones(prestamo.getObservaciones())
                .numRenovaciones(prestamo.getNumRenovaciones())
                .build();
    }
}
