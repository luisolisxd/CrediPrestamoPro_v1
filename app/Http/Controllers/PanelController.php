<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prestamo;
use App\Models\Cliente;

class PanelController extends Controller
{
    public function index()
    {
        $prestamos_activos = Prestamo::where('estado','ACTIVO')->count();
        $prestamos_cerrados = Prestamo::where('estado','CERRADO')->count();
        $clientes = Cliente::count();
        $capital_prestado = Prestamo::sum('capital_prestado');
        $capital_pendiente = Prestamo::sum('capital_pendiente');
        $intereses_cobrados = Prestamo::sum('interes_generado');

        return view('panel.index', compact(
            'prestamos_activos',
            'prestamos_cerrados',
            'clientes',
            'capital_prestado',
            'capital_pendiente',
            'intereses_cobrados'
        ));
    }
}
