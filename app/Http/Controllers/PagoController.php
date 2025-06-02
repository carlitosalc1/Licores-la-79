<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Venta; // Asegúrate de importar el modelo Venta
use App\Models\Compra; // Asegúrate de importar el modelo Compra
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\StorePagoRequest; // Necesitas crear este Request
use App\Http\Requests\UpdatePagoRequest; // Necesitas crear este Request

class PagoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener todos los pagos con sus relaciones de venta (con cliente) y compra (con proveedor)
        $pagos = Pago::with(['venta.cliente', 'compra.proveedor'])->get();

        return Inertia::render('Pago/Index', [
            'pagos' => $pagos,
            // También pasamos las ventas y compras para el formulario de creación/edición en la misma página
            'ventas' => Venta::with('cliente')->get(['id', 'total', 'cliente_id']),
            'compras' => Compra::with('proveedor')->get(['id', 'total', 'proveedor_id']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Obtener solo los IDs y los totales (y nombres de cliente/proveedor si existen)
        // para las listas desplegables en el formulario.
        $ventas = Venta::with('cliente')->get(['id', 'total', 'cliente_id']);
        $compras = Compra::with('proveedor')->get(['id', 'total', 'proveedor_id']);

        return Inertia::render('Pago/Create', [
            'ventas' => $ventas,
            'compras' => $compras,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePagoRequest $request)
    {
        // La validación se maneja automáticamente por StorePagoRequest
        Pago::create($request->validated());

        return redirect()->route('pagos.index')
                         ->with('success', 'Pago registrado exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pago $pago)
    {
        // Obtener solo los IDs y los totales (y nombres de cliente/proveedor si existen)
        // para las listas desplegables en el formulario de edición.
        $ventas = Venta::with('cliente')->get(['id', 'total', 'cliente_id']);
        $compras = Compra::with('proveedor')->get(['id', 'total', 'proveedor_id']);

        return Inertia::render('Pago/Edit', [
            'pago' => $pago,
            'ventas' => $ventas,
            'compras' => $compras,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePagoRequest $request, Pago $pago)
    {
        // La validación se maneja automáticamente por UpdatePagoRequest
        $pago->update($request->validated());

        return redirect()->route('pagos.index')
                         ->with('success', 'Pago actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pago $pago)
    {
        $pago->delete();

        return redirect()->route('pagos.index')
                         ->with('success', 'Pago eliminado exitosamente.');
    }
}
