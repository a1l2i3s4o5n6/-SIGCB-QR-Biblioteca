package com.sigcbqr.repository;

import com.sigcbqr.model.entity.Carrera;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.List;

@Repository
public interface CarreraRepository extends JpaRepository<Carrera, Long> {
    List<Carrera> findByFacultadId(Long facultadId);
    List<Carrera> findByActivoTrue();
}
