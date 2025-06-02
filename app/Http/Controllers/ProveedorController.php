<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Proveedor;
use Inertia\Inertia;

class ProveedorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

         return Inertia::render('Proveedor/Index',['proveedors' => Proveedor::paginate(10),]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Proveedor/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'razon_social'=>'required|string|max:20',
            'nit'=>'required|string|max:20',
            'direccion'=>'required|string|max:30',
            'telefono' => 'required|string|max:20',
            'correo'=>'required|email',
            // Agrega más validaciones si lo necesitas
        ]);
        Proveedor::create($request->all());
        return redirect()->route('proveedors.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
     public function edit(Proveedor $proveedor)
    {
       return Inertia::render('Proveedor/Edit', compact('proveedor'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Proveedor $proveedor)
    {
        $request->validate([
            'razon_social'=>'required|string|max:20',
            'nit'=>'required|string|max:20',
            'direccion'=>'required|string|max:30',
            'telefono' => 'required|string|max:20',
            'correo'=>'required|email',
            // Agrega más validaciones si lo necesitas
        ]);
        $proveedor->update($request->all());
        return redirect()->route('proveedors.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Proveedor $proveedor)
    {
        $proveedor->delete();
        return redirect()->route('proveedors.index');
    }
}
