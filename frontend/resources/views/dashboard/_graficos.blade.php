@php
    $serie = $resumen['actividadPorDia'] ?? [];
    $categorias = $resumen['prestamosPorCategoria'] ?? [];
@endphp

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    {{-- Actividad del sistema --}}
    <div class="xl:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800 flex items-center">
                <i class="fas fa-chart-line text-primary-400 mr-2"></i> Actividad general del sistema
            </h3>
            <span class="text-xs text-gray-400">{{ $resumen['desde'] ?? '' }} → {{ $resumen['hasta'] ?? '' }}</span>
        </div>
        <div class="p-5">
            @if (count($serie) > 0 && array_sum(array_map(fn($d) => $d['prestamos'] + $d['devoluciones'] + $d['reservas'] + $d['qr'], $serie)) > 0)
                <div class="h-72">
                    <canvas id="graficoActividad"></canvas>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <i class="fas fa-chart-line text-gray-200 text-4xl mb-3"></i>
                    <p class="text-sm text-gray-500">Sin actividad registrada en el período seleccionado.</p>
                    <p class="text-xs text-gray-400 mt-1">Préstamos, devoluciones, reservas y QR se mostrarán aquí.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Préstamos por categoría --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800 flex items-center">
                <i class="fas fa-chart-pie text-primary-400 mr-2"></i> Préstamos por categoría
            </h3>
            <p class="text-xs text-gray-400 mt-0.5">Distribución real del período</p>
        </div>
        <div class="p-5">
            @if (count($categorias) > 0)
                <div class="h-56 relative">
                    <canvas id="graficoCategorias"></canvas>
                </div>
                <ul class="mt-4 space-y-2">
                    @foreach ($categorias as $c)
                        <li class="flex items-center justify-between text-sm">
                            <span class="flex items-center text-gray-600">
                                <span class="w-2.5 h-2.5 rounded-full mr-2 dcat-color"></span>{{ $c['categoria'] }}
                            </span>
                            <span class="font-medium text-gray-700">{{ $c['cantidad'] }}
                                <span class="text-gray-400 text-xs">({{ $c['porcentaje'] }}%)</span>
                            </span>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <i class="fas fa-chart-pie text-gray-200 text-4xl mb-3"></i>
                    <p class="text-sm text-gray-500">No hay préstamos registrados en el período.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    (function () {
        if (typeof Chart === 'undefined') return;

        const serieCat = @json($serie);
        if (serieCat.some(d => d.prestamos + d.devoluciones + d.reservas + d.qr > 0)) {
            new Chart(document.getElementById('graficoActividad'), {
                type: 'line',
                data: {
                    labels: serieCat.map(d => d.fecha),
                    datasets: [
                        { label: 'Préstamos', data: serieCat.map(d => d.prestamos), borderColor: '#63A355', backgroundColor: 'rgba(99,163,85,.12)', fill: true, tension: .35, borderWidth: 2, pointRadius: 2 },
                        { label: 'Devoluciones', data: serieCat.map(d => d.devoluciones), borderColor: '#3B82F6', backgroundColor: 'rgba(59,130,246,.10)', fill: true, tension: .35, borderWidth: 2, pointRadius: 2 },
                        { label: 'Reservas', data: serieCat.map(d => d.reservas), borderColor: '#C9A94E', backgroundColor: 'rgba(201,169,78,.10)', fill: true, tension: .35, borderWidth: 2, pointRadius: 2 },
                        { label: 'Códigos QR', data: serieCat.map(d => d.qr), borderColor: '#6366F1', backgroundColor: 'rgba(99,102,241,.10)', fill: true, tension: .35, borderWidth: 2, pointRadius: 2 },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8, font: { family: 'Poppins', size: 11 } } },
                        tooltip: { mode: 'index', intersect: false },
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { maxTicksLimit: 12, font: { family: 'Poppins', size: 10 } } },
                        y: { beginAtZero: true, ticks: { precision: 0, font: { family: 'Poppins', size: 10 } } },
                    },
                },
            });
        }

        const categorias = @json($categorias);
        if (categorias.length > 0) {
            const paleta = ['#63A355', '#4A7D3F', '#C9A94E', '#3B82F6', '#8B5CF6', '#F59E0B', '#EF4444', '#06B6D4'];
            const cChart = new Chart(document.getElementById('graficoCategorias'), {
                type: 'doughnut',
                data: {
                    labels: categorias.map(c => c.categoria),
                    datasets: [{
                        data: categorias.map(c => c.cantidad),
                        backgroundColor: categorias.map((_, i) => paleta[i % paleta.length]),
                        borderWidth: 2,
                        borderColor: '#ffffff',
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '62%',
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: (ctx) => ` ${ctx.label}: ${ctx.parsed} préstamos` } },
                    },
                },
            });
            const colores = cChart.data.datasets[0].backgroundColor;
            document.querySelectorAll('.dcat-color').forEach((el, i) => el.style.background = colores[i]);
        }
    })();
</script>