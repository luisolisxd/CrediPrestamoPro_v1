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
            // El Súper Administrador puede ver y asignar el préstamo a cualquier cliente del sistema
            $clientes = Cliente::orderBy('nombre', 'asc')->get();
        } elseif($user->rol->nombre === 'ADMIN') {
            // El Administrador de empresa solo puede ver los clientes que pertenecen a su sucursal
            $clientes = Cliente::where('empresa_id', $user->empresa_id)->orderBy('nombre', 'asc')->get();
        } else {
            // Si por algún motivo un rol CLIENTE intenta entrar aquí, lo expulsamos
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
            // Si es SUPERADMIN, el préstamo adopta automáticamente la empresa del cliente seleccionado
            $cliente = Cliente::findOrFail($data['cliente_id']);
            $data['empresa_id'] = $cliente->empresa_id;
        } else {
            // Si es un ADMIN, se usa la empresa a la que pertenece el administrador
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

        // 1. Validamos estrictamente el estado de la cuota que se intenta pagar
        if (!in_array($cuota->estado, ['PENDIENTE', 'PARCIAL'])) {
            return back()->with('error', 'Solo se pueden procesar pagos en cuotas pendientes o parciales.');
        }

        $data = $request->validate([
            'fecha_pago' => 'required|date',
            'monto_pagado' => 'required|numeric|min:0.01',
            'numero_operacion' => 'nullable|string|max:100',
            'mora' => 'nullable|numeric|min:0',
        ]);

        $montoRecibido = $data['monto_pagado'];
        $moraInput = $data['mora'] ?? 0;

        $totalOriginalCuota = $cuota->total;
        $yaPagadoEnEstaCuota = $cuota->monto_pagado ?? 0;
        $saldoRestanteCuota = max(0, $totalOriginalCuota - $yaPagadoEnEstaCuota);

        if ($montoRecibido > $saldoRestanteCuota) {
            return back()->with('error', 'El monto ingresado (S/ ' . number_format($montoRecibido, 2) . ') supera el saldo restante de la cuota (S/ ' . number_format($saldoRestanteCuota, 2) . ').');
        }

        $nuevoMontoAcumuladoCuota = $yaPagadoEnEstaCuota + $montoRecibido;
        $esPagoTotal = (round($nuevoMontoAcumuladoCuota, 2) >= round($totalOriginalCuota, 2));
        $nuevoEstadoCuota = $esPagoTotal ? 'PAGADA' : 'PARCIAL';

        $interesAsignado = 0;
        $capitalAsignado = 0;

        if ($esPagoTotal) {
            $interesAsignado = max(0, $cuota->interes - ($cuota->interes_pagado ?? 0));
            $capitalAsignado = max(0, $cuota->capital - ($cuota->capital_pagado ?? 0));
        } else {
            $porcentajeInteres = $cuota->total > 0 ? ($cuota->interes / $cuota->total) : 0;
            $interesAsignado = round($montoRecibido * $porcentajeInteres, 2);
            $capitalAsignado = round($montoRecibido - $interesAsignado, 2);

            $interesYaPagado = $cuota->interes_pagado ?? 0;
            if (($interesYaPagado + $interesAsignado) > $cuota->interes) {
                $interesAsignado = max(0, $cuota->interes - $interesYaPagado);
                $capitalAsignado = round($montoRecibido - $interesAsignado, 2);
            }
        }

        // 🚀 GESTIÓN DE CUOTA ADICIONAL POR MORA FIX: Buscamos el MAX real de la tabla
        if ($moraInput > 0) {
            // Buscamos el número de cuota más alto registrado actualmente para este préstamo
            $maxCuotaNumero = CuotaPrestamo::where('prestamo_id', $prestamo->id)->max('numero_cuota') ?? 0;
            $siguienteCuotaMora = $maxCuotaNumero + 1;

            CuotaPrestamo::create([
                'prestamo_id' => $prestamo->id,
                'cliente_id' => $prestamo->cliente_id,
                'empresa_id' => $prestamo->empresa_id,
                'numero_cuota' => $siguienteCuotaMora, // Ahora será único (ej. Cuota 2, 3, etc.)
                'fecha_vencimiento' => Carbon::parse($data['fecha_pago'])->addMonth(),
                'capital' => 0.00,
                'interes' => 0.00,
                'total' => $moraInput,
                'estado' => 'PENDIENTE',
                'monto_pagado' => 0.00,
                'interes_pagado' => 0.00,
                'capital_pagado' => 0.00
            ]);
        }

        // Actualizamos la cuota evaluada
        $cuota->update([
            'estado' => $nuevoEstadoCuota,
            'fecha_pago' => $data['fecha_pago'],
            'monto_pagado' => $nuevoMontoAcumuladoCuota,
            'interes_pagado' => ($cuota->interes_pagado ?? 0) + $interesAsignado,
            'capital_pagado' => ($cuota->capital_pagado ?? 0) + $capitalAsignado,
            'numero_operacion' => $data['numero_operacion'] ?? $cuota->numero_operacion,
            'usuario_pago_id' => Auth::id(),
        ]);

        // Registramos movimiento en caja
        MovimientoPrestamo::create([
            'prestamo_id' => $prestamo->id,
            'cliente_id' => $prestamo->cliente_id,
            'empresa_id' => $prestamo->empresa_id,
            'fecha' => $data['fecha_pago'],
            'tipo' => 'PAGO',
            'capital_antes' => $prestamo->capital_pendiente,
            'monto' => $montoRecibido + $moraInput, 
            'interes_cobrado' => $interesAsignado, 
            'mora_cobrada' => $moraInput, 
            'capital_cobrado' => $capitalAsignado,
            'capital_final' => max(0, $prestamo->capital_pendiente - $capitalAsignado),
            'numero_operacion' => $data['numero_operacion'] ?? null,
            'usuario_id' => Auth::id(),
        ]);

        $nuevoCapitalPendientePrestamo = max(0, $prestamo->capital_pendiente - $capitalAsignado);

        $prestamo->update([
            'capital_pendiente' => $nuevoCapitalPendientePrestamo,
            'interes_generado' => $prestamo->interes_generado + $interesAsignado,
            'mora_pagada' => ($prestamo->mora_pagada ?? 0) + $moraInput,
            'total_cobrado' => $prestamo->total_cobrado + $montoRecibido + $moraInput,
            'estado' => $nuevoCapitalPendientePrestamo <= 0 ? 'CERRADO' : 'ACTIVO',
        ]);

        // 🔒 FIX: Desbloqueamos la siguiente cuota correlativa SÓLO si la cuota pagada tiene capital/interés real (no es una cuota de mora)
        if ($esPagoTotal && $cuota->total > 0 && ($cuota->capital > 0 || $cuota->interes > 0)) {
            $siguienteCuota = CuotaPrestamo::where('prestamo_id', $prestamo->id)
                ->where('numero_cuota', $cuota->numero_cuota + 1)
                ->first();

            if ($siguienteCuota && $siguienteCuota->estado === 'BLOQUEADA') {
                $siguienteCuota->update(['estado' => 'PENDIENTE']);
            }
        }

        return redirect()->route('prestamos.show', $prestamo->id)->with('success', 'Pago procesado correctamente.');
    }
}