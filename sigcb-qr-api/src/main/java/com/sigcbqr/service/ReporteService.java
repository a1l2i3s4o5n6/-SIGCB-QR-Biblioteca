package com.sigcbqr.service;

import com.sigcbqr.model.entity.Libro;
import com.sigcbqr.model.entity.Prestamo;
import com.sigcbqr.repository.*;
import org.springframework.data.domain.PageRequest;
import org.springframework.stereotype.Service;

import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.LocalTime;
import java.util.*;

@Service
public class ReporteService {

    private final PrestamoRepository prestamoRepository;
    private final MultaRepository multaRepository;
    private final LibroRepository libroRepository;
    private final ReservaRepository reservaRepository;

    public ReporteService(PrestamoRepository prestamoRepository,
                          MultaRepository multaRepository,
                          LibroRepository libroRepository,
                          ReservaRepository reservaRepository) {
        this.prestamoRepository = prestamoRepository;
        this.multaRepository = multaRepository;
        this.libroRepository = libroRepository;
        this.reservaRepository = reservaRepository;
    }

    public Map<String, Object> prestamosDiarios() {
        LocalDateTime inicio = LocalDate.now().atStartOfDay();
        LocalDateTime fin = LocalDate.now().atTime(LocalTime.MAX);

        List<Prestamo> prestamos = prestamoRepository.findByFechaPrestamoBetween(inicio, fin);

        Map<String, Object> result = new LinkedHashMap<>();
        result.put("fecha", LocalDate.now().toString());
        result.put("total", prestamos.size());
        result.put("prestamos", prestamos.stream().map(p -> Map.of(
                "id", p.getId(),
                "usuario", p.getUsuario().getNombre(),
                "libro", p.getInventario().getLibro().getTitulo(),
                "hora", p.getFechaPrestamo().toLocalTime().toString()
        )).toList());
        return result;
    }

    public Map<String, Object> librosMasSolicitados() {
        List<Libro> libros = libroRepository.findMasPrestados(PageRequest.of(0, 10));

        Map<String, Object> result = new LinkedHashMap<>();
        result.put("total", libros.size());
        result.put("libros", libros.stream().map(l -> Map.of(
                "titulo", l.getTitulo(),
                "isbn", l.getIsbn(),
                "vecesPrestado", l.getEjemplaresTotales() - l.getEjemplaresDisponibles()
        )).toList());
        return result;
    }

    public Map<String, Object> multasCobradas() {
        LocalDateTime inicioMes = LocalDate.now().withDayOfMonth(1).atStartOfDay();
        LocalDateTime ahora = LocalDateTime.now();

        double total = multaRepository.totalCobradoEntre(inicioMes, ahora);

        Map<String, Object> result = new LinkedHashMap<>();
        result.put("mes", LocalDate.now().getMonth().toString());
        result.put("totalCobrado", total);
        result.put("pendientes", multaRepository.countByPagadaFalse());
        return result;
    }
}
