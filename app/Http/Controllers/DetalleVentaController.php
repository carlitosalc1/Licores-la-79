<?php

namespace App\Http\Controllers;

use App\Models\DetalleVenta;
use App\Models\Venta;
use App\Models\Producto;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DetalleVentaController extends Controller
{
    /**
     * Muestra una lista paginada de detalles de venta.
     */
    public function index(Request $request)
    {
        $detallesVenta = DetalleVenta::with(['venta', 'producto'])
            ->when($request->search, function ($query, $search) {
                $query->whereHas('producto', function ($q) use ($search) {
                    $q->where('nombre', 'like', '%' . $search . '%');
                })->orWhereHas('venta', function ($q) use ($search) {
                    $q->where('id', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->paginate(10)
            ->appends($request->all());

        return Inertia::render('DetalleVenta/Index', [
            'detallesVenta' => $detallesVenta,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo detalle de venta.
     */
    public function create()
    {
        return Inertia::render('DetalleVenta/Create', [
            'ventas' => Venta::select('id')->get(),
            'productos' => Producto::select('id', 'nombre', 'precio_venta')->get(),
        ]);
    }

    /**
     * Almacena un nuevo detalle de venta en la base de datos.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'venta_id' => 'required|exists:ventas,id',
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
            'precio_unitario' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'impuesto_iva' => 'required|numeric|min:0',
        ]);

        DetalleVenta::create($validated);

        return redirect()->route('detalle_ventas.index');
    }

    /**
     * Muestra los detalles de un detalle de venta específico.
     */
    public function show(DetalleVenta $detalleVenta)
    {
        return Inertia::render('DetalleVenta/Show', [
            'detalleVenta' => $detalleVenta->load(['venta', 'producto']),
        ]);
    }

    /**
     * Muestra el formulario para editar un detalle de venta existente.
     */
    public function edit(DetalleVenta $detalleVenta)
    {
        return Inertia::render('DetalleVenta/Edit', [
            'detalleVenta' => $detalleVenta->load(['venta', 'producto']),
            'ventas' => Venta::select('id')->get(),
            'productos' => Producto::select('id', 'nombre', 'precio_venta')->get(),
        ]);
    }

    /**
     * Actualiza un detalle de venta en la base de datos.
     */
    public function update(Request $request, DetalleVenta $detalleVenta)
    {
        $validated = $request->validate([
            'venta_id' => 'required|exists:ventas,id',
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
            'precio_unitario' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'impuesto_iva' => 'required|numeric|min:0',
        ]);

        $detalleVenta->update($validated);

        return redirect()->route('detalle_ventas.index');
    }

    /**
     * Elimina un detalle de venta de la base de datos.
     */
    public function destroy(DetalleVenta $detalleVenta)
    {
        $detalleVenta->delete();

    return redirect()->route('detalle_ventas.index')->with('success', 'Venta eliminada correctamente.');
    }
}
