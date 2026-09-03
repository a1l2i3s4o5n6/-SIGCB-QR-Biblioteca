// k6-load-test.js — Prueba de carga para SIGCB-QR
//
// Mide la latencia de GET /api/libros, el endpoint del catálogo, que es el que
// la caché de Redis respalda (ADR-0006) y al que apuntan REQ-NF-001 y REQ-R-002.
//
// Uso:
//   k6 run scripts/k6-load-test.js
//   k6 run --out json=docs/mediciones/perf/k6-run1.json scripts/k6-load-test.js
//   BASE_URL=http://sigcbqr-api:8080 k6 run scripts/k6-load-test.js   # dentro de la red de compose
//
// NOTA IMPORTANTE sobre el inicio de sesión. La versión anterior de este script
// hacía login en CADA iteración de CADA usuario virtual. Con el límite de tasa de
// 5 intentos por IP y minuto (RateLimitService), a partir del quinto login todas
// las peticiones recibían 429. El resultado era doblemente inválido: la tasa de
// error se disparaba y el p95 salía artificialmente BAJO, porque un 429 se
// responde sin tocar la base de datos. Es decir, la prueba de carga medía la
// velocidad del limitador de tasa, no la del catálogo.
//
// Ahora el login se hace UNA vez en setup(), fuera de la fase de medición, y
// todos los usuarios virtuales reutilizan el mismo token.

import http from 'k6/http';
import { check, sleep } from 'k6';
import { Trend, Rate } from 'k6/metrics';

const BASE_URL = __ENV.BASE_URL || 'http://localhost:8080';
const EMAIL = __ENV.SIGCB_EMAIL || 'admin@biblioteca.com';
const PASSWORD = __ENV.SIGCB_PASSWORD;
if (!PASSWORD) {
  throw new Error('Define SIGCB_PASSWORD en el entorno; no se versiona ninguna contrasena.');
}

// Métrica propia: aísla la latencia del catálogo del resto del tráfico.
const catalogoDuracion = new Trend('catalogo_duracion', true);
const catalogoErrores = new Rate('catalogo_errores');

// Perfil exigido por la guia: 50 usuarios virtuales SOSTENIDOS durante 30 s.
// La rampa de subida y la de bajada quedan fuera del tramo sostenido a
// proposito: las metricas del informe se leen del tramo de 50 VU, no de la
// rampa, que mezcla latencias de arranque con las de regimen.
export const options = {
  stages: [
    { duration: '15s', target: 50 },  // rampa de subida
    { duration: '30s', target: 50 },  // carga sostenida — tramo que se informa
    { duration: '10s', target: 0 },   // descenso
  ],
  thresholds: {
    // Umbral de REQ-NF-001. Se aplica a la métrica propia y no a
    // http_req_duration, que incluiría el login de setup().
    'catalogo_duracion': ['p(95)<200'],
    'catalogo_errores': ['rate<0.01'],
    'http_req_failed': ['rate<0.01'],
  },
};

export function setup() {
  const res = http.post(`${BASE_URL}/api/auth/login`, JSON.stringify({
    email: EMAIL,
    password: PASSWORD,
  }), { headers: { 'Content-Type': 'application/json' } });

  if (res.status !== 200) {
    throw new Error(
      `setup: el inicio de sesión falló con ${res.status}. ` +
      `Si es 429, espere un minuto: el límite de tasa aún tiene intentos recientes en la ventana.`
    );
  }

  const token = res.json('data.token');
  if (!token) {
    throw new Error('setup: la respuesta de login no trae data.token');
  }
  return { token };
}

export default function (data) {
  const params = {
    headers: { Authorization: `Bearer ${data.token}` },
    tags: { endpoint: 'catalogo' },
  };

  const res = http.get(`${BASE_URL}/api/libros?page=0&size=10`, params);

  catalogoDuracion.add(res.timings.duration);
  catalogoErrores.add(res.status !== 200);

  check(res, {
    'catálogo responde 200': (r) => r.status === 200,
    'catálogo devuelve contenido': (r) => {
      // Comprueba la forma real de la respuesta: un acierto de caché mal
      // deserializado devolvía 500 con cuerpo de error (ver ADR-0006), y una
      // prueba que sólo mirase el código de estado no lo habría notado.
      try {
        return Array.isArray(r.json('content'));
      } catch (e) {
        return false;
      }
    },
  });

  sleep(1);
}
