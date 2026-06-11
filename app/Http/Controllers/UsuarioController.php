<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Rol;
use App\Models\Empresa;
use App\Models\Cliente;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->rol->nombre === 'SUPERADMIN') {
            $usuarios = User::with(['rol', 'empresa', 'cliente'])->get();
        } elseif ($user->rol->nombre === 'ADMIN') {
            $usuarios = User::with(['rol', 'empresa', 'cliente'])->where('empresa_id', $user->empresa_id)->get();
        } else {
            abort(403, 'No tienes acceso a este módulo.');
        }

        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        $user = Auth::user();
        
        if ($user->rol->nombre === 'SUPERADMIN') {
            $roles = Rol::all();
            $empresas = Empresa::all();
            // Buscamos solo clientes que tengan al menos un préstamo amarrado
            $clientes = Cliente::whereHas('prestamos')->get();
        } else {
            $roles = Rol::where('nombre', '!=', 'SUPERADMIN')->get();
            $empresas = Empresa::where('id', $user->empresa_id)->get();
            // Buscamos clientes de su empresa que tengan al menos un préstamo amarrado
            $clientes = Cliente::where('empresa_id', $user->empresa_id)->whereHas('prestamos')->get();
        }

        return view('usuarios.create', compact('roles', 'empresas', 'clientes'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // Buscamos el ID del rol "CLIENTE" dinámicamente para la validación
        $rolCliente = Rol::where('nombre', 'CLIENTE')->first();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'rol_id' => 'required|exists:roles,id',
            'empresa_id' => 'nullable|exists:empresas,id',
            // El cliente_id es OBLIGATORIO solo si seleccionaste el Rol de CLIENTE
            'cliente_id' => [
                Rule::requiredIf($request->rol_id == ($rolCliente->id ?? 0)),
                'nullable',
                'exists:clientes,id'
            ],
        ]);

        if ($user->rol->nombre !== 'SUPERADMIN') {
            $data['empresa_id'] = $user->empresa_id;
        }

        // Si es un cliente, heredamos automáticamente la empresa a la que pertenece ese cliente
        if (!empty($data['cliente_id'])) {
            $clienteSeleccionado = Cliente::find($data['cliente_id']);
            $data['empresa_id'] = $clienteSeleccionado->empresa_id;
        }

        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario)
    {
        $user = Auth::user();

        if ($user->rol->nombre === 'ADMIN' && $usuario->empresa_id != $user->empresa_id) {
            abort(403);
        }

        if ($user->rol->nombre === 'SUPERADMIN') {
            $roles = Rol::all();
            $empresas = Empresa::all();
            $clientes = Cliente::whereHas('prestamos')->get();
        } else {
            $roles = Rol::where('nombre', '!=', 'SUPERADMIN')->get();
            $empresas = Empresa::where('id', $user->empresa_id)->get();
            $clientes = Cliente::where('empresa_id', $user->empresa_id)->whereHas('prestamos')->get();
        }

        return view('usuarios.edit', compact('usuario', 'roles', 'empresas', 'clientes'));
    }

    public function update(Request $request, User $usuario)
    {
        $user = Auth::user();
        $rolCliente = Rol::where('nombre', 'CLIENTE')->first();

        if ($user->rol->nombre === 'ADMIN' && $usuario->empresa_id != $user->empresa_id) {
            abort(403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $usuario->id,
            'password' => 'nullable|string|min:8',
            'rol_id' => 'required|exists:roles,id',
            'empresa_id' => 'nullable|exists:empresas,id',
            'cliente_id' => [
                Rule::requiredIf($request->rol_id == ($rolCliente->id ?? 0)),
                'nullable',
                'exists:clientes,id'
            ],
        ]);

        if ($user->rol->nombre !== 'SUPERADMIN') {
            $data['empresa_id'] = $user->empresa_id;
        }

        if (!empty($data['cliente_id'])) {
            $clienteSeleccionado = Cliente::find($data['cliente_id']);
            $data['empresa_id'] = $clienteSeleccionado->empresa_id;
        } else {
            // Si deja de ser cliente, limpiamos el campo
            $data['cliente_id'] = null;
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $usuario->update($data);

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }
}