<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Venta;
use App\Models\Compra;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\StorePagoRequest;
use App\Http\Requests\UpdatePagoRequest;

class PagoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pagos = Pago::with(['venta.cliente', 'compra.proveedor'])->latest()->paginate(10);
        return Inertia::render('Pago/Index', [
            'pagos' => $pagos,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
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
        Pago::create($request->validated());

        return Redirect::route('pagos.index')->with('flash', ['success' => 'Pago registrado exitosamente.']);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pago $pago)
    {
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
        $pago->update($request->validated());

         return redirect()->route('pagos.index')
                     ->with('flash', ['success' => 'Pago actualizado exitosamente.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pago $pago)
    {
        $pago->delete();

        return Redirect::route('pagos.index')->with('flash', ['success' => 'Pago eliminado correctamente.']);
    }
}
