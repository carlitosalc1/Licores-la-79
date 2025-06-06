<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DetalleFactura;
use App\Models\Factura;
use App\Models\Producto;
use Inertia\Inertia;

class DetalleFacturaController extends Controller
{
    /**
     * Muestra una lista paginada de detalles de factura.
     */
    public function index(Request $request)
    {
        $detallesFactura = DetalleFactura::with(['factura', 'producto'])
            ->when($request->search, function ($query, $search) {
                $query->whereHas('producto', function ($q) use ($search) {
                    $q->where('nombre', 'like', '%' . $search . '%');
                })->orWhereHas('factura', function ($q) use ($search) {
                    $q->where('id', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->paginate(10)
            ->appends($request->all());

        return Inertia::render('DetalleFactura/Index', [
            'detallesFactura' => $detallesFactura,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo detalle de factura.
     */
    public function create()
    {
        return Inertia::render('DetalleFactura/Create', [
            'facturas' => Factura::select('id')->get(),
            'productos' => Producto::select('id', 'nombre', 'precio_venta')->get(),
        ]);
    }

    /**
     * Almacena un nuevo detalle de factura en la base de datos.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'factura_id' => 'required|exists:facturas,id',
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
            'precio_unitario' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'impuesto_iva' => 'required|numeric|min:0',
        ]);

        DetalleFactura::create($validated);

        return redirect()->route('detalle_facturas.index');
    }

    /**
     * Muestra los detalles de un detalle de factura específico.
     */
    public function show(DetalleFactura $detalleFactura)
    {
        return Inertia::render('DetalleFactura/Show', [
            'detalleFactura' => $detalleFactura->load(['factura', 'producto']),
        ]);
    }

    /**
     * Muestra el formulario para editar un detalle de factura existente.
     */
    public function edit(DetalleFactura $detalleFactura)
    {
        return Inertia::render('DetalleFactura/Edit', [
            'detalleFactura' => $detalleFactura->load(['factura', 'producto']),
            'facturas' => Factura::select('id')->get(),
            'productos' => Producto::select('id', 'nombre', 'precio_venta')->get(),
        ]);
    }

    /**
     * Actualiza un detalle de factura en la base de datos.
     */
    public function update(Request $request, DetalleFactura $detalleFactura)
    {
        $validated = $request->validate([
            'factura_id' => 'required|exists:facturas,id',
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
            'precio_unitario' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'impuesto_iva' => 'required|numeric|min:0',
        ]);

        $detalleFacturas->update($validated);

        return redirect()->route('detalle_facturas.index');
    }

    /**
     * Elimina un detalle de factura de la base de datos.
     */
    public function destroy(DetalleFactura $detalleFactura)
    {
        $detalleFactura->delete();

    return redirect()->route('detalle_facturas.index')->with('success', 'factura eliminada correctamente.');
    }
}

