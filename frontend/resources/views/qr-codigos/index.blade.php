<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Códigos QR</h1>
                <p class="text-sm text-gray-500 mt-0.5">Generación y gestión de códigos QR de libros</p>
            </div>
            <a href="{{ route('dashboard') }}"
                class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 transition">
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </a>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="mb-4 flex items-center px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            <span><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg">
            <i class="fas fa-exclamation-circle mr-2"></i>
            @foreach ($errors->all() as $error)
                <span>{{ $error }}</span>
            @endforeach
        </div>
    @endif

    {{-- Formulario para crear un nuevo código QR --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <form method="POST" action="{{ route('qr-codigos.store') }}" class="p-5 flex flex-col sm:flex-row sm:items-end gap-3">
            @csrf
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">ID del libro</label>
                <input type="number" name="libroId" min="1" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400"
                    placeholder="Ej. 4">
            </div>
            <button type="submit"
                class="btn-primary-custom px-6 py-2 rounded-lg text-sm font-semibold text-white shadow">
                <i class="fas fa-plus mr-2"></i> Generar código QR
            </button>
        </form>
    </div>

    {{-- Listado de códigos QR --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Código QR</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Libro</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Código</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($qrs as $qr)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="qr-canvas flex items-center justify-center" id="qr-{{ $qr['id'] }}" data-codigo="{{ $qr['codigo'] }}" style="width:110px;height:110px;"></div>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-500">{{ $qr['id'] }}</td>
                            <td class="px-5 py-4 text-sm font-medium text-gray-800">{{ $qr['libroTitulo'] ?? '—' }}</td>
                            <td class="px-5 py-4 text-sm font-mono text-gray-700">{{ $qr['codigo'] }}</td>
                            <td class="px-5 py-4">
                                @if (!empty($qr['activo']))
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Activo</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Inactivo</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <form method="POST" action="{{ route('qr-codigos.toggle', $qr['id']) }}">
                                        @csrf
                                        <input type="hidden" name="activo" value="{{ empty($qr['activo']) ? '1' : '0' }}">
                                        <button type="submit"
                                            class="px-3 py-1.5 rounded-lg text-xs font-medium {{ empty($qr['activo']) ? 'bg-green-600 text-white hover:bg-green-700' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }} transition">
                                            {{ empty($qr['activo']) ? 'Activar' : 'Desactivar' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('qr-codigos.regenerar', $qr['id']) }}">
                                        @csrf
                                        <button type="submit"
                                            class="px-3 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 transition">
                                            <i class="fas fa-sync-alt mr-1"></i> Regenerar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-400">No hay códigos QR registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.qr-canvas').forEach(function (el) {
                var codigo = el.dataset.codigo;
                if (typeof QRCode === 'undefined') {
                    el.textContent = codigo;
                    return;
                }
                try {
                    new QRCode(el, {
                        text: codigo,
                        width: 110,
                        height: 110,
                        colorDark: '#1f2937',
                        colorLight: '#ffffff',
                        correctLevel: QRCode.CorrectLevel.M
                    });
                } catch (e) {
                    el.textContent = codigo;
                }
            });
        });
    </script>
</x-app-layout>
