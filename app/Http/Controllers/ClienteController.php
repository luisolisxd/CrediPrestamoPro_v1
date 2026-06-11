<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use Illuminate\Support\Facades\Auth;

class ClienteController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->rol->nombre === 'SUPERADMIN') {
            $clientes = Cliente::all();
        } elseif ($user->rol->nombre === 'ADMIN') {
            $clientes = Cliente::where('empresa_id', $user->empresa_id)->get();
        } else { // CLIENTE
            $clientes = Cliente::where('id', $user->cliente_id)->get();
        }

        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'documento' => 'nullable|string|max:50',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'correo' => 'nullable|email|max:255',
        ]);

        $data['empresa_id'] = $user->empresa_id ?? 1;

        Cliente::create($data);

        return redirect()->route('clientes.index')->with('success', 'Cliente creado correctamente.');
    }

    public function edit(Cliente $cliente)
    {
        $user = Auth::user();

        if ($user->rol->nombre === 'ADMIN' && $cliente->empresa_id != $user->empresa_id) {
            abort(403);
        }

        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $user = Auth::user();

        if ($user->rol->nombre === 'ADMIN' && $cliente->empresa_id != $user->empresa_id) {
            abort(403);
        }

        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'documento' => 'nullable|string|max:50',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'correo' => 'nullable|email|max:255',
            'estado' => 'required|in:ACTIVO,INACTIVO',
        ]);

        $cliente->update($data);

        return redirect()->route('clientes.index')->with('success', 'Cliente actualizado correctamente.');
    }
}
