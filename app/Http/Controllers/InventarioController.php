<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InventarioController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Obtener movimientos de inventario con filtros y paginación
        $inventarios = Inventario::query()
            ->with(['producto', 'compra', 'venta'])
            ->when($search, function ($query, $search) {
                $query->whereHas('producto', function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%");
                })
                ->orWhere('tipo_movimiento', 'like', "%{$search}%")
                ->orWhere('compra_id', 'like', "%{$search}%")
                ->orWhere('venta_id', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);

        // Obtener lista de productos con stock y stock mínimo
        $productos = Producto::all()->map(function ($producto) {
            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'stock' => $producto->stock,
                'stock_minimo' => $producto->stock_minimo,
            ];
        });

        return Inertia::render('Inventarios/Index', [
            'inventarios' => $inventarios,
            'productos' => $productos,
            'filters' => $request->only('search'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        $productos = Producto::all(['id', 'nombre']);
        return Inertia::render('Inventarios/Create', [
            'productos' => $productos,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'producto_id' => ['required', 'exists:productos,id'],
            'tipo_movimiento' => ['required', 'in:entrada,salida,ajuste'],
            'cantidad_entrada' => ['nullable', 'integer', 'min:0', 'required_if:tipo_movimiento,entrada,ajuste'],
            'cantidad_salida' => ['nullable', 'integer', 'min:0', 'required_if:tipo_movimiento,salida,ajuste'],
            'fecha_actualizacion' => ['required', 'date'],
            'compra_id' => ['nullable', 'exists:compras,id'],
            'venta_id' => ['nullable', 'exists:ventas,id'],
        ]);

        DB::transaction(function () use ($request) {
            Inventario::create([
                'producto_id' => $request->producto_id,
                'compra_id' => $request->compra_id,
                'venta_id' => $request->venta_id,
                'cantidad_entrada' => $request->cantidad_entrada ?? 0,
                'cantidad_salida' => $request->cantidad_salida ?? 0,
                'tipo_movimiento' => $request->tipo_movimiento,
                'fecha_actualizacion' => $request->fecha_actualizacion,
            ]);
        });

        return redirect()->route('inventarios.index')
            ->with('success', 'Movimiento de inventario creado exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Inventario  $inventario
     * @return \Inertia\Response
     */
    public function edit(Inventario $inventario)
    {
        $productos = Producto::all(['id', 'nombre']);
        $inventario->load(['producto']);

        return Inertia::render('Inventarios/Edit', [
            'inventario' => $inventario,
            'productos' => $productos,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Inventario  $inventario
     * @return \Illuminate\Http\RedirectResponse
     */
     public function getStockActual(Producto $producto)
    {
        return response()->json(['stock_actual' => $producto->stock]);
    }
    public function update(Request $request, Inventario $inventario)
    {
        // 1. Validar los datos de entrada
        $rules = [
            'producto_id' => ['required', 'exists:productos,id'],
            'tipo_movimiento' => ['required', 'in:entrada,salida,ajuste'],
            'cantidad_entrada' => ['nullable', 'integer', 'min:0'],
            'cantidad_salida' => ['nullable', 'integer', 'min:0'],
            'fecha_actualizacion' => ['required', 'date'],
            'compra_id' => ['nullable', 'exists:compras,id'], // Si usas compras
            'venta_id' => ['nullable', 'exists:ventas,id'],   // Si usas ventas
        ];

        // Lógica de validación condicional para cantidades
        if ($request->input('tipo_movimiento') === 'entrada') {
            $rules['cantidad_entrada'] = ['required', 'integer', 'min:1'];
            $rules['cantidad_salida'] = ['nullable', 'integer', 'in:0']; // No debe haber cantidad_salida
        } elseif ($request->input('tipo_movimiento') === 'salida') {
            $rules['cantidad_salida'] = ['required', 'integer', 'min:1'];
            $rules['cantidad_entrada'] = ['nullable', 'integer', 'in:0']; // No debe haber cantidad_entrada
        } elseif ($request->input('tipo_movimiento') === 'ajuste') {
            // Para ajuste, se puede requerir al menos una de las dos cantidades
            $rules['cantidad_entrada'] = ['nullable', 'integer', 'min:0'];
            $rules['cantidad_salida'] = ['nullable', 'integer', 'min:0'];
            // Opcional: Validar que al menos una cantidad sea > 0 para ajustes
            // if ($request->input('cantidad_entrada') == 0 && $request->input('cantidad_salida') == 0) {
            //     throw ValidationException::withMessages(['cantidad' => 'Para un ajuste, la cantidad de entrada o salida debe ser mayor a 0.']);
            // }
        }

        $validatedData = $request->validate($rules);

        // Iniciar una transacción de base de datos para asegurar la consistencia
        // Si algo falla, todo se revierte.
        DB::beginTransaction();

        try {
            $producto = Producto::findOrFail($validatedData['producto_id']);

            // **Lógica para revertir el efecto del movimiento original**
            // Primero, revertimos el stock como si el movimiento anterior nunca hubiera existido.
            if ($inventario->tipo_movimiento === 'entrada') {
                $producto->stock -= $inventario->cantidad_entrada;
            } elseif ($inventario->tipo_movimiento === 'salida') {
                $producto->stock += $inventario->cantidad_salida;
            } elseif ($inventario->tipo_movimiento === 'ajuste') {
                $producto->stock -= $inventario->cantidad_entrada; // Revertir entrada original
                $producto->stock += $inventario->cantidad_salida; // Revertir salida original
            }
            $producto->save(); // Guardar el stock revertido temporalmente


            // **Actualizar el registro del movimiento de inventario**
            $inventario->producto_id = $validatedData['producto_id'];
            $inventario->tipo_movimiento = $validatedData['tipo_movimiento'];
            $inventario->cantidad_entrada = $validatedData['cantidad_entrada'] ?? 0; // Asegura que sea 0 si es null
            $inventario->cantidad_salida = $validatedData['cantidad_salida'] ?? 0;   // Asegura que sea 0 si es null
            $inventario->fecha_actualizacion = $validatedData['fecha_actualizacion'];
            $inventario->compra_id = $validatedData['compra_id'];
            $inventario->venta_id = $validatedData['venta_id'];
            $inventario->save(); // Guardar los nuevos datos del movimiento


            // **Aplicar el efecto del movimiento editado**
            // Ahora, aplicamos el nuevo stock según los datos editados
            if ($inventario->tipo_movimiento === 'entrada') {
                $producto->stock += $inventario->cantidad_entrada;
            } elseif ($inventario->tipo_movimiento === 'salida') {
                // Validación para stock negativo antes de restar
                if ($producto->stock < $inventario->cantidad_salida) {
                    throw ValidationException::withMessages([
                        'cantidad_salida' => 'No hay suficiente stock para esta salida. Stock actual: ' . $producto->stock
                    ]);
                }
                $producto->stock -= $inventario->cantidad_salida;
            } elseif ($inventario->tipo_movimiento === 'ajuste') {
                $producto->stock += $inventario->cantidad_entrada; // Aplicar nueva entrada
                // Validación para stock negativo para el ajuste de salida
                if ($producto->stock < $inventario->cantidad_salida) {
                     throw ValidationException::withMessages([
                        'cantidad_salida' => 'El ajuste de salida resultaría en stock negativo. Stock actual después de entradas: ' . $producto->stock
                    ]);
                }
                $producto->stock -= $inventario->cantidad_salida; // Aplicar nueva salida
            }

            $producto->save(); // Guardar el stock final después de aplicar el movimiento editado

            DB::commit(); // Confirmar la transacción

            return redirect()->route('inventarios.index')->with('success', 'Movimiento de inventario actualizado correctamente.');

        } catch (ValidationException $e) {
            DB::rollBack(); // Revertir la transacción si hay un error de validación
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir la transacción si hay algún otro error
            return back()->with('error', 'Hubo un error al actualizar el movimiento: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Inventario  $inventario
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Inventario $inventario)
    {
        DB::transaction(function () use ($inventario) {
            $inventario->delete();
        });

        return redirect()->route('inventarios.index')
            ->with('success', 'Movimiento de inventario eliminado exitosamente.');
    }

    /**
     * Get the current stock of a product.
     * Este método podría ser llamado vía AJAX desde tu frontend Inertia
     *
     * @param  int  $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStock($productId)
    {
         $stockActual = Inventario::where('producto_id', $productId)
        ->sum('cantidad_entrada') - Inventario::where('producto_id', $productId)
        ->sum('cantidad_salida');

    return response()->json(['stock_actual' => $stockActual]);
    }
}
