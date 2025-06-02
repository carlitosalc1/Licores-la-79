<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Compra;
use App\Models\Proveedor;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CompraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       $compras = Compra::with(['proveedor', 'user'])->paginate(10);
       return Inertia::render('Compra/Index', [
       'compras' => $compras,
       ]);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         return Inertia::render('Compra/Create', [
        'proveedores' => Proveedor::select('id', 'razon_social')->get(),
        'usuario' => Auth::user(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->merge([
        'total' => str_replace('.', '', $request->total),
        ]);
        $request->validate([
            'fecha' => 'required|date',
            'total' => 'required|numeric',
            'estado' => 'required|in:completada,cancelada',
            'proveedor_id' => 'required|exists:proveedors,id',
        ]);

        Compra::create([
            'fecha' => $request->fecha,
            'total' => $request->total,
            'estado' => $request->estado,
            'proveedor_id' => $request->proveedor_id,
            'user_id' => Auth::id(),
        ]);
            return redirect()->route('compras.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Compra $compra)
    {
          return Inertia::render('Compra/Edit', [
          'compra' => $compra->load('proveedor', 'user'),
          'proveedores' => Proveedor::select('id', 'razon_social')->get(),
          'usuario' => Auth::user(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Compra $compra)
    {
        $request->merge([
        'total' => str_replace('.', '', $request->total),
        ]);
        $request->validate([
            'fecha' => 'required|date',
            'total' => 'required|numeric',
            'estado' => 'required|in:pagada,cancelada',
            'proveedor_id' => 'required|exists:proveedors,id',
        ]);

        $compra->update([
            'fecha' => $request->fecha,
            'total' => $request->total,
            'estado' => $request->estado,
            'proveedor_id' => $request->proveedor_id,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('compras.index');
    }

    /**
     * Remove the specified resource from storage.
     */
     public function destroy(Compra $compra)
    {
        $compra->delete();
        return redirect()->route('compras.index');
    }
}
