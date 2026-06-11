
@extends('layouts.app')

@section('contenido')
<h1 class="text-2xl font-bold mb-4">Préstamos</h1>

<a href="{{ route('prestamos.create') }}" class="bg-green-500 text-white px-4 py-2 rounded mb-4 inline-block">Crear Préstamo</a>

<table class="table-auto w-full bg-white rounded shadow">
    <thead>
        <tr class="bg-gray-200">
            <th class="px-4 py-2">ID</th>
            <th class="px-4 py-2">Cliente</th>
            <th class="px-4 py-2">Tipo</th>
            <th class="px-4 py-2">Capital</th>
            <th class="px-4 py-2">Pendiente</th>
            <th class="px-4 py-2">Estado</th>
            <th class="px-4 py-2">Acción</th>
        </tr>
    </thead>
    <tbody>
        @foreach($prestamos as $prestamo)
        <tr>
            <td class="border px-4 py-2">{{ $prestamo->id }}</td>
            <td class="border px-4 py-2">{{ $prestamo->cliente->nombre }}</td>
            <td class="border px-4 py-2">{{ $prestamo->tipo_prestamo }}</td>
            <td class="border px-4 py-2">S/ {{ $prestamo->capital_prestado }}</td>
            <td class="border px-4 py-2">S/ {{ $prestamo->capital_pendiente }}</td>
            <td class="border px-4 py-2">{{ $prestamo->estado }}</td>
            <td class="border px-4 py-2">
                <a href="{{ route('prestamos.show', $prestamo) }}" class="text-blue-500">Ver</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection