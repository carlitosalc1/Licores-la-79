<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controllers;
use App\Models\Compra;
use App\Models\Proveedor;
use App\Models\Producto;
use App\Models\DetalleCompra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;

class CompraController extends Controller
{
    public function index()
    {
        $compras = Compra::with(['proveedor','user','detalleCompras.producto.categoriaProducto'])->latest()->paginate(10);
        return Inertia::render('Compra/Index', [
            'compras' => $compras
        ]);
    }

    public function create()
    {
        return Inertia::render('Compra/Create', [
            'proveedores' => Proveedor::select('id', 'razon_social')->get(),
            'productos' => Producto::select('id', 'nombre', 'precio_compra')->get(),
            'usuario' => Auth::user()->only('id', 'name'),
        ]);
    }

    public function store(Request $request)
{
    // Validaciones
    $request->validate([
        'fecha_compra' => 'required|date',
        'proveedor_id' => 'required|exists:proveedors,id',
        'estado' => 'required|in:pagado,cancelada',
        'detalles' => 'required|array|min:1',
        'detalles.*.producto_id' => 'required|exists:productos,id',
        'detalles.*.cantidad' => 'required|integer|min:1',
        'total_iva' => 'required|numeric|min:0',
        'total_compra' => 'required|numeric|min:0',

    ]);

    DB::beginTransaction();

    try {
        $detallesRequest = $request->input('detalles');
        $totalCompraCalculado = 0;
        $totalIvaCalculado = 0;
        $iva_porcentaje = 0.19;

        foreach ($detallesRequest as $detalle) {
                $producto = Producto::findOrFail($detalle['producto_id']);
                $cantidad = $detalle['cantidad'];
                $precio = (float) $producto->precio_compra;
                $subtotal = $cantidad * $precio;
                $impuesto_iva = $subtotal * $iva_porcentaje;
                $totalCompraCalculado += $subtotal + $impuesto_iva;
                $totalIvaCalculado += $impuesto_iva;
            }

        $compra = Compra::create([
            'proveedor_id' => $request->proveedor_id,
            'user_id' => auth()->id(),
            'fecha_compra' => $request->fecha_compra,
            'total' => $totalCompraCalculado,
            'estado' => $request->estado,
            'total_iva' => $totalIvaCalculado,
        ]);

        foreach ($detallesRequest as $detalle) {
            $producto = Producto::findOrFail($detalle['producto_id']);
            $cantidad = $detalle['cantidad'];
            $precio = (float) $producto->precio_compra;

            $subtotal = $cantidad * $precio;
            $impuesto_iva = $subtotal * $iva_porcentaje;

            DetalleCompra::create([
                'compra_id' => $compra->id,
                'producto_id' => $producto->id,
                'cantidad' => $cantidad,
                'precio_unitario' => $precio,
                'subtotal' => $subtotal,
                'impuesto_iva' => $impuesto_iva,
                'total' => $subtotal + $impuesto_iva,
            ]);
        }

        DB::commit();

        return redirect()->route('compras.index')->with('success', 'Compra registrada correctamente.');
    } catch (ValidationException $e) {
        DB::rollBack();
        return back()->withErrors($e->errors())->withInput();
    } catch (\Throwable $e) {
        DB::rollBack();
        return back()->withErrors(['error' => 'Error al registrar la compra: ' . $e->getMessage()])->withInput();
    }
}

    public function edit(Compra $compra)
{
    $compra->load([
        'proveedor',
        'user',
        'detalleCompras.producto'
    ]);

    return Inertia::render('Compra/Edit', [
        'compra' => [
        'id' => $compra->id,
        'proveedores' => $compra->proveedor_id,
        'fecha_compra' => $compra->fecha_compra,
        'user_id' => $compra->user_id,
        'estado' => $compra->estado,
        'total_iva' => $compra->total_iva,
        'total' => $compra->total,
        'detalle_compras' => $compra->detalleCompras->map(function ($detalle) {
                return [
                    'id' => $detalle->id,
                    'producto_id' => $detalle->producto_id,
                    'producto' => [
                        'nombre' => $detalle->producto->nombre,
                        'precio_compra' => $detalle->producto->precio_compra
                    ],
                    'cantidad' => $detalle->cantidad,
                    'precio_unitario' => $detalle->precio_unitario,
                    'subtotal' => $detalle->subtotal,
                    'impuesto_iva' => $detalle->impuesto_iva,
                    'total' => $detalle->total,
                ];
            }),
            'total_iva' => $compra->total_iva,
            'total_compra' => $compra->total
        ],
        'proveedores' => Proveedor::all(),
        'productos' => Producto::all(),
        'usuario' => Auth::user()->only('id', 'name'),
    ]);
}

    public function update(Request $request, Compra $compra)
{
    try {
        $request->validate([
            'proveedor_id' => ['required', 'exists:proveedors,id'],
            'fecha_compra' => ['required', 'date'],
            'user_id' => ['required', 'exists:users,id'],
            'estado' => ['required', 'in:pagado,cancelada'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.producto_id' => ['required', 'exists:productos,id'],
            'detalles.*.cantidad' => ['required', 'integer', 'min:1'],
            'detalles.*.precio' => ['required', 'numeric', 'min:0'],
            'detalles.*.subtotal' => ['required', 'numeric', 'min:0'],
            'detalles.*.impuesto_iva' => ['required', 'numeric', 'min:0'],
            'detalles.*.total' => ['required', 'numeric', 'min:0'],
            'total_iva' => ['required', 'numeric', 'min:0'],
            'total_compra' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($request, $compra) {
            $totalCompraCalculado = 0;
            $totalIvaCalculado = 0;
            $iva_porcentaje = 0.19;

            foreach ($request->detalles as $detalleData) {
                $producto = Producto::findOrFail($detalleData['producto_id']);
                $cantidad = $detalleData['cantidad'];
                $precio = (float) $producto->precio_compra;

                $subtotal = $cantidad * $precio;
                $impuesto_iva = $subtotal * $iva_porcentaje;

                $totalCompraCalculado += $subtotal + $impuesto_iva;
                $totalIvaCalculado += $impuesto_iva;
            }

            $compra->update([
                'proveedor_id' => $request->proveedor_id,
                'fecha_compra' => $request->fecha_compra,
                'user_id' => $request->user_id,
                'estado' => $request->estado,
                'total' => $totalCompraCalculado,
                'total_iva' => $totalIvaCalculado,
            ]);

            $compra->detalleCompras()->delete();

            foreach ($request->detalles as $detalleData) {
                $producto = Producto::findOrFail($detalleData['producto_id']);
                $cantidad = $detalleData['cantidad'];
                $precio = (float) $producto->precio_compra;

                $subtotal = $cantidad * $precio;
                $impuesto_iva = $subtotal * $iva_porcentaje;

                $compra->detalleCompras()->create([
                    'compra_id' => $compra->id,
                    'producto_id' => $detalleData['producto_id'],
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio,
                    'subtotal' => $subtotal,
                    'impuesto_iva' => $impuesto_iva,
                    'total' => $subtotal + $impuesto_iva,
                ]);
            }
        });

        return redirect()->route('compras.index')->with('success', 'Compra actualizada exitosamente.');

    } catch (ValidationException $e) {
        return redirect()->back()
            ->withErrors($e->errors())
            ->withInput();
    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', 'Error al actualizar la compra: ' . $e->getMessage())
            ->withInput();
    }
}

    public function destroy(Compra $compra)
    {
        $compra->delete();

        return redirect()->route('compras.index')->with('success', 'Compra eliminada correctamente.');
    }

}
