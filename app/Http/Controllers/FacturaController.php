<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\Venta;
use App\Models\Producto;
use App\Models\DetalleFactura;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class FacturaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $facturas = Factura::query()
            ->with(['venta.user'])
            ->when($request->input('search'), function ($query, $search) {
                $query->where('numero_factura', 'like', '%' . $search . '%')
                      ->orWhereHas('venta', function ($q) use ($search) {
                          $q->where('id', 'like', '%' . $search . '%');
                      })
                      ->orWhere('estado', 'like', '%' . $search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Factura/Index', [
            'facturas' => $facturas,
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
        $ventas = Venta::with('cliente')->get();
        $productos = Producto::all();
        return Inertia::render('Factura/Create', [
            'ventas' => $ventas,
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
            'venta_id' => ['required', 'exists:ventas,id', 'unique:facturas,venta_id'],
            'total' => ['required', 'numeric', 'min:0'],
            'metodo_pago' => ['required', 'in:efectivo,tarjeta_credito,tarjeta_debito'],
            'estado' => ['sometimes', 'in:pendiente,pagada,anulada'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.producto_id' => ['required', 'exists:productos,id'],
            'detalles.*.cantidad' => ['required', 'integer', 'min:1'],
            'detalles.*.precio_unitario' => ['required', 'numeric', 'min:0'],
            'detalles.*.subtotal' => ['required', 'numeric', 'min:0'],
            'detalles.*.impuesto_iva' => ['required', 'numeric', 'min:0'],
        ]);

        DB::beginTransaction();
        try {
            $factura = Factura::create([
                'venta_id' => $request->venta_id,
                'total' => $request->total,
                'metodo_pago' => $request->metodo_pago,
                'estado' => $request->estado ?? 'pendiente',
            ]);

            foreach ($request->detalles as $detalle) {
                $factura->detalleFacturas()->create($detalle);
            }

            DB::commit();

            return redirect()->route('facturas.index')
                ->with('success', 'Factura creada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al crear la factura: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Factura  $factura
     * @return \Inertia\Response
     */
    public function show(Factura $factura)
    {
        $factura->load(['venta.user', 'detalleFacturas.producto']);
        return Inertia::render('Facturas/Show', [
            'factura' => $factura,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Factura  $factura
     * @return \Inertia\Response
     */
    public function edit(Factura $factura)
    {
        $ventas = Venta::all();
        $productos = Producto::all();
        $factura->load('detalleFacturas.producto');
        return Inertia::render('Factura/Edit', [
            'factura' => $factura,
            'ventas' => $ventas,
            'productos' => $productos,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Factura  $factura
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Factura $factura)
    {
        $request->validate([
            'total' => ['required', 'numeric', 'min:0'],
            'metodo_pago' => ['required', 'in:efectivo,tarjeta_credito,tarjeta_debito'],
            'estado' => ['sometimes', 'in:pendiente,pagada,anulada'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.id' => ['nullable', 'exists:detalle_facturas,id'],
            'detalles.*.producto_id' => ['required', 'exists:productos,id'],
            'detalles.*.cantidad' => ['required', 'integer', 'min:1'],
            'detalles.*.precio_unitario' => ['required', 'numeric', 'min:0'],
            'detalles.*.subtotal' => ['required', 'numeric', 'min:0'],
            'detalles.*.impuesto_iva' => ['required', 'numeric', 'min:0'],
        ]);

        DB::beginTransaction();
        try {
            $factura->update([
                'total' => $request->total,
                'metodo_pago' => $request->metodo_pago,
                'estado' => $request->estado,
            ]);

            $currentDetailIds = $factura->detalleFacturas->pluck('id')->toArray();
            $incomingDetailIds = collect($request->detalles)->pluck('id')->filter()->toArray();
            DetalleFactura::whereIn('id', array_diff($currentDetailIds, $incomingDetailIds))->delete();

            foreach ($request->detalles as $detalle) {
                if (isset($detalle['id'])) {

                    $factura->detalleFacturas()->where('id', $detalle['id'])->update($detalle);
                } else {
                    $factura->detalleFacturas()->create($detalle);
                }
            }

            DB::commit();

            return redirect()->route('facturas.index')
                ->with('success', 'Factura actualizada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al actualizar la factura: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Factura  $factura
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Factura $factura)
    {
        try {
            $factura->delete();
            return redirect()->route('facturas.index')
                ->with('success', 'Factura eliminada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al eliminar la factura: ' . $e->getMessage());
        }
    }
}
