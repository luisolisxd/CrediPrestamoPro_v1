@extends('layouts.app')

@section('contenido')
<div class="container mx-auto px-4 py-6 max-w-lg">
    <div class="mb-4">
        <a href="{{ route('usuarios.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Volver al listado</a>
    </div>

    <div class="bg-white shadow rounded-lg p-6 border border-gray-200">
        <h1 class="text-xl font-bold text-gray-800 mb-6">Modificar Usuario</h1>

        <form action="{{ route('usuarios.update', $usuario->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre Completo</label>
                <input type="text" name="name" value="{{ $usuario->name }}" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                <input type="email" name="email" value="{{ $usuario->email }}" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nueva Contraseña (Opcional)</label>
                <input type="password" name="password" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Dejar en blanco para no cambiarla">
                @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Rol de Usuario</label>
                <select name="rol_id" id="rol_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" onchange="toggleClienteInput()">
                    @foreach($roles as $rol)
                        <option value="{{ $rol->id }}" data-nombre="{{ $rol->nombre }}" {{ $usuario->rol_id == $rol->id ? 'selected' : '' }}>{{ $rol->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div id="seccion_cliente" class="{{ $usuario->rol->nombre === 'CLIENTE' ? '' : 'hidden' }}">
                <label class="block text-sm font-medium text-red-700 mb-1">Vincular con Cliente (Obligatorio para Rol Cliente)</label>
                <select name="cliente_id" class="w-full border-red-300 rounded-md shadow-sm focus:border-red-500 focus:ring-red-500 bg-red-50">
                    <option value="">-- Selecciona el Cliente Vinculado --</option>
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id }}" {{ $usuario->cliente_id == $cliente->id ? 'selected' : '' }}>{{ $cliente->nombre }} (Doc: {{ $cliente->documento ?? 'N/A' }})</option>
                    @endforeach
                </select>
                @error('cliente_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            @if(Auth::user()->rol->nombre === 'SUPERADMIN')
            <div id="seccion_empresa" class="{{ $usuario->rol->nombre === 'CLIENTE' ? 'hidden' : '' }}">
                <label class="block text-sm font-medium text-gray-700 mb-1">Asignar Empresa</label>
                <select name="empresa_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Ninguna (Acceso Global)</option>
                    @foreach($empresas as $empresa)
                        <option value="{{ $empresa->id }}" {{ $usuario->empresa_id == $empresa->id ? 'selected' : '' }}>{{ $empresa->nombre }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="pt-4">
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded shadow transition duration-150">
                    Actualizar Usuario
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
// Ejecutamos al cargar la vista para asegurar el estado correcto
toggleClienteInput();
</script>
@endsection