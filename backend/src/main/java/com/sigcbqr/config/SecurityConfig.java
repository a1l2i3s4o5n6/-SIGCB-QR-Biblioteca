package com.sigcbqr.config;

import com.sigcbqr.security.JwtAuthenticationEntryPoint;
import com.sigcbqr.security.JwtAuthenticationFilter;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.security.authentication.AuthenticationManager;
import org.springframework.security.config.annotation.authentication.configuration.AuthenticationConfiguration;
import org.springframework.security.config.annotation.method.configuration.EnableMethodSecurity;
import org.springframework.security.config.annotation.web.builders.HttpSecurity;
import org.springframework.security.config.annotation.web.configuration.EnableWebSecurity;
import org.springframework.security.config.http.SessionCreationPolicy;
import org.springframework.security.crypto.bcrypt.BCryptPasswordEncoder;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.http.HttpMethod;
import org.springframework.security.web.SecurityFilterChain;
import org.springframework.security.web.authentication.UsernamePasswordAuthenticationFilter;

@Configuration
@EnableWebSecurity
@EnableMethodSecurity
public class SecurityConfig {

    private final JwtAuthenticationFilter jwtAuthenticationFilter;
    private final JwtAuthenticationEntryPoint jwtAuthenticationEntryPoint;

    public SecurityConfig(JwtAuthenticationFilter jwtAuthenticationFilter,
                          JwtAuthenticationEntryPoint jwtAuthenticationEntryPoint) {
        this.jwtAuthenticationFilter = jwtAuthenticationFilter;
        this.jwtAuthenticationEntryPoint = jwtAuthenticationEntryPoint;
    }

    @Bean
    public SecurityFilterChain filterChain(HttpSecurity http) throws Exception {
        http
            .csrf(csrf -> csrf.disable())
            .cors(cors -> {})
            .exceptionHandling(ex -> ex.authenticationEntryPoint(jwtAuthenticationEntryPoint))
            .sessionManagement(session -> session.sessionCreationPolicy(SessionCreationPolicy.STATELESS))
            .authorizeHttpRequests(auth -> auth
                // /api/auth/me DEBE ir antes que el permitAll de /api/auth/**:
                // las reglas se evaluan en orden y gana la primera que casa.
                //
                // Con /api/auth/** en permitAll, la peticion sin token llegaba al
                // controlador, @AuthenticationPrincipal valia null y el metodo
                // lanzaba NullPointerException al invocar userPrincipal.id().
                // El resultado era un 500 donde corresponde un 401. Lo detecto
                // OWASP ZAP, no la auditoria propia, porque esta solo sondea
                // rutas cuyo comportamiento correcto ya conoce.
                //
                // Se resuelve en la capa de seguridad y no con una comprobacion
                // de null en el controlador: decidir si una peticion esta
                // autenticada es responsabilidad del filtro, no del metodo.
                .requestMatchers("/api/auth/me").authenticated()
                .requestMatchers("/api/auth/**").permitAll()
                .requestMatchers("/swagger-ui/**", "/api-docs/**", "/v3/api-docs/**").authenticated()
                .requestMatchers("/api/dashboard/**").authenticated()
                .requestMatchers(HttpMethod.POST, "/api/autores", "/api/editoriales", "/api/categorias")
                    .hasAnyRole("ADMIN", "BIBLIOTECARIO")
                .requestMatchers(HttpMethod.PUT, "/api/autores/*", "/api/editoriales/*", "/api/categorias/*")
                    .hasAnyRole("ADMIN", "BIBLIOTECARIO")
                .requestMatchers(HttpMethod.DELETE, "/api/editoriales/*", "/api/categorias/*")
                    .hasAnyRole("ADMIN", "BIBLIOTECARIO")
                .requestMatchers("/api/**").authenticated()
                .anyRequest().permitAll()
            )
            .addFilterBefore(jwtAuthenticationFilter, UsernamePasswordAuthenticationFilter.class);

        return http.build();
    }

    @Bean
    public PasswordEncoder passwordEncoder() {
        return new BCryptPasswordEncoder();
    }

    @Bean
    public AuthenticationManager authenticationManager(AuthenticationConfiguration config) throws Exception {
        return config.getAuthenticationManager();
    }
}
