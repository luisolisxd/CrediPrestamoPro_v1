@extends('layouts.app')

@section('contenido')
<h1 class="text-2xl font-bold mb-4">Editar cliente</h1>

<form method="POST" action="{{ route('clientes.update', $cliente) }}" class="bg-white p-6 rounded shadow max-w-2xl">
    @csrf
    @method('PUT')

    <label>Nombre</label>
    <input name="nombre" value="{{ $cliente->nombre }}" class="w-full border rounded p-2 mb-3" required>

    <label>Documento</label>
    <input name="documento" value="{{ $cliente->documento }}" class="w-full border rounded p-2 mb-3">

    <label>Teléfono</label>
    <input name="telefono" value="{{ $cliente->telefono }}" class="w-full border rounded p-2 mb-3">

    <label>Dirección</label>
    <input name="direccion" value="{{ $cliente->direccion }}" class="w-full border rounded p-2 mb-3">

    <label>Correo</label>
    <input name="correo" type="email" value="{{ $cliente->correo }}" class="w-full border rounded p-2 mb-3">

    <label>Estado</label>
    <select name="estado" class="w-full border rounded p-2 mb-3">
        <option value="ACTIVO" @selected($cliente->estado == 'ACTIVO')>ACTIVO</option>
        <option value="INACTIVO" @selected($cliente->estado == 'INACTIVO')>INACTIVO</option>
    </select>

    <button class="bg-blue-600 text-white px-4 py-2 rounded">Actualizar cliente</button>
</form>
@endsection