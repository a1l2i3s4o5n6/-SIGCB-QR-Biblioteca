<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Validar código QR</h1>
                <p class="text-sm text-gray-500 mt-0.5">Escanea o ingresa un código QR para ver la información del libro</p>
            </div>
            @if (in_array(session('rol'), ['ADMIN', 'BIBLIOTECARIO']))
                <a href="{{ route('qr-codigos.index') }}"
                    class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 transition">
                    <i class="fas fa-qrcode mr-1"></i> Gestionar códigos
                </a>
            @endif
        </div>
    </x-slot>

    @if ($error)
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ $error }}
        </div>
    @endif

    {{-- Formulario de búsqueda --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <form method="GET" action="{{ route('qr-codigos.validar') }}" class="p-5 flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Código QR</label>
                <input type="text" name="codigo" value="{{ $codigo }}" required
                    placeholder="Escanea o pega el código aquí (ej. QR-4-XXXXXX)"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary-400">
            </div>
            <div class="flex items-end">
                <button type="submit"
                    class="btn-primary-custom px-6 py-2.5 rounded-lg text-sm font-semibold text-white shadow">
                    <i class="fas fa-search mr-2"></i> Validar
                </button>
            </div>
        </form>
    </div>

    @if ($resultado)
        {{-- Resultado de la validación --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-200 bg-green-50/60 flex items-center gap-3">
                <span class="w-9 h-9 flex items-center justify-center rounded-full bg-green-500 text-white">
                    <i class="fas fa-check text-sm"></i>
                </span>
                <div>
                    <h2 class="text-sm font-bold text-green-700">Código QR válido</h2>
                    <p class="text-xs text-green-600">El código corresponde a un libro activo en el catálogo.</p>
                </div>
            </div>
            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="qr-result flex items-center justify-center bg-gray-50 rounded-xl border border-gray-200 p-6"
                    id="qr-result-{{ $resultado['id'] }}" data-codigo="{{ route('qr-codigos.validar', ['codigo' => $resultado['codigo']]) }}" data-texto="{{ $resultado['codigo'] }}" style="min-height:170px;"></div>
                <div class="space-y-3">
                    <div>
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Título</p>
                        <p class="text-base font-bold text-gray-800">{{ $resultado['libroTitulo'] ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Código</p>
                        <p class="text-sm font-mono text-gray-700">{{ $resultado['codigo'] }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="px-2 py-1 rounded-full text-[11px] font-medium bg-green-50 text-green-700">
                            <i class="fas fa-circle mr-1"></i>Activo
                        </span>
                        <span class="px-2 py-1 rounded-full text-[11px] font-medium bg-gray-100 text-gray-600">ID libro #{{ $resultado['libroId'] }}</span>
                    </div>
                    <p class="text-[11px] text-gray-400">
                        Generado el {{ !empty($resultado['createdAt']) ? \Carbon\Carbon::parse($resultado['createdAt'])->format('d/m/Y H:i') : '—' }}
                    </p>
                    <div class="pt-2 border-t border-gray-100 mt-2">
                        <a href="{{ route('reservas.create', ['libro' => $resultado['libroId'] ?? '']) }}"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg shadow transition">
                            <i class="fas fa-hand-holding-heart"></i>
                            Solicitar préstamo
                        </a>
                        <p class="text-[11px] text-gray-400 mt-1.5">Crea una reserva para poder retirar este libro.</p>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var el = document.getElementById('qr-result-{{ $resultado['id'] }}');
                if (el && typeof QRCode !== 'undefined') {
                    new QRCode(el, {
                        text: el.dataset.codigo,
                        width: 150,
                        height: 150,
                        colorDark: '#1f2937',
                        colorLight: '#ffffff',
                        correctLevel: QRCode.CorrectLevel.M
                    });
                }
            });
        </script>
    @endif
</x-app-layout>