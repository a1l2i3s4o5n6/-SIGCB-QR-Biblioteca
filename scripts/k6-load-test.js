// k6-load-test.js — Prueba de carga para SIGCB-QR
// Uso: k6 run scripts/k6-load-test.js
import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
  stages: [
    { duration: '10s', target: 5 },
    { duration: '20s', target: 20 },
    { duration: '10s', target: 0 },
  ],
  thresholds: {
    http_req_duration: ['p(95)<200'],
    http_req_failed: ['rate<0.01'],
  },
};

const BASE_URL = __ENV.BASE_URL || 'http://localhost:8080';

export default function () {
  // Login para obtener cookie
  const loginRes = http.post(`${BASE_URL}/api/auth/login`, JSON.stringify({
    email: 'admin@biblioteca.com',
    password: 'admin123',
  }), {
    headers: { 'Content-Type': 'application/json' },
  });

  check(loginRes, { 'login exitoso': (r) => r.status === 200 });

  const jar = http.cookieJar();
  const cookies = jar.cookiesForURL(`${BASE_URL}/`);

  // Listar libros (con cache)
  const librosRes = http.get(`${BASE_URL}/api/libros?page=0&size=10`, {
    headers: { 'Cookie': `access_token=${cookies.access_token || ''}` },
  });

  check(librosRes, {
    'libros status 200': (r) => r.status === 200,
    'libros p95<200ms': (r) => r.timings.duration < 200,
  });

  sleep(1);
}
