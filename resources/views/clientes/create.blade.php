@extends('layouts.app')

@section('contenido')
<h1 class="text-2xl font-bold mb-4">Crear cliente</h1>

<form method="POST" action="{{ route('clientes.store') }}" class="bg-white p-6 rounded shadow max-w-2xl">
    @csrf

    <label>Nombre</label>
    <input name="nombre" class="w-full border rounded p-2 mb-3" required>

    <label>Documento</label>
    <input name="documento" class="w-full border rounded p-2 mb-3">

    <label>Teléfono</label>
    <input name="telefono" class="w-full border rounded p-2 mb-3">

    <label>Dirección</label>
    <input name="direccion" class="w-full border rounded p-2 mb-3">

    <label>Correo</label>
    <input name="correo" type="email" class="w-full border rounded p-2 mb-3">

    <button class="bg-blue-600 text-white px-4 py-2 rounded">Guardar cliente</button>
</form>
@endsection