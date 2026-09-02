<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
    <div>
        <h1 class="text-xl font-bold text-gray-800">Dashboard</h1>
        <p class="text-sm text-gray-500 mt-0.5">Resumen general del sistema y actividad reciente</p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <span class="inline-flex items-center gap-1.5 text-sm text-gray-500">
            <i class="fas fa-calendar-alt text-primary-400"></i>
            <span>{{ now()->format('d/m/Y') }}</span>
        </span>

        <form method="GET" action="{{ route('dashboard') }}"
              x-data="rangoFechas({ desde: '{{ $desde ?? '' }}', hasta: '{{ $hasta ?? '' }}' })"
              class="flex flex-wrap items-center gap-2">
            <select name="preset" x-model="preset" @change="aplicarPreset($event.target.value)"
                class="rounded-lg border-gray-200 text-xs text-gray-600 focus:border-primary-400 focus:ring-primary-400">
                <option value="hoy" @if(($preset ?? '') === 'hoy') selected @endif>Hoy</option>
                <option value="7" @if(($preset ?? '') === '7') selected @endif>Últimos 7 días</option>
                <option value="30" @if(($preset ?? '') === '30') selected @endif>Últimos 30 días</option>
                <option value="mes" @if(($preset ?? '') === 'mes') selected @endif>Este mes</option>
                <option value="anno" @if(($preset ?? '') === 'anno') selected @endif>Este año</option>
                <option value="personalizado" @if(($preset ?? '') === 'personalizado') selected @endif>Personalizado</option>
            </select>

            <div x-show="preset === 'personalizado'" class="flex flex-wrap items-center gap-2">
                <input type="date" name="desde" x-model="desde"
                       class="rounded-lg border-gray-200 text-xs text-gray-600 focus:border-primary-400 focus:ring-primary-400">
                <span class="text-xs text-gray-400">→</span>
                <input type="date" name="hasta" x-model="hasta"
                       class="rounded-lg border-gray-200 text-xs text-gray-600 focus:border-primary-400 focus:ring-primary-400">
            </div>

            <button type="submit"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium text-white bg-primary-500 hover:bg-primary-600 transition">
                <i class="fas fa-filter"></i> Aplicar
            </button>

            <a href="{{ route('dashboard') }}" class="px-2 py-1.5 text-xs text-gray-500 hover:text-primary-500" title="Limpiar filtro">
                <i class="fas fa-times"></i>
            </a>
        </form>
    </div>
</div>

<script>
    window.rangoFechas = function (opts) {
        const iso = (d) => d.toISOString().slice(0, 10);
        return {
            desde: opts.desde || '',
            hasta: opts.hasta || '',
            preset: '{{ $preset ?? 'hoy' }}',
            aplicarPreset(p) {
                const ahora = new Date();
                let d = new Date(ahora);
                let h = new Date(ahora);
                if (p === '7') d.setDate(ahora.getDate() - 6);
                if (p === '30') d.setDate(ahora.getDate() - 29);
                if (p === 'mes') d = new Date(ahora.getFullYear(), ahora.getMonth(), 1);
                if (p === 'anno') d = new Date(ahora.getFullYear(), 0, 1);
                this.desde = iso(d);
                this.hasta = iso(h);
                if (p !== 'personalizado') {
                    this.$el.closest('form').submit();
                }
            },
        };
    };
</script>