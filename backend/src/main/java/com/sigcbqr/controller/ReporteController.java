package com.sigcbqr.controller;

import com.sigcbqr.service.ReporteService;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import org.springframework.http.ResponseEntity;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

import java.util.Map;

@RestController
@RequestMapping("/api/reportes")
@Tag(name = "Reportes", description = "Reportes del sistema")
@PreAuthorize("hasAnyRole('ADMIN', 'BIBLIOTECARIO')")
public class ReporteController {

    private final ReporteService reporteService;

    public ReporteController(ReporteService reporteService) {
        this.reporteService = reporteService;
    }

    @GetMapping("/prestamos-diarios")
    @Operation(summary = "Préstamos diarios", description = "Reporte de préstamos del día actual")
    public ResponseEntity<Map<String, Object>> prestamosDiarios() {
        return ResponseEntity.ok(reporteService.prestamosDiarios());
    }

    @GetMapping("/libros-mas-solicitados")
    @Operation(summary = "Libros más solicitados", description = "Top 10 libros más prestados")
    public ResponseEntity<Map<String, Object>> librosMasSolicitados() {
        return ResponseEntity.ok(reporteService.librosMasSolicitados());
    }

    @GetMapping("/multas-cobradas")
    @Operation(summary = "Multas cobradas", description = "Resumen de multas del mes actual")
    public ResponseEntity<Map<String, Object>> multasCobradas() {
        return ResponseEntity.ok(reporteService.multasCobradas());
    }
}
