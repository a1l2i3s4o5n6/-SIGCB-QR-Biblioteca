package com.sigcbqr.controller;

import com.sigcbqr.model.dto.response.ApiResponse;
import com.sigcbqr.service.DashboardService;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/dashboard")
@Tag(name = "Dashboard", description = "Estadísticas del panel principal")
public class DashboardController {

    private final DashboardService dashboardService;

    public DashboardController(DashboardService dashboardService) {
        this.dashboardService = dashboardService;
    }

    @GetMapping("/stats")
    @Operation(summary = "Estadísticas", description = "Obtiene las estadísticas principales del dashboard")
    public ResponseEntity<ApiResponse> getStats() {
        var stats = dashboardService.getStats();
        return ResponseEntity.ok(ApiResponse.success("Estadísticas del dashboard", stats));
    }
}
