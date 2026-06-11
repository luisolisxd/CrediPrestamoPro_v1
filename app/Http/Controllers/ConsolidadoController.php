<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use Illuminate\Support\Facades\Auth;

class ConsolidadoController extends Controller
{
    /**
     * Muestra la lista de clientes con su respectivo estado y conteo de préstamos.
     */
    public function index()
    {
        $user = Auth::user();

        // Inicializamos la consulta cargando la cantidad de préstamos que tiene cada cliente
        $query = Cliente::withCount('prestamos');

        // Filtrado por roles según el esquema del sistema
        if ($user->rol->nombre === 'ADMIN') {
            $query->where('empresa_id', $user->empresa_id);
        } elseif ($user->rol->nombre !== 'SUPERADMIN') {
            // Si es un rol de tipo CLIENTE o menor, solo se ve a sí mismo
            $query->where('id', $user->cliente_id);
        }

        $clientes = $query->orderBy('nombre', 'asc')->get();

        return view('consolidado.index', compact('clientes'));
    }

    /**
     * Muestra el detalle consolidado de un cliente específico junto a todos sus préstamos.
     */
    public function show(Cliente $cliente)
    {
        $user = Auth::user();

        // Control de seguridad por empresa para Administradores
        if ($user->rol->nombre === 'ADMIN' && $cliente->empresa_id != $user->empresa_id) {
            abort(403, 'No tienes permiso para ver este cliente.');
        }
        
        // SEGURIDAD PARA EL ROL CLIENTE: No puede ver consolidados de otros clientes ajenos
        if ($user->rol->nombre === 'CLIENTE' && $cliente->id != $user->cliente_id) {
            abort(403, 'No tienes permiso para acceder a este recurso.');
        }

        // Cargamos los préstamos del cliente usando la relación corregida hasMany
        $cliente->load('prestamos');

        return view('consolidado.show', compact('cliente'));
    }
}