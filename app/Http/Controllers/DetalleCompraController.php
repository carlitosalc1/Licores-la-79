<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DetalleCompra;
use App\Models\Producto;
use App\Models\Compra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class DetalleCompraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $detalleCompras = DetalleCompra::with(['producto', 'compra'])
        ->orderBy('id', 'desc')
        ->paginate(10)
        ->withQueryString();

         return Inertia::render('DetalleCompra/Index', [
        'detalles' => $detalleCompras,
    ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('DetalleCompra/Create', [
            'compras' => Compra::select('id')->get(),
            'productos' => Producto::select('id', 'nombre', 'precio_compra')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
     $validated = $request->validate([
        'compra_id'   => 'required|exists:compras,id',
        'producto_id' => 'required|exists:productos,id',
        'cantidad'    => 'required|integer|min:1',
        'precio_unitario' => 'required|numeric',
        'subtotal'    => 'required|numeric',
    ]);

         DetalleCompra::create($validated);
         return redirect()->route('detalle_compras.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DetalleCompra $detalleCompra)
    {
        $productos = Producto::all();
        $compras = Compra::all();

        return Inertia::render('DetalleCompra/Edit', [
        'detalle' => $detalleCompra,
        'productos' => Producto::select('id', 'nombre')->get(),
        'compras' => Compra::select('id')->get(),
    ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DetalleCompra $detalleCompra)
    {
        $validated = $request->validate([
        'producto_id' => 'required|exists:productos,id',
        'compra_id' => 'required|exists:compras,id',
        'cantidad' => 'required|integer|min:1',
        'precio_unitario' => 'required|numeric|min:0',
    ]);

        $validated['subtotal'] = $validated['cantidad'] * $validated['precio_unitario'];
        $detalleCompra->update($validated);
        return redirect()->route('detalle_compras.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DetalleCompra $detalleCompra)
    {
        $detalleCompra->delete();
        return redirect()->route('detalle_compras.index');
    }
}
