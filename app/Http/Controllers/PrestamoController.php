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
            $clientes = Cliente::orderBy('nombre', 'asc')->get();
        } elseif($user->rol->nombre === 'ADMIN') {
            $clientes = Cliente::where('empresa_id', $user->empresa_id)->orderBy('nombre', 'asc')->get();
        } else {
            abort(403, 'No tienes permisos para registrar préstamos.');
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

        if ($user->rol->nombre === 'SUPERADMIN') {
            $cliente = Cliente::findOrFail($data['cliente_id']);
            $data['empresa_id'] = $cliente->empresa_id;
        } else {
            $data['empresa_id'] = $user->empresa_id;
        }

        if (empty($data['empresa_id'])) {
            return back()->withInput()->withErrors(['cliente_id' => 'El cliente seleccionado debe tener una empresa asignada.']);
        }

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

        $fechaOriginal = $prestamo->fecha_inicio 
            ? Carbon::parse($prestamo->fecha_inicio) 
            : Carbon::now();

        $capital_cuota = round($capital / $cuotas, 2);
        $interes_cuota = round(($capital * ($interes/100)) / $cuotas, 2);
        $total_cuota = $capital_cuota + $interes_cuota;

        for($i=1; $i<=$cuotas; $i++){
            $fechaVencimiento = $fechaOriginal->copy()->addMonths($i);

            CuotaPrestamo::create([
                'prestamo_id' => $prestamo->id,
                'cliente_id' => $prestamo->cliente_id,
                'empresa_id' => $prestamo->empresa_id,
                'numero_cuota' => $i,
                'fecha_vencimiento' => $fechaVencimiento, 
                'capital' => $capital_cuota,
                'interes' => $interes_cuota,
                'total' => $total_cuota,
                'estado' => $i==1 ? 'PENDIENTE' : 'BLOQUEADA',
            ]);
        }
    }

    public function show($prestamoId)
    {
        $user = Auth::user();

        $prestamoCargado = Prestamo::with(['cliente', 'cuotas', 'movimientos'])->findOrFail($prestamoId);

        if ($user->rol->nombre === 'CLIENTE' && $prestamoCargado->cliente_id != $user->cliente_id) {
            abort(403, 'No tienes permiso para ver este préstamo.');
        }

        if ($user->rol->nombre === 'ADMIN' && $prestamoCargado->empresa_id != $user->empresa_id) {
            abort(403, 'No tienes permiso para ver información de otra empresa.');
        }

        $cuotas = $prestamoCargado->cuotas->sortBy('numero_cuota');
        $movimientos = $prestamoCargado->movimientos->sortBy('id');

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
            'numero_operacion' => $data['numero_operacion'] ?? 'MOV. DINAMICO',
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

        if ($cuota->estado === 'PAGADA' || $cuota->estado === 'BLOQUEADA') {
            return back()->with('error', 'Esta cuota ya está pagada o se encuentra bloqueada.');
        }

        $data = $request->validate([
            'fecha_pago' => 'required|date',
            'monto_pagado' => 'required|numeric|min:0.01',
            'numero_operacion' => 'nullable|string|max:100',
            'mora' => 'nullable|numeric|min:0',
        ]);

        $montoRecibidoEfectivo = (float) $data['monto_pagado']; 
        $moraAAsignar = (float) ($data['mora'] ?? 0);          

        $totalOriginalCuota   = (float) $cuota->total;
        $montoYaPagadoTotal = (float) ($cuota->monto_pagado ?? 0);
        
        $saldoRestanteCuota = round($totalOriginalCuota - $montoYaPagadoTotal, 2);

        if ($montoRecibidoEfectivo > ($saldoRestanteCuota + 0.05)) {
            return back()->with('error', 'El monto ingresado (S/ ' . number_format($montoRecibidoEfectivo, 2) . ') supera el saldo restante de la cuota (S/ ' . number_format($saldoRestanteCuota, 2) . ').');
        }

        $nuevoMontoAcumuladoCuota = round($montoYaPagadoTotal + $montoRecibidoEfectivo, 2);
        $esPagoTotal = (round($totalOriginalCuota - $nuevoMontoAcumuladoCuota, 2) <= 0.05);
        $nuevoEstadoCuota = $esPagoTotal ? 'PAGADA' : 'PENDIENTE';

        $interesPendiente = max(0, round($cuota->interes - ($cuota->interes_pagado ?? 0), 2));
        $capitalPendiente = max(0, round($cuota->capital - ($cuota->capital_pagado ?? 0), 2));

        $interesAsignado = 0;
        $capitalAsignado = 0;

        if ($interesPendiente > 0) {
            $interesAsignado = min($montoRecibidoEfectivo, $interesPendiente);
            $capitalAsignado = max(0, round($montoRecibidoEfectivo - $interesAsignado, 2));
        } else {
            $interesAsignado = 0;
            $capitalAsignado = min($montoRecibidoEfectivo, $capitalPendiente);
        }

        if ($moraAAsignar > 0) {
            $cuotaReferencia = CuotaPrestamo::where('prestamo_id', $prestamo->id)->where('capital', '>', 0)->first();
            $topeMoraCuota = $cuotaReferencia ? (float)$cuotaReferencia->total : $totalOriginalCuota;
            $ultimaCuotaMora = CuotaPrestamo::where('prestamo_id', $prestamo->id)->where('capital', 0)->where('interes', 0)->orderBy('numero_cuota', 'desc')->first();

            if ($ultimaCuotaMora && $ultimaCuotaMora->estado === 'PENDIENTE') {
                $espacioDisponible = max(0, $topeMoraCuota - $ultimaCuotaMora->total);
                if ($moraAAsignar <= $espacioDisponible) {
                    $ultimaCuotaMora->update(['total' => round($ultimaCuotaMora->total + $moraAAsignar, 2)]);
                    $moraAAsignar = 0;
                } else {
                    $ultimaCuotaMora->update(['total' => $topeMoraCuota]);
                    $moraAAsignar = round($moraAAsignar - $espacioDisponible, 2);
                }
            }

            while ($moraAAsignar > 0) {
                $maxCuotaNumero = CuotaPrestamo::where('prestamo_id', $prestamo->id)->max('numero_cuota') ?? 0;
                $siguienteCuotaNumero = $maxCuotaNumero + 1;
                $montoAsignarANuevaCuota = min($moraAAsignar, $topeMoraCuota);

                CuotaPrestamo::create([
                    'prestamo_id' => $prestamo->id,
                    'cliente_id' => $prestamo->cliente_id,
                    'empresa_id' => $prestamo->empresa_id,
                    'numero_cuota' => $siguienteCuotaNumero,
                    'fecha_vencimiento' => \Carbon\Carbon::parse($data['fecha_pago'])->addDays(7),
                    'capital' => 0.00,
                    'interes' => 0.00,
                    'total' => round($montoAsignarANuevaCuota, 2),
                    'estado' => 'PENDIENTE',
                    'monto_pagado' => 0.00,
                    'interes_pagado' => 0.00,
                    'capital_pagado' => 0.00
                ]);
                $moraAAsignar = round($moraAAsignar - $montoAsignarANuevaCuota, 2);
            }
        }

        $cuota->update([
            'estado' => $nuevoEstadoCuota,
            'fecha_pago' => $data['fecha_pago'],
            'monto_pagado' => $esPagoTotal ? $totalOriginalCuota : $nuevoMontoAcumuladoCuota,
            'interes_pagado' => round(($cuota->interes_pagado ?? 0) + $interesAsignado, 2),
            'capital_pagado' => round(($cuota->capital_pagado ?? 0) + $capitalAsignado, 2),
            'numero_operacion' => $data['numero_operacion'] ?? $cuota->numero_operacion,
            'usuario_pago_id' => Auth::id(),
        ]);

        $textoDetalleCuota = "CUOTA " . $cuota->numero_cuota . "::" . ($esPagoTotal ? 'COMPLETO' : 'PARCIAL');

        MovimientoPrestamo::create([
            'prestamo_id' => $prestamo->id,
            'cliente_id' => $prestamo->cliente_id,
            'empresa_id' => $prestamo->empresa_id,
            'fecha' => $data['fecha_pago'],
            'tipo' => 'PAGO', 
            'capital_antes' => $prestamo->capital_pendiente,
            'monto' => $montoRecibidoEfectivo,     
            'interes_cobrado' => $interesAsignado, 
            'mora_cobrada' => 0.00, 
            'capital_cobrado' => $capitalAsignado, 
            'capital_final' => max(0, round($prestamo->capital_pendiente - $capitalAsignado, 2)),
            'numero_operacion' => $data['numero_operacion'] ?? $textoDetalleCuota, 
            'usuario_id' => Auth::id(),
        ]);

        $nuevoCapitalPendientePrestamo = max(0, round($prestamo->capital_pendiente - $capitalAsignado, 2));

        $prestamo->update([
            'capital_pendiente' => $nuevoCapitalPendientePrestamo,
            'interes_generado' => round($prestamo->interes_generado + $interesAsignado, 2),
            'total_cobrado' => round($prestamo->total_cobrado + $montoRecibidoEfectivo, 2),
            'estado' => $nuevoCapitalPendientePrestamo <= 0 ? 'CERRADO' : 'ACTIVO',
        ]);

        if ($esPagoTotal && ($cuota->capital > 0 || $cuota->interes > 0)) {
            $siguienteCuota = CuotaPrestamo::where('prestamo_id', $prestamo->id)
                ->where('numero_cuota', $cuota->numero_cuota + 1)
                ->first();

            if ($siguienteCuota && $siguienteCuota->estado === 'BLOQUEADA') {
                $siguienteCuota->update(['estado' => 'PENDIENTE']);
            }
        }

        return redirect()->route('prestamos.show', $prestamo->id)->with('success', 'Pago procesado con total precisión.');
    }

    // =========================================================================
    // 🌟 MÉTODOS DEL SIMULADOR INTERACTIVO (AGREGADOS)
    // =========================================================================
    
    // Muestra la vista del formulario
    public function mostrarSimulador()
    {
        return view('prestamos.simulador');
    }

    // Ejecuta la proyección del cronograma de pagos sin alterar la base de datos
    public function simular(Request $request)
    {
        $request->validate([
            'monto' => 'required|numeric|min:1',
            'tasa_interes' => 'required|numeric|min:0',
            'numero_cuotas' => 'required|integer|min:1',
            'frecuencia' => 'required|in:DIARIO,SEMANAL,QUINCENAL,MENSUAL',
            'fecha_inicio' => 'required|date',
        ]);

        $monto = (float) $request->monto;
        $tasa = (float) $request->tasa_interes;
        $numCuotas = (int) $request->numero_cuotas;
        $frecuencia = $request->frecuencia;
        
        $fecha = Carbon::parse($request->fecha_inicio);

        // Sistema Lineal / Interés Simple por Cuota (Sincronizado con tu método generarCuotas)
        $capital_cuota = round($monto / $numCuotas, 2);
        $interes_cuota = round(($monto * ($tasa / 100)) / $numCuotas, 2);
        $total_cuota = $capital_cuota + $interes_cuota;

        $cuotas = [];
        $saldoCapital = $monto;

        for ($i = 1; $i <= $numCuotas; $i++) {
            // Mapeo dinámico de fechas de vencimiento basándose en la frecuencia elegida
            if ($i > 1) {
                switch ($frecuencia) {
                    case 'DIARIO': $fecha->addDay(); break;
                    case 'SEMANAL': $fecha->addWeek(); break;
                    case 'QUINCENAL': $fecha->addDays(15); break;
                    case 'MENSUAL': $fecha->addMonth(); break;
                }
            }

            $saldoCapital -= $capital_cuota;

            $cuotas[] = [
                'numero_cuota' => $i,
                'fecha_vencimiento' => $fecha->format('Y-m-d'),
                'capital' => $capital_cuota,
                'interes' => $interes_cuota,
                'total' => $total_cuota,
                'saldo_restante' => max(0, round($saldoCapital, 2))
            ];
        }

        return response()->json([
            'monto_solicitado' => $monto,
            'tasa' => $tasa,
            'frecuencia' => $frecuencia,
            'cuotas' => $cuotas
        ]);
    }
}