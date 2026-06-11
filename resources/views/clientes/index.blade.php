@extends('layouts.app')

@section('contenido')
<h1 class="text-2xl font-bold mb-4">Clientes</h1>

<a href="{{ route('clientes.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded mb-4 inline-block">Crear Cliente</a>

<table class="table-auto w-full bg-white rounded shadow">
    <thead>
        <tr class="bg-gray-200">
            <th class="px-4 py-2">ID</th>
            <th class="px-4 py-2">Nombre</th>
            <th class="px-4 py-2">Documento</th>
            <th class="px-4 py-2">Teléfono</th>
            <th class="px-4 py-2">Correo</th>
            <th class="px-4 py-2">Estado</th>
            <th class="px-4 py-2">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($clientes as $cliente)
        <tr>
            <td class="border px-4 py-2">{{ $cliente->id }}</td>
            <td class="border px-4 py-2">{{ $cliente->nombre }}</td>
            <td class="border px-4 py-2">{{ $cliente->documento }}</td>
            <td class="border px-4 py-2">{{ $cliente->telefono }}</td>
            <td class="border px-4 py-2">{{ $cliente->correo }}</td>
            <td class="border px-4 py-2">{{ $cliente->estado }}</td>
            <td class="border px-4 py-2">
                <a href="{{ route('clientes.edit', $cliente) }}" class="text-blue-500">Editar</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection