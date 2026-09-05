<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Código QR de un libro</h1>
                <p class="text-sm text-gray-500 mt-0.5">Escanea o ingresa el código QR para ver el libro y reservarlo</p>
            </div>
            <span class="px-3 py-1.5 rounded-full text-xs font-semibold bg-primary-100 text-primary-700 uppercase tracking-wide">
                {{ session('rol', 'LECTOR') }}
            </span>
        </div>
    </x-slot>

    @if ($error)
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ $error }}
        </div>
    @endif

    @if (session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    {{-- Formulario de escaneo / búsqueda --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <form method="GET" action="{{ route('estudiante.escaneo-qr') }}" class="p-5 flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Código QR</label>
                <input type="text" name="codigo" value="{{ $codigo }}" required
                    placeholder="Escanea o pega el código aquí (ej. QR-978-0132350884)"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary-400">
            </div>
            <div class="flex items-end">
                <button type="submit"
                    class="btn-primary-custom px-6 py-2.5 rounded-lg text-sm font-semibold text-white shadow">
                    <i class="fas fa-qrcode mr-2"></i> Ver libro
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
                    id="qr-result-student" data-codigo="{{ route('estudiante.escaneo-qr', ['codigo' => $resultado['codigo']]) }}" data-texto="{{ $resultado['codigo'] }}" style="min-height:170px;"></div>
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
                        <a href="{{ route('estudiante.mis-reservas', ['libro' => $resultado['libroId'], 'titulo' => $resultado['libroTitulo'] ?? '']) }}"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg shadow transition">
                            <i class="fas fa-hand-holding-heart"></i>
                            Reservar este libro
                        </a>
                        <p class="text-[11px] text-gray-400 mt-1.5">Te llevará a la sección de reservas con el libro ya seleccionado.</p>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var el = document.getElementById('qr-result-student');
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
    @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-10 text-center text-gray-400">
                <i class="fas fa-qrcode text-4xl mb-3"></i>
                <p class="text-sm">Ingresa el código de la contraportada del libro o el que aparece en el menú <strong>Códigos QR</strong> del personal.</p>
                <p class="text-xs text-gray-400 mt-1">Al validarlo verás el código QR, el nombre del libro y podrás reservarlo.</p>
            </div>
        </div>
    @endif
</x-app-layout>