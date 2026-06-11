<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CrediPrestamoPro</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden">

    <aside class="w-64 bg-white shadow-md flex flex-col justify-between h-full">
        <div>
            <div class="p-5 border-b border-gray-200">
                <span class="text-xl font-bold text-indigo-600 block">CrediPrestamoPro</span>
                <span class="text-xs text-gray-500 font-medium uppercase tracking-wider block mt-1">
                    Rol: {{ Auth::user()->rol->nombre }}
                </span>
            </div>

            <nav class="p-4 space-y-1 overflow-y-auto">
                
                <a class="block p-3 rounded hover:bg-indigo-50 hover:text-indigo-600 transition text-gray-700 font-medium {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-600' : '' }}" 
                   href="{{ route('dashboard') }}">
                    📊 Dashboard
                </a>

                @if(Auth::user()->rol->nombre === 'CLIENTE')
                    @if(Auth::user()->cliente_id)
                        <a class="block p-3 rounded hover:bg-indigo-50 hover:text-indigo-600 transition text-gray-700 font-medium {{ request()->routeIs('consolidado.show') ? 'bg-indigo-50 text-indigo-600' : '' }}" 
                           href="{{ route('consolidado.show', Auth::user()->cliente_id) }}">
                            💰 Mis Préstamos
                        </a>
                    @else
                        <div class="p-3 text-xs bg-red-50 text-red-700 rounded italic">
                            ⚠️ Usuario no vinculado a un cliente.
                        </div>
                    @endif
                @endif

                @if(Auth::user()->rol->nombre === 'SUPERADMIN' || Auth::user()->rol->nombre === 'ADMIN')
                    
                    <div class="pt-2 pb-1 text-xs font-bold text-gray-400 uppercase tracking-wider pl-3">Préstamos</div>
                    
                    <a class="block pl-8 p-2 rounded hover:bg-indigo-50 hover:text-indigo-600 transition text-gray-600 font-medium {{ request()->routeIs('prestamos.index') ? 'bg-indigo-50 text-indigo-600' : '' }}" 
                       href="{{ route('prestamos.index') }}">
                        Listar préstamos
                    </a>
                    <a class="block pl-8 p-2 rounded hover:bg-indigo-50 hover:text-indigo-600 transition text-gray-600 font-medium {{ request()->routeIs('prestamos.create') ? 'bg-indigo-50 text-indigo-600' : '' }}" 
                       href="{{ route('prestamos.create') }}">
                        Crear préstamo
                    </a>

                    <div class="pt-2 pb-1 text-xs font-bold text-gray-400 uppercase tracking-wider pl-3">Clientes</div>

                    <a class="block p-3 rounded hover:bg-indigo-50 hover:text-indigo-600 transition text-gray-700 font-medium {{ request()->routeIs('consolidado.index') ? 'bg-indigo-50 text-indigo-600' : '' }}" 
                       href="{{ route('consolidado.index') }}">
                        📊 Consolidado General
                    </a>
                    <a class="block p-3 rounded hover:bg-indigo-50 hover:text-indigo-600 transition text-gray-700 font-medium {{ request()->routeIs('clientes.index') ? 'bg-indigo-50 text-indigo-600' : '' }}" 
                       href="{{ route('clientes.index') }}">
                        👥 Lista de Clientes
                    </a>

                    <div class="pt-2 pb-1 text-xs font-bold text-gray-400 uppercase tracking-wider pl-3">Operaciones</div>
                    
                    <a class="block p-3 rounded hover:bg-indigo-50 hover:text-indigo-600 transition text-gray-700 font-medium {{ request()->routeIs('formas_pago.index') ? 'bg-indigo-50 text-indigo-600' : '' }}" 
                       href="{{ route('formas_pago.index') }}">
                        💵 Formas de pago
                    </a>
                @endif

                @if(Auth::user()->rol->nombre === 'SUPERADMIN')
                    <div class="pt-2 pb-1 text-xs font-bold text-red-400 uppercase tracking-wider pl-3">Panel de Control</div>

                    <a class="block p-3 rounded hover:bg-indigo-50 hover:text-indigo-600 transition text-gray-700 font-medium {{ request()->routeIs('usuarios.index') ? 'bg-indigo-50 text-indigo-600' : '' }}" 
                       href="{{ route('usuarios.index') }}">
                        👤 Control de Usuarios
                    </a>
                    <a class="block p-3 rounded hover:bg-indigo-50 hover:text-indigo-600 transition text-gray-700 font-medium {{ request()->routeIs('empresas.index') ? 'bg-indigo-50 text-indigo-600' : '' }}" 
                       href="{{ route('empresas.index') }}">
                        🏢 Empresas
                    </a>
                    <a class="block p-3 rounded hover:bg-indigo-50 hover:text-indigo-600 transition text-gray-700 font-medium {{ request()->routeIs('auditorias.index') ? 'bg-indigo-50 text-indigo-600' : '' }}" 
                       href="{{ route('auditorias.index') }}">
                        🔍 Auditoría
                    </a>
                    <a class="block p-3 rounded hover:bg-indigo-50 hover:text-indigo-600 transition text-gray-700 font-medium {{ request()->routeIs('backups.index') ? 'bg-indigo-50 text-indigo-600' : '' }}" 
                       href="{{ route('backups.index') }}">
                        💾 Backups
                    </a>
                @endif

            </nav>
        </div>

        <div class="p-4 border-t border-gray-200 bg-gray-50">
            <div class="mb-3">
                <p class="text-sm font-semibold text-gray-800 truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
            </div>
            
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full text-left p-2.5 text-sm font-semibold text-red-600 hover:bg-red-50 rounded transition duration-150">
                    🚪 Cerrar Sesión
                </button>
            </form>
        </div>
    </aside>

    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
        @yield('contenido')
    </main>

</body>
</html>