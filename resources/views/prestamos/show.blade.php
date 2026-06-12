@extends('layouts.app')

@section('contenido')
<div class="mb-4">
    @if(Auth::user()->rol->nombre === 'CLIENTE')
        @php 
            $idDelCliente = Auth::user()->id_cliente ?? Auth::user()->cliente_id; 
        @endphp
        <a href="{{ route('consolidado.show', $idDelCliente) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
            &larr; Volver a Mis Préstamos
        </a>
    @else
        <a href="{{ route('consolidado.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
            &larr; Volver al Consolidado
        </a>
    @endif
</div>

<h1 class="text-2xl font-bold mb-4">Detalle del préstamo #{{ $prestamo->id }}</h1>

<div class="bg-white rounded shadow mb-6 overflow-hidden">
    <div class="bg-gray-800 text-white px-6 py-3 flex justify-between items-center flex-wrap gap-2">
        <h2 class="text-sm font-bold uppercase tracking-wider">
            Resumen de Saldos Financieros Consolidados
        </h2>
        @php
            // 1. Obtener lo estructurado/generado en las cuotas
            $totalInteresEstructurado = (float) $prestamo->cuotas->sum('interes');
            $totalMoraEstructurada = (float) $prestamo->cuotas 
            ->where('capital', 0)
            ->where('interes', 0)
            ->sum('total');
            
            // 2. Determinar lo pagado desde los movimientos para asegurar sincronización en tiempo real
            $capitalPendiente = (float) $prestamo->capital_pendiente;
            $interesPendiente = max(0.00, round($totalInteresEstructurado - (float)$prestamo->interes_generado, 2));
            
            // 🎯 SOLUCIÓN: Sumamos los movimientos tipo 'MORA' para obtener el pago real
            $moraPagadaDesdeMovimientos = (float) $prestamo->cuotas
            ->where('capital', 0)
            ->where('interes', 0)
            ->where('estado', 'PAGADA')
            ->sum('total');
            

            $moraPendienteReal = max(
                0.00,
                round($totalMoraEstructurada - $moraPagadaDesdeMovimientos, 2)
            );
            
            // Deuda Total Pendiente (Franja amarilla)
            $granTotalDeudaPendiente = round($capitalPendiente + $interesPendiente + $moraPendienteReal, 2);
        @endphp
        <span class="bg-yellow-400 text-gray-900 px-3 py-1 rounded text-xs font-black">
            DEUDA TOTAL PENDIENTE: S/ {{ number_format($granTotalDeudaPendiente, 2) }}
        </span>
    </div>
    
    <div class="p-6 bg-gray-50 border-b border-gray-100 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-700">
        <div>
            <p class="mb-1"><strong>Cliente:</strong> {{ $prestamo->cliente->nombre }}</p>
            <p><strong>Tipo de Préstamo:</strong> {{ $prestamo->tipo_prestamo }}</p>
        </div>
        <div>
            <p class="mb-1"><strong>Tasa de Interés:</strong> <span class="text-indigo-600 font-bold">{{ number_format($prestamo->tasa_interes, 2) }}%</span></p>
            <p><strong>Frecuencia de Pago:</strong> {{ $prestamo->frecuencia_pago ?? 'MENSUAL' }}</p>
        </div>
        <div>
            <p class="mb-1"><strong>Estado del Préstamo:</strong> <span class="font-bold {{ $prestamo->estado === 'CERRADO' ? 'text-green-600' : 'text-blue-600' }}">{{ $prestamo->estado }}</span></p>
            <p><strong>N° de Cuotas Contractuales:</strong> {{ $prestamo->numero_cuotas }}</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-center table-fixed border-collapse">
            <thead>
                <tr class="bg-gray-100 text-gray-600 text-xs font-bold uppercase border-b border-gray-200">
                    <th class="p-3 text-left w-1/4 pl-6">Concepto Financiero</th>
                    <th class="p-3 text-blue-600 w-1/4">Monto Otorgado / Generado</th>
                    <th class="p-3 text-green-600 w-1/4">Total Cobrado / Pagado</th>
                    <th class="p-3 text-red-600 w-1/4">Total Saldo Pendiente</th>
                </tr>
            </thead>
            <tbody class="text-sm font-medium text-gray-800 divide-y divide-gray-200">
                <tr>
                    <td class="p-3 text-left pl-6 font-normal text-gray-500">Capital Ordinario</td>
                    <td class="p-3 text-blue-600">S/ {{ number_format($prestamo->capital_prestado, 2) }}</td>
                    <td class="p-3 text-green-600">S/ {{ number_format($prestamo->capital_prestado - $capitalPendiente, 2) }}</td>
                    <td class="p-3 bg-red-50/40 text-red-600 font-bold">S/ {{ number_format($capitalPendiente, 2) }}</td>
                </tr>
                <tr>
                    <td class="p-3 text-left pl-6 font-normal text-gray-500">Interés Ordinario</td>
                    <td class="p-3 text-blue-600">S/ {{ number_format($totalInteresEstructurado, 2) }}</td>
                    <td class="p-3 text-green-600">S/ {{ number_format($prestamo->interes_generado, 2) }}</td>
                    <td class="p-3 bg-red-50/40 text-red-600 font-bold">S/ {{ number_format($interesPendiente, 2) }}</td>
                </tr>
                <tr>
                    <td class="p-3 text-left pl-6 font-normal text-gray-500">Mora por Penalidad</td>
                    <td class="p-3 text-blue-600">S/ {{ number_format($totalMoraEstructurada, 2) }}</td>
                    <td class="p-3 text-green-600">S/ {{ number_format($moraPagadaDesdeMovimientos, 2) }}</td>
                    <td class="p-3 bg-red-50/40 text-red-600 font-bold">S/ {{ number_format($moraPendienteReal, 2) }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="bg-gray-800 text-white font-bold text-sm border-t border-gray-700">
                    <td class="p-3 text-left pl-6 text-yellow-400 font-black">TOTAL CONSOLIDADO (S/)</td>
                    <td class="p-3">S/ {{ number_format($prestamo->capital_prestado + $totalInteresEstructurado + $totalMoraEstructurada, 2) }}</td>
                    <td class="p-3 text-green-400">S/ {{ number_format(($prestamo->capital_prestado - $capitalPendiente) + $prestamo->interes_generado + $moraPagadaDesdeMovimientos, 2) }}</td>
                    <td class="p-3 text-yellow-400 bg-gray-900 font-black">S/ {{ number_format($granTotalDeudaPendiente, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@if($prestamo->tipo_prestamo != 'DINAMICO')
    <div class="bg-white p-6 rounded shadow mb-6">
        <h2 class="text-xl font-bold mb-4">Cuotas del préstamo</h2>

        <table class="w-full table-auto border-collapse">
            <thead>
                <tr class="bg-gray-200 text-gray-700 text-sm">
                    <th class="p-2 border">#</th>
                    <th class="p-2 border">Vencimiento</th>
                    <th class="p-2 border">Capital Contractual</th>
                    <th class="p-2 border">Interés Contractual</th>
                    <th class="p-2 border">Total Fijo</th>
                    <th class="p-2 border">Estado</th>
                    @if(Auth::user()->rol->nombre !== 'CLIENTE')
                        <th class="p-2 border">Acción</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($cuotas as $cuota)
                <tr class="text-center text-sm">
                    <td class="border p-2 font-bold">{{ $cuota->numero_cuota }}</td>
                    <td class="border p-2">{{ $cuota->fecha_vencimiento }}</td>
                    <td class="border p-2">S/ {{ number_format($cuota->capital, 2) }}</td>
                    <td class="border p-2">S/ {{ number_format($cuota->interes, 2) }}</td>
                    <td class="border p-2 font-semibold">S/ {{ number_format($cuota->total, 2) }}</td>
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
                            @if(in_array($cuota->estado, ['PENDIENTE', 'PARCIAL']) || ($cuota->estado == 'PENDIENTE' && $cuota->monto_pagado > 0))
                                <button @click="openModal = true" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs font-semibold cursor-pointer transition">
                                    Cobrar
                                </button>

                                <div x-show="openModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" style="display: none;" x-transition>
                                    <div @click.away="openModal = false" class="bg-white rounded-lg shadow-xl max-w-md w-full p-6 text-left">
                                        <h3 class="text-lg font-bold text-gray-800 mb-4">Procesar Cobro - Cuota #{{ $cuota->numero_cuota }}</h3>
                                        
                                        <form method="POST" action="{{ route('cuotas.pagar', $cuota->id) }}">
                                            @csrf
                                            
                                            @php
                                                $saldoPendienteCuota = round($cuota->total - ($cuota->monto_pagado ?? 0), 2);
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
                                                    <span class="text-xs font-bold text-yellow-800 block">&nbsp;&nbsp;¡Abono Parcial Detectado!</span>
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
                                &mdash;
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

    <table class="w-full table-auto border-collapse">
        <thead>
            <tr class="bg-gray-200 text-gray-700 text-sm">
                <th class="p-2 border">Fecha</th>
                <th class="p-2 border">Detalle de Cuota</th>
                <th class="p-2 border">Tipo</th>
                <th class="p-2 border">Monto Total Recibido</th>
                <th class="p-2 border">Interés Cobrado</th>
                <th class="p-2 border">Mora Cargada</th>
                <th class="p-2 border">Capital Cobrado</th>
                <th class="p-2 border">Capital Final Restante</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movimientos as $movimiento)
            <tr class="text-center text-sm">
                <td class="border p-2">{{ $movimiento->fecha }}</td>
                
                <td class="border p-2 font-medium">
                    @php
                        $esFormatoCuota = str_contains($movimiento->numero_operacion, '::');
                        $cuotaInfo = $esFormatoCuota ? explode('::', $movimiento->numero_operacion) : null;
                        
                        $nombreCuota = $esFormatoCuota ? $cuotaInfo[0] : 'N/A';
                        $estadoPago  = $esFormatoCuota ? $cuotaInfo[1] : 'PAGO';
                    @endphp

                    @if($esFormatoCuota)
                        <div class="flex items-center justify-center gap-1.5">
                            <span class="font-bold text-gray-700">{{ $nombreCuota }}</span>
                            @if($estadoPago === 'PARCIAL')
                                <span class="px-2 py-0.5 text-xs font-extrabold rounded-full bg-blue-100 text-blue-800 tracking-wide">
                                    PARCIAL
                                </span>
                            @else
                                <span class="px-2 py-0.5 text-xs font-extrabold rounded-full bg-green-100 text-green-800 tracking-wide">
                                    COMPLETO
                                </span>
                            @endif
                        </div>
                    @else
                        <span class="text-gray-400 italic text-xs">{{ $movimiento->numero_operacion ?? '—' }}</span>
                    @endif
                </td>

                <td class="border p-2 font-semibold text-gray-600">
                    {{ $movimiento->tipo }}
                </td>
                <td class="border p-2 font-bold text-gray-900">S/ {{ number_format($movimiento->monto, 2) }}</td>
                <td class="border p-2 text-blue-600">S/ {{ number_format($movimiento->interes_cobrado, 2) }}</td>
                <td class="border p-2 text-red-600 font-semibold">S/ {{ number_format($movimiento->mora_cobrada ?? 0, 2) }}</td>
                <td class="border p-2 text-green-600">S/ {{ number_format($movimiento->capital_cobrado, 2) }}</td>
                <td class="border p-2 bg-gray-50 font-bold text-gray-800">S/ {{ number_format($movimiento->capital_final, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection