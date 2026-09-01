package com.sigcbqr.controller;

import com.sigcbqr.model.dto.request.NotificacionRequest;
import com.sigcbqr.model.dto.response.ApiResponse;
import com.sigcbqr.model.dto.response.NotificacionResponse;
import com.sigcbqr.model.dto.response.PageResponse;
import com.sigcbqr.security.UserPrincipal;
import com.sigcbqr.service.NotificacionService;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.data.domain.Pageable;
import org.springframework.data.web.PageableDefault;
import org.springframework.http.ResponseEntity;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.web.bind.annotation.*;

@RestController
@RequestMapping("/api/notificaciones")
@Tag(name = "Notificaciones", description = "Gestión de notificaciones de usuarios")
@RequiredArgsConstructor
public class NotificacionController {

    private final NotificacionService notificacionService;

    @GetMapping
    @Operation(summary = "Mis notificaciones", description = "Notificaciones del usuario autenticado")
    public ResponseEntity<PageResponse<NotificacionResponse>> mis(
            @AuthenticationPrincipal UserPrincipal principal,
            @PageableDefault(size = 10) Pageable pageable) {
        return ResponseEntity.ok(PageResponse.from(notificacionService.listarPorUsuario(principal.id(), pageable)));
    }

    @GetMapping("/todas")
    @PreAuthorize("hasAnyRole('ADMIN', 'BIBLIOTECARIO')")
    @Operation(summary = "Todas las notificaciones", description = "Todas las notificaciones con filtro opcional de leída")
    public ResponseEntity<PageResponse<NotificacionResponse>> todas(
            @RequestParam(required = false) Boolean leida,
            @PageableDefault(size = 10) Pageable pageable) {
        return ResponseEntity.ok(PageResponse.from(notificacionService.listarTodas(leida, pageable)));
    }

    @GetMapping("/no-leidas")
    @Operation(summary = "Contar no leídas", description = "Cantidad de notificaciones sin leer del usuario")
    public ResponseEntity<ApiResponse> noLeidas(@AuthenticationPrincipal UserPrincipal principal) {
        return ResponseEntity.ok(ApiResponse.success("Notificaciones no leídas",
                notificacionService.contarNoLeidas(principal.id())));
    }

    @PostMapping
    @PreAuthorize("hasAnyRole('ADMIN', 'BIBLIOTECARIO')")
    @Operation(summary = "Crear notificación")
    public ResponseEntity<ApiResponse> crear(@Valid @RequestBody NotificacionRequest request) {
        return ResponseEntity.status(201).body(ApiResponse.created(
                "Notificación creada", notificacionService.crear(request)));
    }

    @PutMapping("/{id}/leida")
    @Operation(summary = "Marcar como leída")
    public ResponseEntity<ApiResponse> marcarLeida(@PathVariable Long id) {
        notificacionService.marcarLeida(id);
        return ResponseEntity.ok(ApiResponse.success("Notificación marcada como leída", null));
    }

    @PutMapping("/leer-todas")
    @Operation(summary = "Marcar todas como leídas")
    public ResponseEntity<ApiResponse> marcarTodas(@AuthenticationPrincipal UserPrincipal principal) {
        long marcadas = notificacionService.marcarTodasLeidas(principal.id());
        return ResponseEntity.ok(ApiResponse.success(marcadas + " notificaciones marcadas como leídas", marcadas));
    }
}