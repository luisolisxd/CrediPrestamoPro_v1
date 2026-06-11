@extends('layouts.app')

@section('contenido')
<div class="container mx-auto px-4 py-6 max-w-lg">
    <div class="mb-4">
        <a href="{{ route('usuarios.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Volver al listado</a>
    </div>

    <div class="bg-white shadow rounded-lg p-6 border border-gray-200">
        <h1 class="text-xl font-bold text-gray-800 mb-6">Registrar Nuevo Usuario</h1>

        <form action="{{ route('usuarios.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre Completo</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña de Acceso</label>
                <input type="password" name="password" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Mínimo 8 caracteres">
                @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Rol de Usuario</label>
                <select name="role_id" id="rol_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" onchange="toggleClienteInput()">
                    <option value="">-- Selecciona un Rol --</option>
                    @foreach($roles as $rol)
                        <option value="{{ $rol->id }}" data-nombre="{{ $rol->nombre }}">{{ $rol->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div id="seccion_cliente" class="hidden">
                <label class="block text-sm font-medium text-red-700 mb-1">Vincular con Cliente (Obligatorio para Rol Cliente)</label>
                <select name="cliente_id" class="w-full border-red-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500 bg-red-50">
                    <option value="">-- Selecciona el Cliente Vinculado --</option>
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id }}">{{ $cliente->nombre }} (Doc: {{ $cliente->documento ?? 'N/A' }})</option>
                    @endforeach
                </select>
                @error('cliente_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            @if(Auth::user()->rol->nombre === 'SUPERADMIN')
            <div id="seccion_empresa">
                <label class="block text-sm font-medium text-gray-700 mb-1">Asignar Empresa (Solo para Administradores)</label>
                <select name="empresa_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Ninguna (Acceso Global)</option>
                    @foreach($empresas as $empresa)
                        <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="pt-4">
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow transition duration-150">
                    Guardar Usuario
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleClienteInput() {
    var selectRol = document.getElementById('rol_id');
    var opcionSeleccionada = selectRol.options[selectRol.selectedIndex];
    var nombreRol = opcionSeleccionada.getAttribute('data-nombre');
    
    var seccionCliente = document.getElementById('seccion_cliente');
    var seccionEmpresa = document.getElementById('seccion_empresa');

    if (nombreRol === 'CLIENTE') {
        seccionCliente.classList.remove('hidden');
        if(seccionEmpresa) seccionEmpresa.classList.add('hidden');
    } else {
        seccionCliente.classList.add('hidden');
        if(seccionEmpresa) seccionEmpresa.classList.remove('hidden');
    }
}
</script>
@endsection