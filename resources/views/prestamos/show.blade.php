@extends('layouts.app')

@section('contenido')
<div class="mb-4">
    @if(Auth::user()->rol->nombre === 'CLIENTE')
        @php 
            $idDelCliente = Auth::user()->id_cliente ?? Auth::user()->cliente_id; 
        @endphp
        <a href="{{ route('consolidado.show', $idDelCliente) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
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
    <p><strong>Total Moras Cobradas:</strong> <span class="text-red-600 font-bold">S/ {{ number_format($prestamo->mora_pagada ?? 0, 2) }}</span></p>
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
                            {{ $cuota->estado == 'PENDIENTE' && ($cuota->monto_pagado > 0) ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $cuota->estado == 'PENDIENTE' && !($cuota->monto_pagado > 0) ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $cuota->estado == 'PAGADA' || $cuota->estado == 'PAGADO' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $cuota->estado == 'BLOQUEADA' ? 'bg-gray-100 text-gray-800' : '' }}">
                            
                            {{ $cuota->estado == 'PENDIENTE' && ($cuota->monto_pagado > 0) ? 'PARCIAL' : $cuota->estado }}
                        </span>
                    </td>
                    
                    @if(Auth::user()->rol->nombre !== 'CLIENTE')
                        <td class="border p-2 text-sm font-medium" x-data="{ openModal: false, esParcial: false }">
                            @if(in_array($cuota->estado, ['PENDIENTE', 'PARCIAL']))
                                <button @click="openModal = true" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs font-semibold cursor-pointer">
                                    Cobrar
                                </button>

                                <div x-show="openModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" style="display: none;">
                                    <div @click.away="openModal = false" class="bg-white rounded-lg shadow-xl max-w-md w-full p-6 text-left">
                                        <h3 class="text-lg font-bold text-gray-800 mb-4">Procesar Cobro - Cuota #{{ $cuota->numero_cuota }}</h3>
                                        
                                        <form method="POST" action="{{ route('cuotas.pagar', $cuota->id) }}">
                                            @csrf
                                            
                                            @php
                                                $saldoPendienteCuota = $cuota->total - ($cuota->monto_pagado ?? 0);
                                            @endphp

                                            <div class="space-y-4">
                                                <div>
                                                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Monto a Cobrar (S/)</label>
                                                    <input type="number" step="0.01" max="{{ $saldoPendienteCuota }}" name="monto_pagado" value="{{ $saldoPendienteCuota }}"
                                                           @input="esParcial = ($el.value < {{ $saldoPendienteCuota }})"
                                                           class="w-full border rounded p-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" required>
                                                    <p class="text-xs text-gray-400 mt-1">Saldo restante en la cuota: S/ {{ number_format($saldoPendienteCuota, 2) }}</p>
                                                </div>

                                                <div x-show="esParcial" x-transition class="bg-yellow-50 border-l-4 border-yellow-500 p-3 rounded" style="display: none;">
                                                    <span class="text-xs font-bold text-yellow-800 block">⚠️ ¡Abono Parcial Detectado!</span>
                                                    <label class="block text-xs font-semibold text-gray-700 mt-2 mb-1">¿Desea agregar un recargo por Mora? (S/)</label>
                                                    <input type="number" step="0.01" min="0" name="mora" value="0"
                                                           class="w-full bg-white border rounded p-1.5 text-sm focus:ring-1 focus:ring-yellow-500 outline-none">
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Fecha de Operación</label>
                                                    <input type="date" name="fecha_pago" value="{{ date('Y-m-d') }}" class="w-full border rounded p-2 text-sm" required>
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">N° de Operación</label>
                                                    <input type="text" name="numero_operacion" placeholder="Opcional" class="w-full border rounded p-2 text-sm">
                                                </div>
                                            </div>

                                            <div class="mt-6 flex justify-end gap-2 border-t pt-4">
                                                <button type="button" @click="openModal = false" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm font-semibold transition">
                                                    Cancelar
                                                </button>
                                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm font-bold shadow transition">
                                                    Confirmar Pago
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
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
                <th class="p-2">Monto Total</th>
                <th class="p-2">Interés</th>
                <th class="p-2">Mora</th>
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
                <td class="border p-2 text-red-600 font-semibold">S/ {{ number_format($movimiento->mora_cobrada ?? 0, 2) }}</td>
                <td class="border p-2">S/ {{ number_format($movimiento->capital_cobrado, 2) }}</td>
                <td class="border p-2">S/ {{ number_format($movimiento->capital_final, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection