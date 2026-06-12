@extends('layouts.app')

@section('contenido')
<div class="max-w-4xl mx-auto">
    
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Registrar Nuevo Préstamo</h1>
        <a href="{{ route('prestamos.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm font-semibold transition">
            ← Volver al Listado
        </a>
    </div>

    <div class="bg-white p-6 rounded shadow-md">
        <form method="POST" action="{{ route('prestamos.store') }}" x-data="{ tipoPrestamo: 'CUOTA_FIJA' }">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Seleccionar Cliente <span class="text-red-500">*</span></label>
                    <select name="cliente_id" class="w-full border border-gray-300 rounded p-2.5 focus:ring-2 focus:ring-indigo-500 focus:outline-none" required>
                        <option value="">-- Seleccione un cliente --</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->nombre }} (Doc: {{ $cliente->documento ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                    @error('cliente_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo de Préstamo <span class="text-red-500">*</span></label>
                    <select name="tipo_prestamo" 
                            x-model="tipoPrestamo"
                            class="w-full border border-gray-300 rounded p-2.5 focus:ring-2 focus:ring-indigo-500 focus:outline-none" required>
                        <option value="CUOTA_FIJA">CUOTA FIJA</option>
                        <option value="DINAMICO">DINÁMICO</option>
                        <option value="CRONOGRAMA">CRONOGRAMA</option>
                    </select>
                    @error('tipo_prestamo') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Capital a Prestar (S/) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="1" name="capital_prestado" value="{{ old('capital_prestado') }}" 
                           placeholder="Ejemplo: 5000.00"
                           class="w-full border border-gray-300 rounded p-2.5 focus:ring-2 focus:ring-indigo-500 focus:outline-none" required>
                    @error('capital_prestado') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha de Desembolso / Inicio</label>
                    <input type="date" name="fecha_inicio" value="{{ old('fecha_inicio', date('Y-m-d')) }}"
                           class="w-full border border-gray-300 rounded p-2.5 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    @error('fecha_inicio') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div x-show="tipoPrestamo !== 'DINAMICO'">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Número de Cuotas <span class="text-red-500">*</span></label>
                    <input type="number" min="1" name="numero_cuotas" value="{{ old('numero_cuotas', 12) }}" 
                           ::required="tipoPrestamo !== 'DINAMICO'"
                           class="w-full border border-gray-300 rounded p-2.5 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    @error('numero_cuotas') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div x-show="tipoPrestamo !== 'DINAMICO'">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Frecuencia de Pago</label>
                    <select name="frecuencia_pago" class="w-full border border-gray-300 rounded p-2.5 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="MENSUAL" {{ old('frecuencia_pago') == 'MENSUAL' ? 'selected' : '' }}>MENSUAL</option>
                        <option value="QUINCENAL" {{ old('frecuencia_pago') == 'QUINCENAL' ? 'selected' : '' }}>QUINCENAL</option>
                        <option value="SEMANAL" {{ old('frecuencia_pago') == 'SEMANAL' ? 'selected' : '' }}>SEMANAL</option>
                        <option value="DIARIO" {{ old('frecuencia_pago') == 'DIARIO' ? 'selected' : '' }}>DIARIO</option>
                    </select>
                    @error('frecuencia_pago') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div x-show="tipoPrestamo !== 'DINAMICO'">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tasa de Interés Total (%)</label>
                    <input type="number" step="0.01" min="0" name="tasa_interes" value="{{ old('tasa_interes', 0) }}" 
                           placeholder="Ejemplo: 20.00"
                           class="w-full border border-gray-300 rounded p-2.5 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    @error('tasa_interes') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div x-show="tipoPrestamo !== 'DINAMICO'">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Día de Pago Fijo del Mes (1-31)</label>
                    <input type="number" min="1" max="31" name="dia_pago" value="{{ old('dia_pago', 16) }}" 
                           class="w-full border border-gray-300 rounded p-2.5 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    @error('dia_pago') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Notas / Observaciones sobre Cuotas Dobles</label>
                    <input type="text" name="cuotas_dobles_adicionales" value="{{ old('cuotas_dobles_adicionales') }}" 
                           placeholder="Ejemplo: Diciembre y Julio se cobra cuota doble"
                           class="w-full border border-gray-300 rounded p-2.5 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    @error('cuotas_dobles_adicionales') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

            </div>

            <div class="mt-8 pt-4 border-t border-gray-200 flex justify-end gap-3">
                <button type="reset" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-5 py-2.5 rounded transition cursor-pointer">
                    Limpiar Formulario
                </button>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-6 py-2.5 rounded shadow transition cursor-pointer">
                    🚀 Guardar y Generar Préstamo
                </button>
            </div>
        </form>
    </div>
</div>
@endsection