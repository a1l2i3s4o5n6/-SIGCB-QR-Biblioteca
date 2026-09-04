package com.sigcbqr.controller;

import com.sigcbqr.model.dto.response.ApiResponse;
import com.sigcbqr.security.UserPrincipal;
import com.sigcbqr.service.DashboardService;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import org.springframework.format.annotation.DateTimeFormat;
import org.springframework.http.ResponseEntity;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.web.bind.annotation.*;

import java.time.LocalDate;
import java.util.LinkedHashMap;
import java.util.Map;

@RestController
@RequestMapping("/api/dashboard")
@Tag(name = "Dashboard", description = "Estadísticas del panel principal")
public class DashboardController {

    private final DashboardService dashboardService;

    public DashboardController(DashboardService dashboardService) {
        this.dashboardService = dashboardService;
    }

    @GetMapping("/stats")
    @Operation(summary = "Estadísticas", description = "Obtiene las estadísticas principales del dashboard (filtradas por rol)")
    public ResponseEntity<ApiResponse> getStats(@AuthenticationPrincipal UserPrincipal principal) {
        if ("ESTUDIANTE".equals(principal.rol())) {
            Map<String, Object> stats = new LinkedHashMap<>();
            stats.put("misPrestamosActivos", dashboardService.contarPrestamosActivos(principal.id()));
            stats.put("misMultasPendientes", dashboardService.contarMultasPendientes(principal.id()));
            stats.put("misReservasPendientes", dashboardService.contarReservasPendientes(principal.id()));
            return ResponseEntity.ok(ApiResponse.success("Estadísticas del dashboard", stats));
        }
        var stats = dashboardService.getStats();
        return ResponseEntity.ok(ApiResponse.success("Estadísticas del dashboard", stats));
    }

    @GetMapping("/resumen")
    @Operation(summary = "Resumen general", description = "Indicadores, alertas, actividad reciente, series y categorías según el rol (staff o personal)")
    public ResponseEntity<ApiResponse> resumen(
            @AuthenticationPrincipal UserPrincipal principal,
            @RequestParam(value = "desde", required = false)
            @DateTimeFormat(iso = DateTimeFormat.ISO.DATE) LocalDate desde,
            @RequestParam(value = "hasta", required = false)
            @DateTimeFormat(iso = DateTimeFormat.ISO.DATE) LocalDate hasta) {
        var resumen = dashboardService.resumen(principal, desde, hasta);
        return ResponseEntity.ok(ApiResponse.success("Resumen del dashboard", resumen));
    }
}