<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Producto;
use App\Models\DetalleVenta;
use App\Models\Cliente;
use App\Models\Factura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;

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
            'usuario' => Auth::user(),
        ]);
    }

    public function store(Request $request)
{
    $request->validate([
        'fecha_venta' => 'required|date',
        'cliente_id' => 'required|exists:clientes,id',
        'metodo_pago' => 'required|in:efectivo,tarjeta',
        'tipo_comprobante' => 'required|in:factura',
        'detalles' => 'required|array|min:1',
        'detalles.*.producto_id' => 'required|exists:productos,id',
        'detalles.*.cantidad' => 'required|integer|min:1',
    ]);

    DB::beginTransaction();

    try {
        $detalles = $request->input('detalles');
        $total = 0;
        $iva_porcentaje = 0.19;

        $venta = Venta::create([
            'cliente_id' => $request->cliente_id,
            'user_id' => auth()->id(),
            'fecha_venta' => now(),
            'total' => 0,
            'metodo_pago' => $request->metodo_pago,
            'estado' => 'pagado',
            'tipo_comprobante' => $request->tipo_comprobante,
        ]);

        foreach ($detalles as $detalle) {
            $producto = Producto::findOrFail($detalle['producto_id']);
            $cantidad = $detalle['cantidad'];
            $precio = $producto->precio_venta;
            $subtotal = $cantidad * $precio;
            $impuesto_iva = $subtotal * $iva_porcentaje;

            $total += $subtotal + $impuesto_iva;

            DetalleVenta::create([
                'venta_id' => $venta->id,
                'producto_id' => $producto->id,
                'cantidad' => $cantidad,
                'precio_unitario' => $precio,
                'subtotal' => $subtotal,
                'impuesto_iva' => $impuesto_iva,
            ]);
        }

        $venta->update([
            'total' => $total
        ]);

        DB::commit();

        return redirect()->route('ventas.index')->with('success', 'Venta registrada correctamente.');
    } catch (\Throwable $e) {
        DB::rollBack();
        return back()->withErrors(['error' => 'Error al registrar la venta: ' . $e->getMessage()]);
    }
}

    public function edit(Venta $venta)
    {
        $venta->load(['cliente', 'user', 'detalleVentas.producto']);

        $clientes = Cliente::all();
        $productos = Producto::all();
        $usuario = Auth::user(); // Obtener el usuario autenticado

        return Inertia::render('Venta/Edit', [
            'venta' => $venta,
            'clientes' => $clientes,
            'productos' => $productos,
            'usuario' => $usuario,
        ]);
    }

    public function update(Request $request, Venta $venta)
    {
        try {
            // Validación de datos (ajusta según tus necesidades)
            $request->validate([
                'cliente_id' => ['required', 'exists:clientes,id'],
                'metodo_pago' => ['required', 'string', 'max:255'],
                'fecha_venta' => ['required', 'date'],
                'tipo_comprobante' => ['required', 'string', 'max:255'],
                'user_id' => ['required', 'exists:users,id'],
                'detalles' => ['required', 'array', 'min:1'],
                'detalles.*.producto_id' => ['required', 'exists:productos,id'],
                'detalles.*.cantidad' => ['required', 'integer', 'min:1'],
                'detalles.*.precio' => ['required', 'numeric', 'min:0'], // Precio unitario enviado desde frontend
                'detalles.*.subtotal' => ['required', 'numeric', 'min:0'],
                'detalles.*.impuesto_iva' => ['required', 'numeric', 'min:0'],
                'detalles.*.total' => ['required', 'numeric', 'min:0'],
                'total_iva' => ['required', 'numeric', 'min:0'],
                'total_venta' => ['required', 'numeric', 'min:0'],
            ]);

            DB::transaction(function () use ($request, $venta) {
                // Actualizar los datos de la venta
                $venta->update($request->only([
                    'cliente_id', 'metodo_pago', 'fecha_venta', 'tipo_comprobante',
                    'user_id', 'total_iva', 'total_venta'
                ]));

                // Eliminar los detalles de venta existentes para luego agregar los nuevos
                $venta->detalleVentas()->delete();

                // Crear nuevos detalles de venta
                foreach ($request->detalles as $detalleData) {
                    $venta->detalleVentas()->create([
                        'producto_id' => $detalleData['producto_id'],
                        'cantidad' => $detalleData['cantidad'],
                        'precio_unitario' => $detalleData['precio'], // Almacenar el precio unitario
                        'subtotal' => $detalleData['subtotal'],
                        'impuesto_iva' => $detalleData['impuesto_iva'],
                        'total' => $detalleData['total'],
                    ]);
                }
            });

            return redirect()->route('ventas.index')->with('success', 'Venta actualizada exitosamente.');

        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar la venta: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Venta $venta)
    {
        $venta->delete();

        return redirect()->route('ventas.index')->with('success', 'Venta eliminada correctamente.');
    }

    private function generarNumeroFactura(): string
    {
        return 'F-' . str_pad(Factura::count() + 1, 6, '0', STR_PAD_LEFT);
    }

    public function generarFactura(Venta $venta)
{
    // Carga relaciones necesarias
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
          ->setPaper([0, 0, 226.77, 453,54],'portrait'); // A7 personalizado en puntos,80mm de ancho × alto variable

    // Devuelve la descarga del archivo PDF
    return $pdf->download('factura_' . $venta->id . '.pdf');
}
}
