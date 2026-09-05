import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.store('ui', {
        sidebarOpen: window.innerWidth >= 768,
    });
});

/**
 * Componente de búsqueda en vivo: recarga el fragmento de tabla
 * (contador + filas + paginación) sin recargar la página.
 *
 * x-data="liveTabla({
 *     url: '{{ route('datos.catalogo') }}',
 *     container: 'tabla-catalogo',
 *     campos: ['q', 'categoriaId'],
 *     iniciales: { q: 'texto' }
 * })"
 */
window.liveTabla = function (config) {
    const data = {
        cargando: false,
        _timer: null,

        init() {
            window.tablaIr = (pagina) => this.cargar(pagina);
        },

        alEscribir() {
            clearTimeout(this._timer);
            this._timer = setTimeout(() => this.cargar(1), 350);
        },

        async cargar(pagina = 1) {
            const params = new URLSearchParams({ page: String(Math.max(0, pagina - 1)) });

            (config.campos || []).forEach((campo) => {
                const valor = ('' + (this[campo] ?? '')).trim();
                if (valor !== '') params.set(campo, valor);
            });

            try {
                this.cargando = true;
                const res = await fetch(`${config.url}?${params.toString()}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    accept: 'text/html',
                });
                if (res.ok) {
                    document.getElementById(config.container).innerHTML = await res.text();
                }
            } catch (e) {
                console.error('Error al buscar:', e);
            } finally {
                this.cargando = false;
            }
        },

        limpiar() {
            (config.campos || []).forEach((campo) => (this[campo] = config.iniciales?.[campo] ?? ''));
            this.cargar(1);
        },
    };

    (config.campos || []).forEach((campo) => {
        data[campo] = config.iniciales?.[campo] ?? '';
    });

    return data;
};

/**
 * Formulario de nuevo préstamo: combobox buscable de usuario,
 * filtro de libro y selección de ejemplar (o búsqueda directa por código).
 */
window.formularioPrestamo = function (cfg) {
    return {
        usuarios: cfg.usuarios,
        inventario: cfg.inventario,
        solicitudes: cfg.solicitudes ?? [],
        usuarioId: cfg.oldUsuarioId ?? '',
        usuarioSel: null,
        usuarioQ: '',
        abiertoU: false,
        libroKey: null,
        libroQ: '',
        abiertoL: false,
        inventarioSel: cfg.oldInventarioId ? String(cfg.oldInventarioId) : '',
        codigo: '',
        cargandoCodigo: false,
        errorCodigo: '',

        init() {
            if (this.usuarioId) {
                const u = this.usuarios.find((x) => x.id == this.usuarioId);
                if (u) {
                    this.usuarioSel = u;
                    this.usuarioQ = `${u.nombre} (${u.email})`;
                }
            }
            if (this.inventarioSel) {
                const it = this.inventario.find((x) => x.id == this.inventarioSel);
                if (it) {
                    this.libroKey = this.claveItem(it);
                    this.libroQ = it.libroTitulo;
                }
            }
        },

        claveItem(it) {
            return String(it.libroId ?? 't:' + it.libroTitulo);
        },

        get usuariosFiltrados() {
            const q = this.usuarioQ.trim().toLowerCase();
            const lista = this.usuarios.filter(
                (u) => !q || (u.nombre || '').toLowerCase().includes(q) || (u.email || '').toLowerCase().includes(q)
            );
            return lista.slice(0, 8);
        },

        elegirUsuario(u) {
            this.usuarioId = u.id;
            this.usuarioSel = u;
            this.usuarioQ = `${u.nombre} (${u.email})`;
            this.abiertoU = false;
        },

        limpiarUsuario() {
            this.usuarioId = '';
            this.usuarioSel = null;
            this.usuarioQ = '';
        },

        get librosDisponibles() {
            const mapa = new Map();
            this.inventario.forEach((it) => {
                const clave = this.claveItem(it);
                if (!mapa.has(clave)) mapa.set(clave, { clave, titulo: it.libroTitulo });
            });
            return [...mapa.values()];
        },

        get librosFiltrados() {
            const q = this.libroQ.trim().toLowerCase();
            return this.librosDisponibles
                .filter((l) => !q || l.titulo.toLowerCase().includes(q))
                .slice(0, 8);
        },

        elegirLibro(l) {
            this.libroKey = l.clave;
            this.libroQ = l.titulo;
            this.abiertoL = false;
            this.inventarioSel = '';
        },

        limpiarLibro() {
            this.libroKey = null;
            this.libroQ = '';
            this.inventarioSel = '';
        },

        get ejemplaresDelLibro() {
            if (!this.libroKey) return [];
            return this.inventario.filter((it) => this.claveItem(it) === this.libroKey);
        },

        get solicitantesPorLibro() {
            const m = new Map();
            this.solicitudes.forEach((s) => {
                const clave = String(s.libroId ?? 't:' + s.libroTitulo);
                if (!m.has(clave)) m.set(clave, []);
                m.get(clave).push(s);
            });
            return m;
        },

        get solicitantesActuales() {
            if (!this.libroKey) return [];
            return this.solicitantesPorLibro.get(this.libroKey) ?? [];
        },

        get haySolicitudes() {
            return this.solicitantesActuales.length > 0;
        },

        get nombresSolicitantes() {
            return this.solicitantesActuales.map((s) => s.usuarioNombre ?? 'Usuario').join(', ');
        },

        async buscarPorCodigo(url) {
            this.errorCodigo = '';
            const codigo = this.codigo.trim();
            if (!codigo) {
                this.errorCodigo = 'Escribe un código de ejemplar.';
                return;
            }
            try {
                this.cargandoCodigo = true;
                const res = await fetch(`${url}?codigo=${encodeURIComponent(codigo)}`, {
                    headers: { accept: 'application/json' },
                });
                if (!res.ok) {
                    const body = await res.json().catch(() => ({}));
                    this.errorCodigo = body.mensaje ?? 'No se encontró el ejemplar.';
                    return;
                }
                const ej = await res.json();
                if ((ej.estado || '').toUpperCase() !== 'DISPONIBLE') {
                    this.errorCodigo = `El ejemplar ${ej.codigoEjemplar} no está disponible (estado: ${ej.estado}).`;
                    return;
                }
                this.libroKey = String(ej.libroId ?? 't:' + ej.libroTitulo);
                this.libroQ = ej.libroTitulo;
                this.inventarioSel = String(ej.id);
                this.$nextTick(() => {
                    const select = document.querySelector('select[name="inventarioId"]');
                    if (select && !select.value) {
                        const opt = document.createElement('option');
                        opt.value = ej.id;
                        opt.textContent = `${ej.codigoEjemplar} — ${ej.libroTitulo}`;
                        opt.selected = true;
                        select.appendChild(opt);
                    }
                });
            } finally {
                this.cargandoCodigo = false;
            }
        },
    };
};

/**
 * Formulario de nueva reserva: combobox buscable de usuario y de libro.
 */
window.formularioReserva = function (cfg) {
    return {
        usuarios: cfg.usuarios,
        libros: cfg.libros,
        usuarioId: cfg.oldUsuarioId ?? '',
        usuarioSel: null,
        usuarioQ: '',
        abiertoU: false,
        libroId: cfg.oldLibroId ?? '',
        libroSel: null,
        libroQ: '',
        abiertoL: false,

        init() {
            if (this.usuarioId) {
                const u = this.usuarios.find((x) => x.id == this.usuarioId);
                if (u) {
                    this.usuarioSel = u;
                    this.usuarioQ = `${u.nombre} (${u.email})`;
                }
            }
            if (this.libroId) {
                const l = this.libros.find((x) => x.id == this.libroId);
                if (l) {
                    this.libroSel = l;
                    this.libroQ = l.titulo;
                }
            }
        },

        get usuariosFiltrados() {
            const q = this.usuarioQ.trim().toLowerCase();
            const lista = this.usuarios.filter(
                (u) => !q || (u.nombre || '').toLowerCase().includes(q) || (u.email || '').toLowerCase().includes(q)
            );
            return lista.slice(0, 8);
        },

        elegirUsuario(u) {
            this.usuarioId = u.id;
            this.usuarioSel = u;
            this.usuarioQ = `${u.nombre} (${u.email})`;
            this.abiertoU = false;
        },

        limpiarUsuario() {
            this.usuarioId = '';
            this.usuarioSel = null;
            this.usuarioQ = '';
        },

        get librosFiltrados() {
            const q = this.libroQ.trim().toLowerCase();
            const lista = this.libros.filter(
                (l) => !q || (l.titulo || '').toLowerCase().includes(q)
            );
            return lista.slice(0, 8);
        },

        elegirLibro(l) {
            this.libroId = l.id;
            this.libroSel = l;
            this.libroQ = l.titulo;
            this.abiertoL = false;
        },

        limpiarLibro() {
            this.libroId = '';
            this.libroSel = null;
            this.libroQ = '';
        },
    };
};

Alpine.start();
