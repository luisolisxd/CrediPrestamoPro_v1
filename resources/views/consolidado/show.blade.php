@extends('layouts.app')

@section('contenido')
<div class="container mx-auto px-4 py-6">
    <div class="mb-4">
        <a href="{{ route('consolidado.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
            ← Volver al Consolidado
        </a>
    </div>

    <div class="bg-white shadow rounded-lg p-6 mb-6 border border-gray-100">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ $cliente->nombre }}</h1>
                <p class="text-sm text-gray-500">Documento: <span class="font-semibold text-gray-700">{{ $cliente->documento ?? 'No registrado' }}</span></p>
            </div>
            <div>
                @if($cliente->estado === 'ACTIVO')
                    <span class="px-4 py-1.5 text-xs font-bold rounded-full bg-green-100 text-green-800 uppercase">Activo</span>
                @else
                    <span class="px-4 py-1.5 text-xs font-bold rounded-full bg-red-100 text-red-800 uppercase">Inactivo</span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6 pt-4 border-t border-gray-100 text-sm">
            <div>
                <span class="block text-gray-400 font-medium uppercase text-xs">Teléfono</span>
                <span class="text-gray-700 font-medium">{{ $cliente->telefono ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="block text-gray-400 font-medium uppercase text-xs">Correo Electrónico</span>
                <span class="text-gray-700 font-medium">{{ $cliente->correo ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="block text-gray-400 font-medium uppercase text-xs">Dirección</span>
                <span class="text-gray-700 font-medium">{{ $cliente->direccion ?? 'N/A' }}</span>
            </div>
        </div>
    </div>

    <h2 class="text-xl font-bold text-gray-800 mb-4">Préstamos Asociados</h2>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full leading-normal">
            <thead>
                <tr class="bg-gray-100 border-b border-gray-200 text-gray-600 text-left text-sm uppercase font-semibold">
                    <th class="px-5 py-3">Tipo Préstamo</th>
                    <th class="px-5 py-3">Capital Prestado</th>
                    <th class="px-5 py-3">Capital Pendiente</th>
                    <th class="px-5 py-3 text-center">Cuotas</th>
                    <th class="px-5 py-3 text-center">Interés</th>
                    <th class="px-5 py-3 text-center">Estado</th>
                    <th class="px-5 py-3 text-center">Detalles</th>
                </tr>
            </thead>
            <tbody class="text-gray-700 text-sm">
                @forelse($cliente->prestamos as $prestamo)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="px-5 py-4">
                            <span class="font-medium text-gray-900">{{ $prestamo->tipo_prestamo }}</span>
                            <div class="text-xs text-gray-500">Freq: {{ $prestamo->frecuencia_pago }}</div>
                        </td>
                        <td class="px-5 py-4 font-semibold text-gray-900">
                            ${{ number_format($prestamo->capital_prestado, 2) }}
                        </td>
                        <td class="px-5 py-4 font-semibold text-red-600">
                            ${{ number_format($prestamo->capital_pendiente, 2) }}
                        </td>
                        <td class="px-5 py-4 text-center">
                            {{ $prestamo->numero_cuotas }}
                        </td>
                        <td class="px-5 py-4 text-center text-gray-600">
                            {{ $prestamo->tasa_interes }}%
                        </td>
                        <td class="px-5 py-4 text-center">
                            @switch($prestamo->estado)
                                @case('PAGADO')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Pagado</span>
                                    @break
                                @case('PENDIENTE')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pendiente</span>
                                    @break
                                @case('VENCIDO')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Vencido</span>
                                    @break
                                @default
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ $prestamo->estado }}</span>
                            @endswitch
                        </td>
                        <td class="px-5 py-4 text-center">
                            <a href="{{ route('prestamos.show', $prestamo->id) }}" 
                               class="text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-2 py-1 rounded transition duration-150">
                                Ver Ficha completa
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-8 text-center text-gray-400 italic">
                            Este cliente no cuenta con préstamos registrados actualmente.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection