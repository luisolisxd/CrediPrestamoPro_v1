@extends('layouts.app')

@section('contenido')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-white p-6 rounded shadow">
        <h2 class="text-lg font-bold">Préstamos activos</h2>
        <p class="text-2xl">{{ $prestamos_activos ?? 0 }}</p>
    </div>
    <div class="bg-white p-6 rounded shadow">
        <h2 class="text-lg font-bold">Préstamos cerrados</h2>
        <p class="text-2xl">{{ $prestamos_cerrados ?? 0 }}</p>
    </div>
    <div class="bg-white p-6 rounded shadow">
        <h2 class="text-lg font-bold">Clientes registrados</h2>
        <p class="text-2xl">{{ $clientes ?? 0 }}</p>
    </div>
    <div class="bg-white p-6 rounded shadow">
        <h2 class="text-lg font-bold">Capital prestado</h2>
        <p class="text-2xl">S/ {{ $capital_prestado ?? 0 }}</p>
    </div>
    <div class="bg-white p-6 rounded shadow">
        <h2 class="text-lg font-bold">Capital pendiente</h2>
        <p class="text-2xl">S/ {{ $capital_pendiente ?? 0 }}</p>
    </div>
    <div class="bg-white p-6 rounded shadow">
        <h2 class="text-lg font-bold">Intereses cobrados</h2>
        <p class="text-2xl">S/ {{ $intereses_cobrados ?? 0 }}</p>
    </div>
</div>
@endsection