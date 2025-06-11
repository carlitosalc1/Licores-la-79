<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\Venta;
use App\Models\User;
use App\Models\Cliente;
use App\Models\DetalleFactura;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class FacturaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $facturas = Factura::with(['venta.user', 'cliente', 'user'])
                            ->when($search, function ($query, $search) {
                                // Aplicar filtro de búsqueda
                                $query->where('numero_factura', 'like', '%' . $search . '%')
                                      ->orWhere('metodo_pago', 'like', '%' . $search . '%')
                                      ->orWhere('estado', 'like', '%' . $search . '%')

                                      ->orWhereHas('venta', function ($q) use ($search) {
                                          $q->where('id', 'like', '%' . $search . '%');
                                      })

                                      ->orWhereHas('cliente', function ($q) use ($search) {
                                          $q->where('nombre', 'like', '%' . $search . '%');
                                      })

                                      ->orWhereHas('user', function ($q) use ($search) {
                                          $q->where('name', 'like', '%' . $search . '%');
                                      });
                            })
                            ->latest('id')
                            ->paginate(10)
                            ->withQueryString();

        return Inertia::render('Factura/Index', [
            'facturas' => $facturas,
            'filters' => ['search' => $search],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $ventasSinFactura = Venta::doesntHave('factura')
                                ->with(['cliente', 'user', 'detalleVentas.producto'])
                                ->get()
                                ->map(function ($venta) {
                                    return [
                                        'id' => $venta->id,
                                        'total_venta' => $venta->total,
                                        'cliente' => [
                                            'id' => $venta->cliente->id ?? null,
                                            'nombre' => $venta->cliente->nombre ?? 'N/A',
                                        ],
                                        'user' => [
                                            'id' => $venta->user->id ?? null,
                                            'name' => $venta->user->name ?? 'N/A',
                                        ],
                                        // Mapear los detalles de la venta para enviarlos al frontend
                                        'detalles_venta' => $venta->detalleVentas->map(function ($detalle) {
                                            return [
                                                'producto_id' => $detalle->producto_id,
                                                'producto_nombre' => $detalle->producto->nombre ?? 'N/A',
                                                'cantidad' => $detalle->cantidad,
                                                'precio_unitario' => $detalle->precio_unitario,
                                                'subtotal' => $detalle->subtotal,
                                                'impuesto_iva' => $detalle->impuesto_iva,
                                            ];
                                        }),
                                    ];
                                });


        $clientes = Cliente::all()->map(fn ($cliente) => ['id' => $cliente->id, 'nombre' => $cliente->nombre]);
        $users = User::all()->map(fn ($user) => ['id' => $user->id, 'name' => $user->name]);

        return Inertia::render('Factura/Create', [
            'ventasSinFactura' => $ventasSinFactura,
            'clientes' => $clientes,
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validar los datos de la factura principal
        $validatedFactura = $request->validate([
            'venta_id'      => ['required', 'exists:ventas,id', 'unique:facturas,venta_id'],
            'user_id'       => ['required', 'exists:users,id'],
            'cliente_id'    => ['nullable', 'exists:clientes,id'],
            'metodo_pago'   => ['required', 'string', Rule::in(['efectivo', 'tarjeta_credito', 'tarjeta_debito'])],
            'estado'        => ['required', 'string', Rule::in(['pendiente', 'pagada', 'anulada'])],

        ]);

        // Validar los datos de los detalles de la factura que vienen del frontend
        $validatedDetalles = $request->validate([
            'detalles_factura'                       => ['required', 'array', 'min:1'],
            'detalles_factura.*.producto_id'         => ['required', 'exists:productos,id'],
            'detalles_factura.*.cantidad'            => ['required', 'integer', 'min:1'],
            'detalles_factura.*.precio_unitario'     => ['required', 'numeric', 'min:0'],
            'detalles_factura.*.subtotal'            => ['required', 'numeric', 'min:0'],
            'detalles_factura.*.impuesto_iva'        => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validatedFactura, $validatedDetalles) {
            // Calcular el total de la factura sumando los subtotales e impuestos de los detalles
            $totalFactura = collect($validatedDetalles['detalles_factura'])->sum(function ($detalle) {
                return (float)$detalle['subtotal'] + (float)$detalle['impuesto_iva'];
            });

            // Crear la factura principal
            $factura = Factura::create([
                'venta_id' => $validatedFactura['venta_id'],
                'user_id' => $validatedFactura['user_id'],
                'cliente_id' => $validatedFactura['cliente_id'],
                'fecha_emision' => Carbon::now(),
                'total' => $totalFactura,
                'metodo_pago' => $validatedFactura['metodo_pago'],
                'estado' => $validatedFactura['estado'],
            ]);

            // Crear los detalles de la factura asociados a la factura recién creada
            foreach ($validatedDetalles['detalles_factura'] as $detalleData) {
                $factura->detalleFacturas()->create($detalleData);
            }

            // Opcional: Actualizar el estado de la Venta a 'facturada' o similar
            // $venta = Venta::find($validatedFactura['venta_id']);
            // if ($venta) {
            //     $venta->update(['estado_facturado' => true]);
            // }
        });

        return redirect()->route('facturas.index')->with('success', 'Factura creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Factura $factura)
    {
        // Cargar todas las relaciones anidadas para mostrar un detalle completo de la factura
        $factura->load(['venta.user', 'cliente', 'user', 'detalleFacturas.producto']);
        return Inertia::render('Factura/Show', [
            'factura' => $factura,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Factura $factura)
    {
        $factura->load(['venta.user', 'cliente', 'user', 'detalleFacturas.producto']);
        $clientes = Cliente::all()->map(fn ($cliente) => ['id' => $cliente->id, 'nombre' => $cliente->nombre]);
        $users = User::all()->map(fn ($user) => ['id' => $user->id, 'name' => $user->name]);
        return Inertia::render('Factura/Edit', [
            'factura' => $factura,
            'clientes' => $clientes,
            'users' => $users,
        ]);
    }

      /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Factura $factura)
    {
        // Validar los campos de la factura principal que se pueden editar
        $validatedFactura = $request->validate([
            // 'venta_id' no suele cambiarse en un update de factura, se asume inmutable.
            'user_id'       => ['required', 'exists:users,id'],
            'cliente_id'    => ['nullable', 'exists:clientes,id'],
            'metodo_pago'   => ['required', 'string', Rule::in(['efectivo', 'tarjeta_credito', 'tarjeta_debito'])],
            'estado'        => ['required', 'string', Rule::in(['pendiente', 'pagada', 'anulada'])],
        ]);

        DB::transaction(function () use ($validatedFactura, $factura, $request) {
            // Valor por defecto si no se envían nuevos detalles
            $totalFacturaRecalculado = $factura->total;

            if ($request->has('detalles_factura') && is_array($request->detalles_factura)) {
                $validatedDetalles = $request->validate([
                    'detalles_factura.*.id'                  => ['nullable', 'exists:detalle_facturas,id'],
                    'detalles_factura.*.producto_id'         => ['required', 'exists:productos,id'],
                    'detalles_factura.*.cantidad'            => ['required', 'integer', 'min:1'],
                    'detalles_factura.*.precio_unitario'     => ['required', 'numeric', 'min:0'],
                    'detalles_factura.*.subtotal'            => ['required', 'numeric', 'min:0'],
                    'detalles_factura.*.impuesto_iva'        => ['required', 'numeric', 'min:0'],
                ]);

                // Recalcular total
                $totalFacturaRecalculado = collect($validatedDetalles['detalles_factura'])->sum(function ($detalle) {
                    return (float)$detalle['subtotal'] + (float)$detalle['impuesto_iva'];
                });

                // Eliminar detalles no presentes
                $currentDetailIds = $factura->detalleFacturas->pluck('id')->toArray();
                $incomingDetailIds = collect($validatedDetalles['detalles_factura'])->pluck('id')->filter()->toArray();
                $detailsToDelete = array_diff($currentDetailIds, $incomingDetailIds);
                if (!empty($detailsToDelete)) {
                    DetalleFactura::whereIn('id', $detailsToDelete)->delete();
                }

                // Crear o actualizar
                foreach ($validatedDetalles['detalles_factura'] as $detalleData) {
                    if (isset($detalleData['id'])) {
                        // Actualizar
                        $detalle = $factura->detalleFacturas()->where('id', $detalleData['id'])->first();
                        if ($detalle) {
                            $detalle->update($detalleData);
                        }
                    } else {
                        // Crear
                        $factura->detalleFacturas()->create($detalleData);
                    }
                }
            }

            // Actualizar la factura
            $factura->update([
                'user_id' => $validatedFactura['user_id'],
                'cliente_id' => $validatedFactura['cliente_id'],
                'metodo_pago' => $validatedFactura['metodo_pago'],
                'estado' => $validatedFactura['estado'],
                'total' => $totalFacturaRecalculado,
            ]);
        });

        return redirect()->route('facturas.index')->with('success', 'Factura actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
   public function destroy(Factura $factura)
{
    DB::transaction(function () use ($factura) {
        // Eliminar detalles primero si es necesario
        $factura->detalleFacturas()->delete();
        $factura->delete();
    });

    return redirect()->route('facturas.index')->with('success', 'Factura eliminada');
}
}
