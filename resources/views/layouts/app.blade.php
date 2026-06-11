<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CrediPrestamoPro</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">

<div x-data="{ abierto: false }" class="min-h-screen flex">

    <aside
        :class="abierto ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-40 w-64 bg-white shadow transform transition-transform duration-300 md:translate-x-0 md:static md:inset-auto"
    >
        <div class="p-6 text-xl font-bold flex justify-between">
            <span>CrediPrestamoPro</span>
            <button class="md:hidden" @click="abierto = false">✕</button>
        </div>

        <nav class="mt-4 text-sm">
            <a class="block p-3 hover:bg-gray-200" href="{{ route('panel') }}">🏠 Panel</a>

            <div class="p-3">💰 Préstamos</div>
            <a class="block pl-8 p-2 hover:bg-gray-200" href="{{ route('prestamos.index') }}">Listar préstamos</a>
            <a class="block pl-8 p-2 hover:bg-gray-200" href="{{ route('prestamos.create') }}">Crear préstamo</a>

            <a class="block p-3 hover:bg-gray-200" href="{{ route('consolidado') }}">📊 Consolidado</a>
            <a class="block p-3 hover:bg-gray-200" href="{{ route('clientes.index') }}">👥 Clientes</a>

            <div class="p-3">⚙ Configuración</div>
            <a class="block pl-8 p-2 hover:bg-gray-200" href="{{ route('empresas.index') }}">Empresas</a>
            <a class="block pl-8 p-2 hover:bg-gray-200" href="{{ route('usuarios.index') }}">Usuarios</a>
            <a class="block pl-8 p-2 hover:bg-gray-200" href="{{ route('formas_pago.index') }}">Formas de pago</a>
            <a class="block pl-8 p-2 hover:bg-gray-200" href="{{ route('auditorias.index') }}">Auditoría</a>
            <a class="block pl-8 p-2 hover:bg-gray-200" href="{{ route('backups.index') }}">Backups</a>

            <form method="POST" action="{{ route('logout') }}" class="p-3">
                @csrf
                <button type="submit" class="hover:underline">Salir</button>
            </form>
        </nav>
    </aside>

    <div
        x-show="abierto"
        @click="abierto = false"
        class="fixed inset-0 bg-black bg-opacity-40 z-30 md:hidden"
    ></div>

    <main class="flex-1 w-full">
        <header class="bg-white shadow p-4 flex items-center gap-4 md:hidden">
            <button @click="abierto = true" class="text-2xl">☰</button>
            <h1 class="font-bold">CrediPrestamoPro</h1>
        </header>

        <section class="p-4 md:p-6">
            @yield('contenido')
        </section>
    </main>

</div>

</body>
</html>