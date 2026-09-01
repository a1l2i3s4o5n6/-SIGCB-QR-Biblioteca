package com.sigcbqr.config;

import com.sigcbqr.model.dto.response.LibroResponse;
import com.sigcbqr.model.dto.response.PageResponse;
import org.junit.jupiter.api.DisplayName;
import org.junit.jupiter.api.Test;
import org.springframework.data.domain.PageImpl;
import org.springframework.data.domain.PageRequest;
import org.springframework.data.redis.serializer.GenericJackson2JsonRedisSerializer;

import java.util.List;

import static org.junit.jupiter.api.Assertions.*;

/**
 * Regresion del defecto de cache: el valor guardado en Redis por
 * {@code @Cacheable} debe poder deserializarse con el mismo serializador
 * configurado en {@link CacheConfig}.
 *
 * Antes de la correccion, LibroService.listar devolvia Page/PageImpl: el
 * serializador escribia el objeto sin problema (primera peticion, cache miss)
 * pero fallaba al leerlo (peticiones siguientes, cache hit) con
 * SerializationException, de modo que GET /api/libros respondia 500 en todo
 * acierto de cache.
 */
class CacheSerializationTest {

    private final GenericJackson2JsonRedisSerializer serializer = new GenericJackson2JsonRedisSerializer();

    private LibroResponse libroDeEjemplo() {
        LibroResponse libro = new LibroResponse();
        libro.setId(7L);
        libro.setTitulo("Cien anos de soledad");
        return libro;
    }

    @Test
    @DisplayName("PageResponse<LibroResponse> sobrevive el viaje de ida y vuelta por Redis")
    void pageResponseHaceRoundTripPorElSerializadorDeRedis() {
        PageResponse<LibroResponse> original = PageResponse.from(
                new PageImpl<>(List.of(libroDeEjemplo()), PageRequest.of(0, 10), 1));

        byte[] bytes = serializer.serialize(original);
        Object leido = serializer.deserialize(bytes);

        assertInstanceOf(PageResponse.class, leido, "el valor cacheado debe volver como PageResponse");

        @SuppressWarnings("unchecked")
        PageResponse<LibroResponse> recuperado = (PageResponse<LibroResponse>) leido;
        assertEquals(1, recuperado.getContent().size());
        assertEquals(original.getTotalElements(), recuperado.getTotalElements());
        assertEquals(original.getTotalPages(), recuperado.getTotalPages());
        assertEquals(original.getPage(), recuperado.getPage());
        assertEquals(original.getSize(), recuperado.getSize());

        LibroResponse libro = recuperado.getContent().get(0);
        assertEquals(7L, libro.getId(), "los elementos deben conservar su tipo, no volver como Map");
        assertEquals("Cien anos de soledad", libro.getTitulo());
    }

    @Test
    @DisplayName("PageImpl no se puede deserializar: por eso no debe usarse como valor de cache")
    void pageImplNoSePuedeDeserializarYPorEsoNoSeCachea() {
        byte[] bytes = serializer.serialize(
                new PageImpl<>(List.of(libroDeEjemplo()), PageRequest.of(0, 10), 1));

        assertThrows(Exception.class, () -> serializer.deserialize(bytes),
                "si esto dejara de lanzar, el motivo de la correccion habria cambiado");
    }
}
