<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prestamo;
use App\Models\Cliente;
use App\Models\CuotaPrestamo;
use Illuminate\Support\Facades\Auth;
use App\Models\MovimientoPrestamo;
use Carbon\Carbon;

class PrestamoController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if($user->rol->nombre === 'SUPERADMIN') {
            $prestamos = Prestamo::all();
        } elseif($user->rol->nombre === 'ADMIN') {
            $prestamos = Prestamo::where('empresa_id', $user->empresa_id)->get();
        } else { // CLIENTE
            $prestamos = Prestamo::where('cliente_id', $user->cliente_id)->get();
        }

        return view('prestamos.index', compact('prestamos'));
    }

    public function create()
    {
        $user = Auth::user();

        if($user->rol->nombre === 'SUPERADMIN') {
            $clientes = Cliente::all();
        } elseif($user->rol->nombre === 'ADMIN') {
            $clientes = Cliente::where('empresa_id', $user->empresa_id)->get();
        } else {
            abort(403);
        }

        return view('prestamos.create', compact('clientes'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'tipo_prestamo' => 'required|in:DINAMICO,CUOTA_FIJA,CRONOGRAMA',
            'capital_prestado' => 'required|numeric|min:1',
            'numero_cuotas' => 'nullable|integer|min:1',
            'frecuencia_pago' => 'nullable|string',
            'tasa_interes' => 'nullable|numeric|min:0',
            'dia_pago' => 'nullable|integer|min:1|max:31',
            'cuotas_dobles_adicionales' => 'nullable|string',
            'fecha_inicio' => 'nullable|date',
        ]);

        $data['empresa_id'] = $user->empresa_id ?? null;
        if (!empty($data['cuotas_dobles_adicionales'])) {
            $data['cuotas_dobles_adicionales'] = json_encode([
                'detalle' => $data['cuotas_dobles_adicionales']
            ]);
        } else {
            $data['cuotas_dobles_adicionales'] = null;
        }

        $data['capital_pendiente'] = $data['capital_prestado'];
        $data['estado'] = 'ACTIVO';
        $data['fecha_vencimiento'] = null;
        $data['interes_generado'] = 0;
        $data['total_cobrado'] = 0;

        $prestamo = Prestamo::create($data);

        // Si es CUOTA_FIJA o CRONOGRAMA, generamos cuotas
        if(in_array($prestamo->tipo_prestamo, ['CUOTA_FIJA','CRONOGRAMA'])) {
            $this->generarCuotas($prestamo);
        }

        return redirect()->route('prestamos.index')->with('success', 'Préstamo creado correctamente.');
    }

    private function generarCuotas(Prestamo $prestamo)
    {
        $capital = $prestamo->capital_prestado;
        
        $cuotas = $prestamo->numero_cuotas ?? 1;
        $interes = $prestamo->tasa_interes ?? 0;

        // Convertimos la fecha de texto a un objeto Carbon ejecutable
        $fechaOriginal = $prestamo->fecha_inicio 
            ? Carbon::parse($prestamo->fecha_inicio) 
            : Carbon::now();

        $capital_cuota = round($capital / $cuotas, 2);
        $interes_cuota = round(($capital * ($interes/100)) / $cuotas, 2);
        $total_cuota = $capital_cuota + $interes_cuota;

        for($i=1; $i<=$cuotas; $i++){
            // Usamos ->copy() para no alterar la fecha original en cada vuelta del ciclo
            $fechaVencimiento = $fechaOriginal->copy()->addMonths($i);

            CuotaPrestamo::create([
                'prestamo_id' => $prestamo->id,
                'cliente_id' => $prestamo->cliente_id,
                'empresa_id' => $prestamo->empresa_id,
                'numero_cuota' => $i,
                'fecha_vencimiento' => $fechaVencimiento, // Asignamos la fecha calculada adecuadamente
                'capital' => $capital_cuota,
                'interes' => $interes_cuota,
                'total' => $total_cuota,
                'estado' => $i==1 ? 'PENDIENTE' : 'BLOQUEADA',
            ]);
        }
    }

    // SOLUCIÓN: Eliminamos la palabra 'Prestamo' aquí para recibir el ID puro como parámetro de ruta
    public function show($prestamoId)
    {
        $user = Auth::user();

        // Buscamos manualmente el préstamo cargando de forma explícita al cliente, sus cuotas y movimientos
        $prestamoCargado = Prestamo::with(['cliente', 'cuotas', 'movimientos'])->findOrFail($prestamoId);

        // 🔒 CANDADO DE SEGURIDAD PARA EL ROL CLIENTE
        if ($user->rol->nombre === 'CLIENTE' && $prestamoCargado->cliente_id != $user->cliente_id) {
            abort(403, 'No tienes permiso para ver este préstamo.');
        }

        // 🔒 CANDADO DE SEGURIDAD PARA EL ROL ADMIN
        if ($user->rol->nombre === 'ADMIN' && $prestamoCargado->empresa_id != $user->empresa_id) {
            abort(403, 'No tienes permiso para ver información de otra empresa.');
        }

        // Preparamos las colecciones ordenadas desde el nuevo objeto seguro
        $cuotas = $prestamoCargado->cuotas->sortBy('numero_cuota');
        $movimientos = $prestamoCargado->movimientos->sortBy('fecha');

        return view('prestamos.show', [
            'prestamo' => $prestamoCargado,
            'cuotas' => $cuotas,
            'movimientos' => $movimientos
        ]);
    }

    public function guardarMovimiento(Request $request, Prestamo $prestamo)
    {
        $data = $request->validate([
            'fecha' => 'required|date',
            'tipo' => 'required|in:DEPÓSITO,PAGO',
            'monto' => 'required|numeric|min:0.01',
            'interes_cobrado' => 'nullable|numeric|min:0',
            'capital_cobrado' => 'nullable|numeric|min:0',
            'numero_operacion' => 'nullable|string|max:100',
        ]);

        $capitalAntes = $prestamo->capital_pendiente;

        if ($data['tipo'] === 'DEPÓSITO') {
            $capitalFinal = $capitalAntes + $data['monto'];
            $interesCobrado = 0;
            $capitalCobrado = 0;
        } else {
            $interesCobrado = $data['interes_cobrado'] ?? 0;
            $capitalCobrado = $data['capital_cobrado'] ?? 0;
            $capitalFinal = max(0, $capitalAntes - $capitalCobrado);
        }

        MovimientoPrestamo::create([
            'prestamo_id' => $prestamo->id,
            'cliente_id' => $prestamo->cliente_id,
            'empresa_id' => $prestamo->empresa_id,
            'fecha' => $data['fecha'],
            'tipo' => $data['tipo'],
            'capital_antes' => $capitalAntes,
            'monto' => $data['monto'],
            'numero_operacion' => $data['numero_operacion'] ?? null,
            'interes_cobrado' => $interesCobrado,
            'capital_cobrado' => $capitalCobrado,
            'capital_final' => $capitalFinal,
            'usuario_id' => Auth::id(),
        ]);

        $prestamo->update([
            'capital_pendiente' => $capitalFinal,
            'interes_generado' => $prestamo->interes_generado + $interesCobrado,
            'total_cobrado' => $prestamo->total_cobrado + ($data['tipo'] === 'PAGO' ? $data['monto'] : 0),
            'estado' => $capitalFinal <= 0 ? 'CERRADO' : 'ACTIVO',
        ]);

        return redirect()->route('prestamos.show', $prestamo)->with('success', 'Movimiento registrado correctamente.');
    }

    public function pagarCuota(Request $request, $cuotaId)
    {
        $cuota = CuotaPrestamo::findOrFail($cuotaId);
        $prestamo = Prestamo::findOrFail($cuota->prestamo_id);

        if ($cuota->estado !== 'PENDIENTE') {
            return back()->with('error', 'Solo se puede pagar la cuota pendiente.');
        }

        $data = $request->validate([
            'fecha_pago' => 'required|date',
            'monto_pagado' => 'required|numeric|min:0.01',
            'numero_operacion' => 'nullable|string|max:100',
        ]);

        $cuota->update([
            'estado' => 'PAGADA',
            'fecha_pago' => $data['fecha_pago'],
            'monto_pagado' => $data['monto_pagado'],
            'numero_operacion' => $data['numero_operacion'] ?? null,
            'usuario_pago_id' => Auth::id(),
        ]);

        MovimientoPrestamo::create([
            'prestamo_id' => $prestamo->id,
            'cliente_id' => $prestamo->cliente_id,
            'empresa_id' => $prestamo->empresa_id,
            'fecha' => $data['fecha_pago'],
            'tipo' => 'PAGO',
            'capital_antes' => $prestamo->capital_pendiente,
            'monto' => $data['monto_pagado'],
            'interes_cobrado' => $cuota->interes,
            'capital_cobrado' => $cuota->capital,
            'capital_final' => max(0, $prestamo->capital_pendiente - $cuota->capital),
            'usuario_id' => Auth::id(),
        ]);

        $nuevoCapitalPendiente = max(0, $prestamo->capital_pendiente - $cuota->capital);

        $prestamo->update([
            'capital_pendiente' => $nuevoCapitalPendiente,
            'interes_generado' => $prestamo->interes_generado + $cuota->interes,
            'total_cobrado' => $prestamo->total_cobrado + $data['monto_pagado'],
            'estado' => $nuevoCapitalPendiente <= 0 ? 'CERRADO' : 'ACTIVO',
        ]);

        $siguienteCuota = CuotaPrestamo::where('prestamo_id', $prestamo->id)
            ->where('numero_cuota', $cuota->numero_cuota + 1)
            ->first();

        if ($siguienteCuota && $siguienteCuota->estado === 'BLOQUEADA') {
            $siguienteCuota->update([
                'estado' => 'PENDIENTE',
            ]);
        }

        return redirect()->route('prestamos.show', $prestamo)->with('success', 'Cuota pagada correctamente.');
    }
}