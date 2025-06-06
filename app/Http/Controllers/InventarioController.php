<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\Producto;
use App\Http\Requests\InventarioRequest;
use Inertia\Inertia;

class InventarioController extends Controller
{
    public function index()
    {
        $inventarios = Inventario::with(['producto', 'compra', 'venta'])->get();
        $productos = Producto::all()->map(function ($producto) {
            $producto->stock_actual = $producto->stock_actual; // Calcula el stock dinámicamente
            return $producto;
        });

        return Inertia::render('Inventario/Index', [
            'inventarios' => $inventarios,
            'productos' => $productos,
        ]);
    }

    public function create()
    {
        $productos = Producto::all();
        return Inertia::render('Inventario/Create', [
            'productos' => $productos,
            'tipo_movimientos' => ['entrada', 'salida', 'ajuste'],
        ]);
    }

    public function store(InventarioRequest $request)
    {
        // Validar stock para salidas
        if ($request->tipo_movimiento === 'salida') {
            $stock_actual = Inventario::getStockActual($request->producto_id);
            if ($stock_actual < $request->cantidad) {
                return back()->withErrors(['cantidad' => 'No hay suficiente stock disponible.']);
            }
        }

        // Crear el movimiento de inventario
        Inventario::create([
            'producto_id' => $request->producto_id,
            'compra_id' => $request->compra_id,
            'venta_id' => $request->venta_id,
            'cantidad_entrada' => $request->tipo_movimiento === 'entrada' || $request->tipo_movimiento === 'ajuste' ? $request->cantidad : 0,
            'cantidad_salida' => $request->tipo_movimiento === 'salida' ? $request->cantidad : 0,
            'tipo_movimiento' => $request->tipo_movimiento,
            'fecha_actualizacion' => now(),
        ]);

        return redirect()->route('inventario.index')->with('message', 'Movimiento de inventario registrado exitosamente.');
    }

    public function edit(Inventario $inventario)
    {
        $productos = Producto::all();
        return Inertia::render('Inventario/Edit', [
            'inventario' => $inventario->load(['producto', 'compra', 'venta']),
            'productos' => $productos,
            'tipo_movimientos' => ['entrada', 'salida', 'ajuste'],
        ]);
    }

    public function update(InventarioRequest $request, Inventario $inventario)
    {
        // Validar stock para salidas
        if ($request->tipo_movimiento === 'salida') {
            $stock_actual = Inventario::getStockActual($inventario->producto_id) + $inventario->cantidad_entrada - $inventario->cantidad_salida;
            if ($stock_actual < $request->cantidad) {
                return back()->withErrors(['cantidad' => 'No hay suficiente stock disponible.']);
            }
        }

        // Actualizar el movimiento
        $inventario->update([
            'producto_id' => $request->producto_id,
            'compra_id' => $request->compra_id,
            'venta_id' => $request->venta_id,
            'cantidad_entrada' => $request->tipo_movimiento === 'entrada' || $request->tipo_movimiento === 'ajuste' ? $request->cantidad : 0,
            'cantidad_salida' => $request->tipo_movimiento === 'salida' ? $request->cantidad : 0,
            'tipo_movimiento' => $request->tipo_movimiento,
            'fecha_actualizacion' => now(),
        ]);

        return redirect()->route('inventario.index')->with('message', 'Movimiento de inventario actualizado exitosamente.');
    }

    public function destroy(Inventario $inventario)
    {
        // Validar antes de eliminar
        $stock_actual = Inventario::getStockActual($inventario->producto_id) + $inventario->cantidad_entrada - $inventario->cantidad_salida;
        if ($stock_actual < 0) {
            return back()->withErrors(['error' => 'No se puede eliminar el movimiento, resultaría en stock negativo.']);
        }

        $inventario->delete();
        return redirect()->route('inventario.index')->with('message', 'Movimiento de inventario eliminado exitosamente.');
    }
}
