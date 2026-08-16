package com.sigcbqr.config;

import io.swagger.v3.oas.models.Components;
import io.swagger.v3.oas.models.OpenAPI;
import io.swagger.v3.oas.models.info.Contact;
import io.swagger.v3.oas.models.info.Info;
import io.swagger.v3.oas.models.info.License;
import io.swagger.v3.oas.models.security.SecurityRequirement;
import io.swagger.v3.oas.models.security.SecurityScheme;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;

@Configuration
public class OpenApiConfig {

    @Bean
    public OpenAPI customOpenAPI() {
        final String securitySchemeName = "cookieAuth";

        return new OpenAPI()
                .info(new Info()
                        .title("SIGCB-QR API")
                        .version("1.0.0")
                        .description("API REST del Sistema Integral de Gestión, Control y Seguimiento de Lectura Interna en la Biblioteca Universitaria mediante Códigos QR e Inteligencia Artificial")
                        .contact(new Contact()
                                .name("SIGCB-QR")
                                .email("contacto@sigcbqr.com"))
                        .license(new License()
                                .name("MIT")
                                .url("https://opensource.org/licenses/MIT")))
                .addSecurityItem(new SecurityRequirement().addList(securitySchemeName))
                .components(new Components()
                        .addSecuritySchemes(securitySchemeName, new SecurityScheme()
                                .name(securitySchemeName)
                                .type(SecurityScheme.Type.APIKEY)
                                .in(SecurityScheme.In.COOKIE)
                                .description("Autenticación mediante cookie HttpOnly: access_token")));
    }
}
