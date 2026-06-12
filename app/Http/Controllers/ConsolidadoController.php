<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Prestamo;
use Illuminate\Support\Facades\Auth;

class ConsolidadoController extends Controller
{
    // Listado general de consolidados
    public function index()
    {
        $user = Auth::user();

        // Si es un CLIENTE, calculamos su ID de forma ultra segura
        if ($user->rol->nombre === 'CLIENTE') {
            // Alternativa 1: Intentar usar id_cliente o cliente_id de la sesión
            $clienteId = $user->id_cliente ?? $user->cliente_id;

            // Alternativa 2: Si no lo encuentra, buscamos en la tabla clientes usando su email
            if (!$clienteId) {
                $clienteEncontrado = Cliente::where('email', $user->email)->first();
                $clienteId = $clienteEncontrado ? $clienteEncontrado->id : null;
            }

            // Si definitivamente el usuario no está amarrado a ningún cliente en el sistema
            if (!$clienteId) {
                abort(403, 'Tu usuario no tiene una ficha de cliente vinculada en la base de datos.');
            }

            return redirect()->route('consolidado.show', $clienteId);
        }

        if ($user->rol->nombre === 'SUPERADMIN') {
            $clientes = Cliente::with('prestamos')->get();
        } else {
            $clientes = Cliente::where('empresa_id', $user->empresa_id)->with('prestamos')->get();
        }

        return view('consolidado.index', compact('clientes'));
    }

    // Ficha detallada de un cliente
    public function show($id)
    {
        $user = Auth::user();

        // Buscamos el cliente solicitado junto con sus préstamos
        $cliente = Cliente::with(['prestamos'])->findOrFail($id);

        // 🔒 CANDADO 1: Si es un CLIENTE, solo puede ver su propia información
        if ($user->rol->nombre === 'CLIENTE') {
            $clienteIdPropio = $user->id_cliente ?? $user->cliente_id;
            
            if (!$clienteIdPropio) {
                $clienteEncontrado = Cliente::where('email', $user->email)->first();
                $clienteIdPropio = $clienteEncontrado ? $clienteEncontrado->id : null;
            }

            if ($cliente->id != $clienteIdPropio) {
                abort(403, 'No tienes permiso para acceder a la información de este cliente.');
            }
        }

        // 🔒 CANDADO 2: Si es un ADMIN de empresa, no ve franquicias ajenas
        if ($user->rol->nombre === 'ADMIN' && $cliente->empresa_id != $user->empresa_id) {
            abort(403, 'No tienes permiso para ver clientes ajenos a tu empresa.');
        }

        return view('consolidado.show', compact('cliente'));
    }
}