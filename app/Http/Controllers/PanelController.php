<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prestamo;
use App\Models\Cliente;
use Illuminate\Support\Facades\Auth;

class PanelController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Si es SUPERADMIN
        if ($user->rol->nombre === 'SUPERADMIN') {
            $totalPrestado = Prestamo::sum('capital_prestado');
            $totalPendiente = Prestamo::sum('capital_pendiente');
            $totalCobrado = Prestamo::sum('total_cobrado');
            $cantidadPrestamos = Prestamo::count();
            $prestamosRecientes = Prestamo::with('cliente')->latest()->take(5)->get();
        } 
        // 2. Si es ADMIN
        elseif ($user->rol->nombre === 'ADMIN') {
            $totalPrestado = Prestamo::where('empresa_id', $user->empresa_id)->sum('capital_prestado');
            $totalPendiente = Prestamo::where('empresa_id', $user->empresa_id)->sum('capital_pendiente');
            $totalCobrado = Prestamo::where('empresa_id', $user->empresa_id)->sum('total_cobrado');
            $cantidadPrestamos = Prestamo::where('empresa_id', $user->empresa_id)->count();
            $prestamosRecientes = Prestamo::where('empresa_id', $user->empresa_id)->with('cliente')->latest()->take(5)->get();
        } 
        // 3. Si es CLIENTE
        else { 
            // Busqueda inteligente del ID del cliente
            $clienteId = $user->id_cliente ?? $user->cliente_id;

            if (!$clienteId) {
                $clienteEncontrado = Cliente::where('email', $user->email)->first();
                $clienteId = $clienteEncontrado ? $clienteEncontrado->id : null;
            }

            // Si está bien mapeado, traemos su información financiera
            if ($clienteId) {
                $totalPrestado = Prestamo::where('cliente_id', $clienteId)->sum('capital_prestado');
                $totalPendiente = Prestamo::where('cliente_id', $clienteId)->sum('capital_pendiente');
                $totalCobrado = Prestamo::where('cliente_id', $clienteId)->sum('total_cobrado');
                $cantidadPrestamos = Prestamo::where('cliente_id', $clienteId)->count();
                $prestamosRecientes = Prestamo::where('cliente_id', $clienteId)->latest()->get();
            } else {
                // Valores en cero seguros por si el usuario es nuevo y no tiene historial asignado aún
                $totalPrestado = 0;
                $totalPendiente = 0;
                $totalCobrado = 0;
                $cantidadPrestamos = 0;
                $prestamosRecientes = collect();
            }
        }

        return view('dashboard', compact(
            'totalPrestado',
            'totalPendiente',
            'totalCobrado',
            'cantidadPrestamos',
            'prestamosRecientes'
        ));
    }
}