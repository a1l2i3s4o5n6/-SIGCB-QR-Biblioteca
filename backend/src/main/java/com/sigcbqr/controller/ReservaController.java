package com.sigcbqr.controller;

import com.sigcbqr.model.dto.request.ReservaRequest;
import com.sigcbqr.model.dto.response.ApiResponse;
import com.sigcbqr.model.dto.response.PageResponse;
import com.sigcbqr.model.dto.response.ReservaResponse;
import com.sigcbqr.model.entity.Reserva;
import com.sigcbqr.repository.LibroRepository;
import com.sigcbqr.repository.ReservaRepository;
import com.sigcbqr.repository.UsuarioRepository;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import jakarta.validation.Valid;
import org.springframework.data.domain.Pageable;
import org.springframework.data.web.PageableDefault;
import org.springframework.http.ResponseEntity;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.web.bind.annotation.*;

import java.time.LocalDateTime;
import java.util.Map;

@RestController
@RequestMapping("/api/reservas")
@Tag(name = "Reservas", description = "Gestión de reservas de libros")
public class ReservaController {

    private final ReservaRepository reservaRepository;
    private final UsuarioRepository usuarioRepository;
    private final LibroRepository libroRepository;
    private final com.sigcbqr.service.AuditoriaService auditoriaService;

    public ReservaController(ReservaRepository reservaRepository,
                             UsuarioRepository usuarioRepository,
                             LibroRepository libroRepository,
                             com.sigcbqr.service.AuditoriaService auditoriaService) {
        this.reservaRepository = reservaRepository;
        this.usuarioRepository = usuarioRepository;
        this.libroRepository = libroRepository;
        this.auditoriaService = auditoriaService;
    }

    @GetMapping
    @Operation(summary = "Listar reservas", description = "Obtiene todas las reservas con paginación")
    public ResponseEntity<PageResponse<ReservaResponse>> listar(
            @PageableDefault(size = 10) Pageable pageable) {
        var page = reservaRepository.findAll(pageable).map(this::toResponse);
        return ResponseEntity.ok(PageResponse.from(page));
    }

    private ReservaResponse toResponse(Reserva reserva) {
        return ReservaResponse.builder()
                .id(reserva.getId())
                .usuarioNombre(reserva.getUsuario().getNombre())
                .libroTitulo(reserva.getLibro().getTitulo())
                .fechaReserva(reserva.getFechaReserva())
                .fechaVencimiento(reserva.getFechaVencimiento())
                .estado(reserva.getEstado())
                .build();
    }

    @PostMapping
    @PreAuthorize("hasAnyRole('ADMIN','BIBLIOTECARIO')")
    @Operation(summary = "Crear reserva", description = "Registra una nueva reserva de libro")
    public ResponseEntity<ApiResponse> crear(@Valid @RequestBody ReservaRequest request) {
        var usuario = usuarioRepository.findById(request.getUsuarioId())
                .orElseThrow(() -> new RuntimeException("Usuario no encontrado"));
        var libro = libroRepository.findById(request.getLibroId())
                .orElseThrow(() -> new RuntimeException("Libro no encontrado"));

        if (reservaRepository.existsByLibroIdAndEstado(request.getLibroId(), "PENDIENTE")) {
            return ResponseEntity.badRequest()
                    .body(ApiResponse.error(400, "El libro ya tiene una reserva pendiente"));
        }

        var reserva = Reserva.builder()
                .usuario(usuario)
                .libro(libro)
                .fechaReserva(LocalDateTime.now())
                .fechaVencimiento(LocalDateTime.now().plusDays(2))
                .estado("PENDIENTE")
                .build();

        reserva = reservaRepository.save(reserva);
        auditoriaService.registrar("CREAR", "RESERVA", reserva.getId(),
                "Reserva de \"" + libro.getTitulo() + "\" por " + usuario.getNombre());
        return ResponseEntity.ok(ApiResponse.created("Reserva registrada", toResponse(reserva)));
    }

    @DeleteMapping("/{id}")
    @PreAuthorize("hasAnyRole('ADMIN','BIBLIOTECARIO')")
    @Operation(summary = "Cancelar reserva", description = "Cancela una reserva existente")
    public ResponseEntity<ApiResponse> cancelar(@PathVariable Long id) {
        var reserva = reservaRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Reserva no encontrada"));
        reserva.setEstado("CANCELADA");
        reservaRepository.save(reserva);
        auditoriaService.registrar("CANCELAR", "RESERVA", reserva.getId(),
                "Reserva de \"" + reserva.getLibro().getTitulo() + "\" cancelada");
        return ResponseEntity.ok(ApiResponse.success("Reserva cancelada", toResponse(reserva)));
    }
}
