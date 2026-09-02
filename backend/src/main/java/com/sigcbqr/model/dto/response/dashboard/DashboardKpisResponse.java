package com.sigcbqr.model.dto.response.dashboard;

import lombok.*;

import java.util.List;

@Getter @Setter @NoArgsConstructor @AllArgsConstructor @Builder
public class DashboardKpisResponse {

    private long librosRegistrados;
    private long librosNuevosPeriodo;
    private long librosDisponibles;
    private long ejemplaresDisponibles;
    private long ejemplaresPrestados;
    private long ejemplaresDanados;
    private long prestamosActivos;
    private long prestamosVencidos;
    private long prestamosReservados;
    private long prestamosDevueltos;
    private long prestamosProximos24h;
    private long prestamosProximos7dias;
    private long prestamosRegistradosPeriodo;
    private long prestamosDevueltosPeriodo;
    private long usuariosRegistrados;
    private long usuariosNuevosPeriodo;
    private long usuariosActivos;
    private long usuariosConSancionActiva;
    private long reservasPendientes;
    private long reservasConfirmadas;
    private long reservasCompletadas;
    private long reservasCanceladas;
    private long reservasCreadasPeriodo;
    private List<DashboardRolCantidadResponse> usuariosPorRol;
    private long multasPendientes;
    private double totalMultasPendientes;
    private long multasGeneradasPeriodo;
    private long multasPagadasPeriodo;
    private long sancionesActivas;
    private long sancionesVencidas;
    private long sancionesProximas;
    private long sancionesResueltas;
    private long sancionesNuevasPeriodo;
    private long qrActivos;
    private long qrInactivos;
    private long qrCreadosPeriodo;
    private long qrRegeneradosPeriodo;
    private long qrActivadosPeriodo;
    private long qrDesactivadosPeriodo;
}