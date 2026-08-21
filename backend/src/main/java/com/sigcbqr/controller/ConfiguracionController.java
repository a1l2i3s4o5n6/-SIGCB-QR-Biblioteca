package com.sigcbqr.controller;

import com.sigcbqr.exception.ResourceNotFoundException;
import com.sigcbqr.model.dto.response.ApiResponse;
import com.sigcbqr.model.entity.Configuracion;
import com.sigcbqr.repository.ConfiguracionRepository;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import jakarta.validation.Valid;
import jakarta.validation.constraints.NotBlank;
import lombok.Getter;
import lombok.Setter;
import org.springframework.http.ResponseEntity;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.web.bind.annotation.*;

import java.util.List;

@RestController
@RequestMapping("/api/configuracion")
@Tag(name = "Configuración", description = "Parámetros de configuración del sistema")
public class ConfiguracionController {

    private final ConfiguracionRepository configuracionRepository;

    public ConfiguracionController(ConfiguracionRepository configuracionRepository) {
        this.configuracionRepository = configuracionRepository;
    }

    @GetMapping
    @Transactional(readOnly = true)
    @Operation(summary = "Listar configuraciones", description = "Obtiene todos los parámetros de configuración")
    @PreAuthorize("hasRole('ADMIN')")
    public ResponseEntity<ApiResponse> listar() {
        List<Configuracion> configs = configuracionRepository.findAll();
        return ResponseEntity.ok(ApiResponse.success("Configuraciones obtenidas", configs));
    }

    @PutMapping("/{id}")
    @Operation(summary = "Actualizar configuración", description = "Actualiza el valor de un parámetro de configuración")
    @PreAuthorize("hasRole('ADMIN')")
    public ResponseEntity<ApiResponse> actualizar(@PathVariable Long id,
                                                  @Valid @RequestBody ConfiguracionRequest request) {
        Configuracion config = configuracionRepository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Configuración", id));

        config.setValor(request.getValor());
        config = configuracionRepository.save(config);
        return ResponseEntity.ok(ApiResponse.success("Configuración actualizada", config));
    }

    @Getter @Setter
    public static class ConfiguracionRequest {
        @NotBlank(message = "El valor es obligatorio")
        private String valor;
    }
}