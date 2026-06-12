<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CrediPrestamoPro</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden">

    {{-- Agregamos lógica global en Alpine para recordar qué menús están abiertos --}}
    <aside x-data="{ openMenu: '{{ request()->routeIs('prestamos.*') ? 'prestamos' : (request()->routeIs('clientes.*') || request()->routeIs('consolidado.*') ? 'clientes' : '') }}' }" 
           class="w-64 bg-white shadow-md flex flex-col justify-between h-full shrink-0">
        
        <div class="p-5 border-b border-gray-200 shrink-0">
            <span class="text-xl font-bold text-indigo-600 block">CrediPrestamoPro</span>
            <span class="text-xs text-gray-500 font-medium uppercase tracking-wider block mt-1">
                Rol: {{ Auth::user()->rol->nombre }}
            </span>
        </div>

        <nav class="flex-1 p-4 space-y-1 overflow-y-auto min-h-0 [scrollbar-width:thin] text-gray-700 font-medium">
            
            <a class="block p-3 rounded hover:bg-indigo-50 hover:text-indigo-600 transition {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-600' : '' }}" 
               href="{{ route('dashboard') }}">
                📊 Dashboard
            </a>

            @if(Auth::user()->rol->nombre === 'CLIENTE')
                @php
                    $idDelCliente = Auth::user()->id_cliente ?? Auth::user()->cliente_id;
                @endphp
                
                @if($idDelCliente)
                    <a class="block p-3 rounded hover:bg-indigo-50 hover:text-indigo-600 transition {{ request()->routeIs('consolidado.show') ? 'bg-indigo-50 text-indigo-600' : '' }}" 
                       href="{{ route('consolidated.show', $idDelCliente) }}">
                        💰 Mis Préstamos
                    </a>
                @else
                    <div class="p-3 text-xs bg-red-50 text-red-700 rounded italic">
                        ⚠️ Usuario no vinculado a un cliente.
                    </div>
                @endif
            @endif

            @if(Auth::user()->rol->nombre === 'SUPERADMIN' || Auth::user()->rol->nombre === 'ADMIN')
                
                <div class="space-y-1">
                    <button @click="openMenu = (openMenu === 'prestamos' ? '' : 'prestamos')" 
                            class="w-full flex items-center justify-between p-3 rounded hover:bg-gray-50 transition font-semibold text-gray-700 cursor-pointer">
                        <span class="flex items-center gap-2">💵 Préstamos</span>
                        <svg class="h-4 w-4 transform transition-transform duration-200" :class="openMenu === 'prestamos' ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="openMenu === 'prestamos'" x-collapse class="pl-4 space-y-1 bg-gray-50/50 rounded-b p-1" style="display: none;">
                        <a class="block p-2 rounded text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition {{ request()->routeIs('prestamos.index') ? 'bg-indigo-50 text-indigo-600 font-semibold' : '' }}" 
                           href="{{ route('prestamos.index') }}">
                            • Listar préstamos
                        </a>
                        <a class="block p-2 rounded text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition {{ request()->routeIs('prestamos.create') ? 'bg-indigo-50 text-indigo-600 font-semibold' : '' }}" 
                           href="{{ route('prestamos.create') }}">
                            • Crear préstamo
                        </a>
                    </div>
                </div>

                <div class="space-y-1">
                    <button @click="openMenu = (openMenu === 'clientes' ? '' : 'clientes')" 
                            class="w-full flex items-center justify-between p-3 rounded hover:bg-gray-50 transition font-semibold text-gray-700 cursor-pointer">
                        <span class="flex items-center gap-2">👥 Clientes</span>
                        <svg class="h-4 w-4 transform transition-transform duration-200" :class="openMenu === 'clientes' ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="openMenu === 'clientes'" x-collapse class="pl-4 space-y-1 bg-gray-50/50 rounded-b p-1" style="display: none;">
                        <a class="block p-2 rounded text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition {{ request()->routeIs('consolidado.index') ? 'bg-indigo-50 text-indigo-600 font-semibold' : '' }}" 
                           href="{{ route('consolidado.index') }}">
                            • Consolidado General
                        </a>
                        <a class="block p-2 rounded text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition {{ request()->routeIs('clientes.index') ? 'bg-indigo-50 text-indigo-600 font-semibold' : '' }}" 
                           href="{{ route('clientes.index') }}">
                            • Lista de Clientes
                        </a>
                    </div>
                </div>

                <a class="block p-3 rounded hover:bg-indigo-50 hover:text-indigo-600 transition text-gray-700 font-medium {{ request()->routeIs('formas_pago.index') ? 'bg-indigo-50 text-indigo-600' : '' }}" 
                   href="{{ route('formas_pago.index') }}">
                    💳 Formas de pago
                </a>
            @endif

            @if(Auth::user()->rol->nombre === 'SUPERADMIN')
                <div x-data="{ openAdmin: {{ request()->routeIs('usuarios.*') || request()->routeIs('empresas.*') || request()->routeIs('auditorias.*') || request()->routeIs('backups.*') ? 'true' : 'false' }} }" class="space-y-1">
                    <button @click="openAdmin = !openAdmin" 
                            class="w-full flex items-center justify-between p-3 rounded hover:bg-red-50 hover:text-red-600 transition font-semibold text-red-500 cursor-pointer">
                        <span class="flex items-center gap-2">🛠️ Panel de Control</span>
                        <svg class="h-4 w-4 transform transition-transform duration-200" :class="openAdmin ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="openAdmin" x-collapse class="pl-4 space-y-1 bg-red-50/30 rounded-b p-1" style="display: none;">
                        <a class="block p-2 rounded text-sm text-gray-600 hover:bg-red-50 hover:text-red-600 transition {{ request()->routeIs('usuarios.index') ? 'bg-red-50 text-red-600 font-semibold' : '' }}" 
                           href="{{ route('usuarios.index') }}">
                            • Control de Usuarios
                        </a>
                        <a class="block p-2 rounded text-sm text-gray-600 hover:bg-red-50 hover:text-red-600 transition {{ request()->routeIs('empresas.index') ? 'bg-red-50 text-red-600 font-semibold' : '' }}" 
                           href="{{ route('empresas.index') }}">
                            • Empresas
                        </a>
                        <a class="block p-2 rounded text-sm text-gray-600 hover:bg-red-50 hover:text-red-600 transition {{ request()->routeIs('auditorias.index') ? 'bg-red-50 text-red-600 font-semibold' : '' }}" 
                           href="{{ route('auditorias.index') }}">
                            • Auditoría
                        </a>
                        <a class="block p-2 rounded text-sm text-gray-600 hover:bg-red-50 hover:text-red-600 transition {{ request()->routeIs('backups.index') ? 'bg-red-50 text-red-600 font-semibold' : '' }}" 
                           href="{{ route('backups.index') }}">
                            • Backups
                        </a>
                    </div>
                </div>
            @endif

        </nav>

        <div class="p-4 border-t border-gray-200 bg-gray-50 shrink-0">
            <div class="mb-3 px-1">
                <p class="text-sm font-semibold text-gray-800 truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
            </div>
            
            <form id="logout-form" method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" 
                        onclick="event.preventDefault(); this.closest('form').submit();"
                        class="w-full text-left p-2.5 text-sm font-semibold text-red-600 hover:bg-red-50 rounded transition duration-150 cursor-pointer">
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