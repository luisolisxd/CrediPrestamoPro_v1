@extends('layouts.app')

@section('contenido')
<h1 class="text-2xl font-bold mb-4">Crear préstamo</h1>

<form method="POST" action="{{ route('prestamos.store') }}" class="bg-white p-6 rounded shadow max-w-3xl">
    @csrf

    <label>Cliente</label>
    <select name="cliente_id" class="w-full border rounded p-2 mb-3" required>
        <option value="">Seleccione cliente</option>
        @foreach($clientes as $cliente)
            <option value="{{ $cliente->id }}">{{ $cliente->nombre }}</option>
        @endforeach
    </select>

    <label>Tipo de préstamo</label>
    <select name="tipo_prestamo" class="w-full border rounded p-2 mb-3" required>
        <option value="DINAMICO">DINÁMICO</option>
        <option value="CUOTA_FIJA">CUOTA FIJA</option>
        <option value="CRONOGRAMA">CRONOGRAMA</option>
    </select>

    <label>Capital prestado</label>
    <input type="number" step="0.01" name="capital_prestado" class="w-full border rounded p-2 mb-3" required>

    <label>Número de cuotas</label>
    <input type="number" name="numero_cuotas" class="w-full border rounded p-2 mb-3">

    <label>Frecuencia de pago</label>
    <select name="frecuencia_pago" class="w-full border rounded p-2 mb-3">
        <option value="">No aplica</option>
        <option value="MENSUAL">Mensual</option>
        <option value="QUINCENAL">Quincenal</option>
        <option value="DIAS">Días personalizados</option>
    </select>

    <label>Tasa de interés %</label>
    <input type="number" step="0.01" name="tasa_interes" class="w-full border rounded p-2 mb-3">

    <label>Día de pago</label>
    <input type="number" name="dia_pago" min="1" max="31" class="w-full border rounded p-2 mb-3">

    <label>Fecha de inicio</label>
    <input type="date" name="fecha_inicio" class="w-full border rounded p-2 mb-3">

    <label>Cuotas dobles/adicionales</label>
    <textarea name="cuotas_dobles_adicionales" class="w-full border rounded p-2 mb-3" placeholder="Ejemplo: Julio: 1 cuota adicional, Diciembre: 1 cuota adicional"></textarea>

    <button class="bg-green-600 text-white px-4 py-2 rounded">Guardar préstamo</button>
</form>
@endsection