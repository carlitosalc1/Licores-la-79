<?php

namespace App\Http\Controllers;

use App\Models\DetalleCompra;
use App\Models\Compra;
use App\Models\Producto;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DetalleCompraController extends Controller
{
    /**
     * Muestra una lista paginada de detalles de compra.
     */
    public function index(Request $request)
    {
        $detallesCompra = DetalleCompra::with(['compra', 'producto'])
            ->when($request->search, function ($query, $search) {
                $query->whereHas('producto', function ($q) use ($search) {
                    $q->where('nombre', 'like', '%' . $search . '%');
                })->orWhereHas('compra', function ($q) use ($search) {
                    $q->where('id', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->paginate(10)
            ->appends($request->all());

        return Inertia::render('DetalleCompra/Index', [
            'detallesCompra' => $detallesCompra,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo detalle de compra.
     */
    public function create()
    {
        return Inertia::render('DetalleCompra/Create', [
            'compras' => Compra::select('id')->get(),
            'productos' => Producto::select('id', 'nombre', 'precio_compra')->get(),
        ]);
    }

    /**
     * Almacena un nuevo detalle de compra en la base de datos.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'compra_id' => 'required|exists:compras,id',
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
            'precio_unitario' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'impuesto_iva' => 'required|numeric|min:0',
        ]);

        DetalleCompra::create($validated);

        return redirect()->route('detalle_compras.index');
    }

    /**
     * Muestra los detalles de un detalle de compra específico.
     */
    public function show(DetalleCompra $detalleCompra)
    {
        return Inertia::render('DetalleCompra/Show', [
            'detalleCompra' => $detalleCompra->load(['compra', 'producto']),
        ]);
    }

    /**
     * Muestra el formulario para editar un detalle de compra existente.
     */
    public function edit(DetalleCompra $detalleCompra)
    {
        return Inertia::render('DetalleCompra/Edit', [
            'detalleCompra' => $detalleCompra->load(['compra', 'producto']),
            'compras' => Compra::select('id')->get(),
            'productos' => Producto::select('id', 'nombre', 'precio_compra')->get(),
        ]);
    }

    /**
     * Actualiza un detalle de compra en la base de datos.
     */
    public function update(Request $request, DetalleCompra $detalleCompra)
    {
        $validated = $request->validate([
            'compra_id' => 'required|exists:compras,id',
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
            'precio_unitario' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'impuesto_iva' => 'required|numeric|min:0',
        ]);

        $detalleCompra->update($validated);

        return redirect()->route('detalle_compras.index');
    }

    /**
     * Elimina un detalle de compra de la base de datos.
     */
    public function destroy(DetalleCompra $detalleCompra)
    {
        $detalleCompra->delete();

        return redirect()->route('detalle_compras.index')->with('success', 'Detalle de compra eliminado correctamente.');
    }
}
