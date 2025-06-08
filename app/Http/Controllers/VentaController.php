<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Producto;
use App\Models\DetalleVenta;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;

class VentaController extends Controller
{
    public function index()
    {
        $ventas = Venta::with([
            'cliente',
            'user',
            'detalleVentas.producto.categoriaProducto'
        ])->latest()->paginate(10);

        return Inertia::render('Venta/Index', [
            'ventas' => $ventas
        ]);
    }

    public function create()
    {
        return Inertia::render('Venta/Create', [
            'clientes' => Cliente::select('id', 'nombre')->get(),
            'productos' => Producto::select('id', 'nombre', 'precio_venta')->get(),
            'usuario' => Auth::user()->only('id', 'name'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha_venta' => 'required|date',
            'cliente_id' => 'required|exists:clientes,id',
            'metodo_pago' => 'required|in:efectivo,tarjeta_credito,tarjeta_debito',
            'tipo_comprobante' => 'required|in:factura',
            'estado' => 'required|in:pendiente,pagado,cancelada',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:productos,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'total_iva' => 'required|numeric|min:0',
            'total_venta' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $detallesRequest = $request->input('detalles');
            $totalVentaCalculado = 0;
            $totalIvaCalculado = 0;
            $iva_porcentaje = 0.19;

            // Validar disponibilidad de stock para todos los productos
            foreach ($detallesRequest as $detalle) {
                $stock_actual = Inventario::getStockActual($detalle['producto_id']);
                if ($stock_actual < $detalle['cantidad']) {
                    throw new \Exception('No hay suficiente stock para el producto ID ' . $detalle['producto_id']);
                }
            }

            foreach ($detallesRequest as $detalle) {
                $producto = Producto::findOrFail($detalle['producto_id']);
                $cantidad = $detalle['cantidad'];
                $precio = (float) $producto->precio_venta;
                $subtotal = $cantidad * $precio;
                $impuesto_iva = $subtotal * $iva_porcentaje;
                $totalVentaCalculado += $subtotal + $impuesto_iva;
                $totalIvaCalculado += $impuesto_iva;
            }

            $venta = Venta::create([
                'cliente_id' => $request->cliente_id,
                'user_id' => auth()->id(),
                'fecha_venta' => $request->fecha_venta,
                'total' => $totalVentaCalculado,
                'metodo_pago' => $request->metodo_pago,
                'estado' => $request->estado,
                'tipo_comprobante' => $request->tipo_comprobante,
                'total_iva' => $totalIvaCalculado,
            ]);

            foreach ($detallesRequest as $detalle) {
                $producto = Producto::findOrFail($detalle['producto_id']);
                $cantidad = $detalle['cantidad'];
                $precio = (float) $producto->precio_venta;
                $subtotal = $cantidad * $precio;
                $impuesto_iva = $subtotal * $iva_porcentaje;

                // Crear detalle de venta
                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $producto->id,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio,
                    'subtotal' => $subtotal,
                    'impuesto_iva' => $impuesto_iva,
                    'total' => $subtotal + $impuesto_iva,
                ]);

                // Reducir stock en productos
                $producto->decrement('stock', $cantidad);

                // Crear movimiento de inventario
                Inventario::create([
                    'producto_id' => $detalle['producto_id'],
                    'venta_id' => $venta->id,
                    'cantidad_entrada' => 0,
                    'cantidad_salida' => $detalle['cantidad'],
                    'tipo_movimiento' => 'salida',
                    'fecha_actualizacion' => now(),
                ]);
            }

            DB::commit();

            return redirect()->route('ventas.index')->with('success', 'Venta registrada correctamente.');
        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al registrar la venta: ' . $e->getMessage()])->withInput();
        }
    }

    public function edit(Venta $venta)
    {
        $venta->load(['cliente', 'user', 'detalleVentas.producto']);

        return Inertia::render('Venta/Edit', [
            'venta' => [
                'id' => $venta->id,
                'cliente_id' => $venta->cliente_id,
                'metodo_pago' => $venta->metodo_pago,
                'fecha_venta' => $venta->fecha_venta,
                'tipo_comprobante' => $venta->tipo_comprobante,
                'user_id' => $venta->user_id,
                'estado' => $venta->estado,
                'total_iva' => $venta->total_iva,
                'total' => $venta->total,
                'detalle_ventas' => $venta->detalleVentas->map(function ($detalle) {
                    return [
                        'id' => $detalle->id,
                        'producto_id' => $detalle->producto_id,
                        'producto' => [
                            'nombre' => $detalle->producto->nombre,
                            'precio_venta' => $detalle->producto->precio_venta
                        ],
                        'cantidad' => $detalle->cantidad,
                        'precio_unitario' => $detalle->precio_unitario,
                        'subtotal' => $detalle->subtotal,
                        'impuesto_iva' => $detalle->impuesto_iva,
                        'total' => $detalle->total,
                    ];
                }),
                'total_iva' => $venta->total_iva,
                'total_venta' => $venta->total
            ],
            'clientes' => Cliente::all(),
            'productos' => Producto::all(),
            'usuario' => Auth::user()->only('id', 'name'),
        ]);
    }

    public function update(Request $request, Venta $venta)
    {
        try {
            $request->validate([
                'cliente_id' => ['required', 'exists:clientes,id'],
                'metodo_pago' => ['required', 'string', 'max:255'],
                'fecha_venta' => ['required', 'date'],
                'tipo_comprobante' => ['required', 'string', 'max:255'],
                'user_id' => ['required', 'exists:users,id'],
                'estado' => ['required', 'string', 'in:pendiente,pagado,cancelada'],
                'detalles' => ['required', 'array', 'min:1'],
                'detalles.*.producto_id' => ['required', 'exists:productos,id'],
                'detalles.*.cantidad' => ['required', 'integer', 'min:1'],
                'detalles.*.precio' => ['required', 'numeric', 'min:0'],
                'detalles.*.subtotal' => ['required', 'numeric', 'min:0'],
                'detalles.*.impuesto_iva' => ['required', 'numeric', 'min:0'],
                'detalles.*.total' => ['required', 'numeric', 'min:0'],
                'total_iva' => ['required', 'numeric', 'min:0'],
                'total_venta' => ['required', 'numeric', 'min:0'],
            ]);

            DB::transaction(function () use ($request, $venta) {
                $totalVentaCalculado = 0;
                $totalIvaCalculado = 0;
                $iva_porcentaje = 0.19;

                // Validar stock para los nuevos detalles
                foreach ($request->detalles as $detalleData) {
                    $stock_actual = Inventario::getStockActual($detalleData['producto_id']);
                    if ($stock_actual < $detalleData['cantidad']) {
                        throw new \Exception('No hay suficiente stock para el producto ID ' . $detalleData['producto_id']);
                    }
                }

                // Eliminar detalles y movimientos de inventario existentes
                $venta->detalleVentas()->delete();
                Inventario::where('venta_id', $venta->id)->delete();

                foreach ($request->detalles as $detalleData) {
                    $producto = Producto::findOrFail($detalleData['producto_id']);
                    $cantidad = $detalleData['cantidad'];
                    $precio = (float) $producto->precio_venta;

                    $subtotal = $cantidad * $precio;
                    $impuesto_iva = $subtotal * $iva_porcentaje;

                    $totalVentaCalculado += $subtotal + $impuesto_iva;
                    $totalIvaCalculado += $impuesto_iva;
                }

                $venta->update([
                    'cliente_id' => $request->cliente_id,
                    'metodo_pago' => $request->metodo_pago,
                    'fecha_venta' => $request->fecha_venta,
                    'tipo_comprobante' => $request->tipo_comprobante,
                    'user_id' => $request->user_id,
                    'estado' => $request->estado,
                    'total' => $totalVentaCalculado,
                    'total_iva' => $totalIvaCalculado,
                ]);

                foreach ($request->detalles as $detalleData) {
                    $producto = Producto::findOrFail($detalleData['producto_id']);
                    $cantidad = $detalleData['cantidad'];
                    $precio = (float) $producto->precio_venta;
                    $subtotal = $cantidad * $precio;
                    $impuesto_iva = $subtotal * $iva_porcentaje;

                    // Crear nuevo detalle de venta
                    $venta->detalleVentas()->create([
                        'venta_id' => $venta->id,
                        'producto_id' => $detalleData['producto_id'],
                        'cantidad' => $cantidad,
                        'precio_unitario' => $precio,
                        'subtotal' => $subtotal,
                        'impuesto_iva' => $impuesto_iva,
                        'total' => $subtotal + $impuesto_iva,
                    ]);

                    // Crear nuevo movimiento de inventario
                    Inventario::create([
                        'producto_id' => $detalleData['producto_id'],
                        'venta_id' => $venta->id,
                        'cantidad_entrada' => 0,
                        'cantidad_salida' => $detalleData['cantidad'],
                        'tipo_movimiento' => 'salida',
                        'fecha_actualizacion' => now(),
                    ]);
                }
            });

            return redirect()->route('ventas.index')->with('success', 'Venta actualizada exitosamente.');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al actualizar la venta: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Venta $venta)
    {
        DB::transaction(function () use ($venta) {
            // Eliminar movimientos de inventario asociados
            Inventario::where('venta_id', $venta->id)->delete();
            $venta->delete();
        });

        return redirect()->route('ventas.index')->with('success', 'Venta eliminada correctamente.');
    }

    private function generarNumeroFactura(): string
    {
        return 'F-' . str_pad(Factura::count() + 1, 6, '0', STR_PAD_LEFT);
    }

    public function generarFactura(Venta $venta)
    {
        $venta->load([
            'cliente',
            'user',
            'detalleVentas.producto',
        ]);

        // Datos que se pasarán a la vista PDF
        $data = [
            'venta' => $venta,
            'cliente' => $venta->cliente,
            'cajero' => $venta->user,
            'detalles_venta' => $venta->detalleVentas,
            'fecha_actual' => now()->format('d/m/Y H:i'),
        ];

        // Renderiza la vista en PDF
        $pdf = Pdf::loadView('pdf.factura', $data)
            ->setPaper([0, 0, 226.77, 453.54], 'portrait');

        // Devuelve la descarga del archivo PDF
        return $pdf->download('factura_' . $venta->id . '.pdf');
    }
}
