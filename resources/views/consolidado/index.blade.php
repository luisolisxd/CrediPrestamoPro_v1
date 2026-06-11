@extends('layouts.app')

@section('contenido')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Consolidado de Clientes</h1>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full leading-normal">
            <thead>
                <tr class="bg-gray-100 border-b border-gray-200 text-gray-600 text-left text-sm uppercase font-semibold">
                    <th class="px-5 py-3">Cliente</th>
                    <th class="px-5 py-3">Documento</th>
                    <th class="px-5 py-3">Teléfono</th>
                    <th class="px-5 py-3 text-center">Estado</th>
                    <th class="px-5 py-3 text-center">Nº Préstamos</th>
                    <th class="px-5 py-3 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="text-gray-700 text-sm">
                @forelse($clientes as $cliente)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="px-5 py-4">
                            <div class="font-medium text-gray-900">{{ $cliente->nombre }}</div>
                            <div class="text-gray-500 text-xs">{{ $cliente->correo }}</div>
                        </td>
                        <td class="px-5 py-4">{{ $cliente->documento ?? 'N/A' }}</td>
                        <td class="px-5 py-4">{{ $cliente->telefono ?? 'N/A' }}</td>
                        <td class="px-5 py-4 text-center">
                            @if($cliente->estado === 'ACTIVO')
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Activo
                                </span>
                            @else
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Inactivo
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center font-bold">
                            {{ $cliente->prestamos_count }}
                        </td>
                        <td class="px-5 py-4 text-center">
                            <a href="{{ route('consolidado.show', $cliente->id) }}" 
                               class="text-indigo-600 hover:text-indigo-900 font-semibold bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded transition duration-150">
                                Ver Préstamos
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-5 text-center text-gray-500">
                            No se encontraron clientes registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection