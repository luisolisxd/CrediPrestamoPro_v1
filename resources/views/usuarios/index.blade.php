@extends('layouts.app')

@section('contenido')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Control de Usuarios</h1>
        <a href="{{ route('usuarios.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded shadow transition duration-150">
            + Nuevo Usuario
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full leading-normal">
            <thead>
                <tr class="bg-gray-100 border-b border-gray-200 text-gray-600 text-left text-sm uppercase font-semibold">
                    <th class="px-5 py-3">Nombre</th>
                    <th class="px-5 py-3">Correo Electrónico</th>
                    <th class="px-5 py-3">Rol / Permiso</th>
                    <th class="px-5 py-3">Empresa</th>
                    <th class="px-5 py-3 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="text-gray-700 text-sm">
                @forelse($usuarios as $usr)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="px-5 py-4 font-medium text-gray-900">{{ $usr->name }}</td>
                        <td class="px-5 py-4">{{ $usr->email }}</td>
                        <td class="px-5 py-4">
                            <span class="px-2.5 py-1 text-xs font-bold rounded bg-blue-100 text-blue-800 uppercase">
                                {{ $usr->rol->nombre ?? 'Sin Rol' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-gray-500">
                            {{ $usr->empresa->nombre ?? 'Global / Todo' }}
                        </td>
                        <td class="px-5 py-4 text-center">
                            <a href="{{ route('usuarios.edit', $usr->id) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded transition duration-150">
                                Editar
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-5 text-center text-gray-500">
                            No hay usuarios registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection