<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 text-xs uppercase bg-gray-50">
                    <th class="px-5 py-3 font-medium">Nombre</th>
                    <th class="px-5 py-3 font-medium">Correo</th>
                    <th class="px-5 py-3 font-medium">Teléfono</th>
                    <th class="px-5 py-3 font-medium text-center">Rol</th>
                    <th class="px-5 py-3 font-medium text-center">Estado</th>
                    <th class="px-5 py-3 font-medium text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($usuarios as $usuario)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3">
                            <a href="{{ route('usuarios.show', $usuario['id']) }}"
                                class="font-semibold text-gray-800 hover:text-primary-400">
                                {{ $usuario['nombre'] ?? '—' }}
                            </a>
                        </td>
                        <td class="px-5 py-3 text-gray-600">{{ $usuario['email'] ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ $usuario['telefono'] ?? '—' }}</td>
                        <td class="px-5 py-3 text-center">
                            @php
                                $rolColor = match($usuario['rol'] ?? '') {
                                    'ADMIN' => 'bg-red-50 text-red-700',
                                    'BIBLIOTECARIO' => 'bg-blue-50 text-blue-700',
                                    'ESTUDIANTE' => 'bg-green-50 text-green-700',
                                    default => 'bg-gray-100 text-gray-700',
                                };
                            @endphp
                            <span class="px-2 py-1 {{ $rolColor }} text-[11px] font-medium rounded-full">{{ $usuario['rol'] ?? '—' }}</span>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <span class="px-2 py-1 {{ ($usuario['activo'] ?? false) ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }} text-[11px] font-medium rounded-full">
                                {{ ($usuario['activo'] ?? false) ? 'ACTIVO' : 'INACTIVO' }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('usuarios.show', $usuario['id']) }}"
                                    title="Ver detalle"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 text-gray-600 hover:bg-primary-50 hover:text-primary-400 transition">
                                    <i class="fas fa-eye text-xs"></i>
                                </a>
                                <a href="{{ route('usuarios.edit', $usuario['id']) }}"
                                    title="Editar"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 text-gray-600 hover:bg-blue-50 hover:text-blue-500 transition">
                                    <i class="fas fa-pen text-xs"></i>
                                </a>
                                @if (($usuario['activo'] ?? false) && (($usuario['email'] ?? '') !== session('user.email', '')))
                                    <form method="POST" action="{{ route('usuarios.destroy', $usuario['id']) }}"
                                        onsubmit="return confirm('¿Desactivar este usuario?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            title="Desactivar"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 text-gray-600 hover:bg-red-50 hover:text-red-500 transition">
                                            <i class="fas fa-user-slash text-xs"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center">
                            <i class="fas fa-users text-gray-300 text-3xl mb-3"></i>
                            <p class="text-gray-400">No se encontraron usuarios con los criterios indicados.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-5 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
        <p class="text-xs text-gray-500">
            Mostrando <span class="font-semibold">{{ count($usuarios) }}</span> de {{ number_format($total) }} usuarios · Página {{ $page + 1 }} de {{ $totalPages }}
        </p>
        @if ($totalPages > 1)
            <div class="flex gap-1.5">
                @if (!$first)
                    <button type="button" onclick="tablaIr({{ $page }})"
                        class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">← Anterior</button>
                @endif
                @if (!$last)
                    <button type="button" onclick="tablaIr({{ $page + 2 }})"
                        class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">Siguiente →</button>
                @endif
            </div>
        @endif
    </div>
</div>
