@extends('layouts.app')

@section('contenido')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800 leading-tight">
        {{ Auth::user()->rol->nombre === 'CLIENTE' ? __('Mi Resumen Financiero') : __('Dashboard') }}
    </h2>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white overflow-hidden shadow sm:rounded-lg p-6 border-l-4 border-blue-500">
        <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">
            {{ Auth::user()->rol->nombre === 'CLIENTE' ? 'Total Solicitado' : 'Total Prestado' }}
        </div>
        <div class="mt-2 text-3xl font-bold text-gray-900">
            S/ {{ number_format($totalPrestado, 2) }}
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow sm:rounded-lg p-6 border-l-4 border-red-500">
        <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">
            {{ Auth::user()->rol->nombre === 'CLIENTE' ? 'Mi Saldo Pendiente' : 'Total Pendiente' }}
        </div>
        <div class="mt-2 text-3xl font-bold text-gray-900">
            S/ {{ number_format($totalPendiente, 2) }}
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow sm:rounded-lg p-6 border-l-4 border-green-500">
        <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">
            {{ Auth::user()->rol->nombre === 'CLIENTE' ? 'Total Pagado' : 'Total Cobrado' }}
        </div>
        <div class="mt-2 text-3xl font-bold text-gray-900">
            S/ {{ number_format($totalCobrado, 2) }}
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow sm:rounded-lg p-6 border-l-4 border-purple-500">
        <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">
            {{ Auth::user()->rol->nombre === 'CLIENTE' ? 'Mis Préstamos' : 'N° Préstamos Registrados' }}
        </div>
        <div class="mt-2 text-3xl font-bold text-gray-900">
            {{ $cantidadPrestamos }}
        </div>
    </div>
</div>

<div class="bg-white overflow-hidden shadow sm:rounded-lg p-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-bold text-gray-800">
            {{ Auth::user()->rol->nombre === 'CLIENTE' ? __('Historial de Mis Préstamos') : __('Últimos Préstamos Registrados') }}
        </h3>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full table-auto text-center border-collapse">
            <thead>
                <tr class="bg-gray-200 text-gray-700">
                    <th class="p-3 text-sm font-semibold">ID</th>
                    @if(Auth::user()->rol->nombre !== 'CLIENTE')
                        <th class="p-3 text-sm font-semibold">Cliente</th>
                    @endif
                    <th class="p-3 text-sm font-semibold">Tipo</th>
                    <th class="p-3 text-sm font-semibold">Monto</th>
                    <th class="p-3 text-sm font-semibold">Pendiente</th>
                    <th class="p-3 text-sm font-semibold">Estado</th>
                    <th class="p-3 text-sm font-semibold">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($prestamosRecientes as $prestamo)
                    <tr>
                        <td class="border p-3 text-sm font-medium text-gray-900">
                            #{{ $prestamo->id }}
                        </td>
                        @if(Auth::user()->rol->nombre !== 'CLIENTE')
                            <td class="border p-3 text-sm text-gray-600">
                                {{ $prestamo->cliente->nombre ?? 'Sin cliente asignado' }}
                            </td>
                        @endif
                        <td class="border p-3 text-sm text-gray-600">
                            {{ $prestamo->tipo_prestamo }}
                        </td>
                        <td class="border p-3 text-sm text-gray-900 font-semibold">
                            S/ {{ number_format($prestamo->capital_prestado, 2) }}
                        </td>
                        <td class="border p-3 text-sm text-red-600 font-semibold">
                            S/ {{ number_format($prestamo->capital_pendiente, 2) }}
                        </td>
                        <td class="border p-3 text-sm">
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $prestamo->estado === 'ACTIVO' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $prestamo->estado }}
                            </span>
                        </td>
                        <td class="border p-3 text-sm font-medium">
                            <a href="{{ route('prestamos.show', $prestamo->id) }}" class="text-blue-600 hover:text-blue-900 bg-blue-50 px-3 py-1 rounded inline-block font-semibold">
                                Ver Detalle
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-6 text-center text-sm text-gray-500">
                            No se encontraron préstamos registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection