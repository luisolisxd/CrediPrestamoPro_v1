@extends('layouts.app')

@section('contenido')
<div class="mb-4">
    @if(Auth::user()->rol->nombre === 'CLIENTE')
        <a href="{{ route('consolidado.show', Auth::user()->cliente_id) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
            ← Volver a Mis Préstamos
        </a>
    @else
        <a href="{{ route('consolidado.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
            ← Volver al Consolidado
        </a>
    @endif
</div>

<h1 class="text-2xl font-bold mb-4">Detalle del préstamo #{{ $prestamo->id }}</h1>

<div class="bg-white p-6 rounded shadow mb-6">
    <p><strong>Cliente:</strong> {{ $prestamo->cliente->nombre }}</p>
    <p><strong>Tipo:</strong> {{ $prestamo->tipo_prestamo }}</p>
    <p><strong>Capital prestado:</strong> S/ {{ number_format($prestamo->capital_prestado, 2) }}</p>
    <p><strong>Capital pendiente:</strong> S/ {{ number_format($prestamo->capital_pendiente, 2) }}</p>
    <p><strong>Estado:</strong> {{ $prestamo->estado }}</p>
</div>

{{-- Opciones de registro ocultas para el rol CLIENTE --}}
@if($prestamo->tipo_prestamo == 'DINAMICO')
    @if(Auth::user()->rol->nombre !== 'CLIENTE')
        <div class="bg-white p-6 rounded shadow mb-6">
            <h2 class="text-xl font-bold mb-4">Registrar movimiento dinámico</h2>

            <form method="POST" action="{{ route('prestamos.movimiento', $prestamo) }}">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha</label>
                        <input type="date" name="fecha" class="w-full border rounded p-2" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tipo</label>
                        <select name="tipo" class="w-full border rounded p-2" required>
                            <option value="DEPÓSITO">Depósito</option>
                            <option value="PAGO">Pago</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Monto</label>
                        <input type="number" step="0.01" name="monto" class="w-full border rounded p-2" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Interés cobrado</label>
                        <input type="number" step="0.01" name="interes_cobrado" class="w-full border rounded p-2" value="0">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Capital cobrado</label>
                        <input type="number" step="0.01" name="capital_cobrado" class="w-full border rounded p-2" value="0">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">N° operación</label>
                        <input type="text" name="numero_operacion" class="w-full border rounded p-2">
                    </div>
                </div>

                <button class="mt-4 bg-blue-600 text-white px-4 py-2 rounded">
                    Guardar movimiento
                </button>
            </form>
        </div>
    @endif
@endif

@if($prestamo->tipo_prestamo != 'DINAMICO')
    <div class="bg-white p-6 rounded shadow mb-6">
        <h2 class="text-xl font-bold mb-4">Cuotas del préstamo</h2>

        <table class="w-full table-auto">
            <thead>
                <tr class="bg-gray-200">
                    <th class="p-2">#</th>
                    <th class="p-2">Vencimiento</th>
                    <th class="p-2">Capital</th>
                    <th class="p-2">Interés</th>
                    <th class="p-2">Total</th>
                    <th class="p-2">Estado</th>
                    @if(Auth::user()->rol->nombre !== 'CLIENTE')
                        <th class="p-2">Acción</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($cuotas as $cuota)
                <tr class="text-center">
                    <td class="border p-2">{{ $cuota->numero_cuota }}</td>
                    <td class="border p-2">{{ $cuota->fecha_vencimiento }}</td>
                    <td class="border p-2">S/ {{ number_format($cuota->capital, 2) }}</td>
                    <td class="border p-2">S/ {{ number_format($cuota->interes, 2) }}</td>
                    <td class="border p-2">S/ {{ number_format($cuota->total, 2) }}</td>
                    <td class="border p-2">
                        <span class="px-2 py-1 text-xs font-bold rounded-full 
                            {{ $cuota->estado == 'PENDIENTE' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $cuota->estado == 'PAGADO' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $cuota->estado == 'BLOQUEADA' ? 'bg-gray-100 text-gray-800' : '' }}">
                            {{ $cuota->estado }}
                        </span>
                    </td>
                    {{-- Acciones de Pago ocultas si el usuario es CLIENTE --}}
                    @if(Auth::user()->rol->nombre !== 'CLIENTE')
                        <td class="border p-2">
                            @if($cuota->estado == 'PENDIENTE')
                                <form method="POST" action="{{ route('cuotas.pagar', $cuota->id) }}" class="flex flex-col gap-1 items-center">
                                    @csrf
                                    <input type="date" name="fecha_pago" class="border rounded p-1 text-xs" required>
                                    <input type="number" step="0.01" name="monto_pagado" value="{{ $cuota->total }}" class="border rounded p-1 text-xs w-24 text-center" required>
                                    <input type="text" name="numero_operacion" placeholder="N° operación" class="border rounded p-1 text-xs w-24 text-center">
                                    <button class="bg-green-600 text-white px-3 py-1 rounded text-xs font-semibold">Pagar</button>
                                </form>
                            @elseif($cuota->estado == 'BLOQUEADA')
                                🔒 Bloqueada
                            @else
                                —
                            @endif
                        </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

<div class="bg-white p-6 rounded shadow">
    <h2 class="text-xl font-bold mb-4">Movimientos registrados</h2>

    <table class="w-full table-auto">
        <thead>
            <tr class="bg-gray-200">
                <th class="p-2">Fecha</th>
                <th class="p-2">Tipo</th>
                <th class="p-2">Monto</th>
                <th class="p-2">Interés</th>
                <th class="p-2">Capital cobrado</th>
                <th class="p-2">Capital final</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movimientos as $movimiento)
            <tr class="text-center">
                <td class="border p-2">{{ $movimiento->fecha }}</td>
                <td class="border p-2">{{ $movimiento->tipo }}</td>
                <td class="border p-2">S/ {{ number_format($movimiento->monto, 2) }}</td>
                <td class="border p-2">S/ {{ number_format($movimiento->interes_cobrado, 2) }}</td>
                <td class="border p-2">S/ {{ number_format($movimiento->capital_cobrado, 2) }}</td>
                <td class="border p-2">S/ {{ number_format($movimiento->capital_final, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection