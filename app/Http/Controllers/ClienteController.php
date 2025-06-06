<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cliente;
use Inertia\Inertia;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Cliente/Index',['clientes' => Cliente::paginate(10),]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Cliente/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tipo_identificacion' => 'nullable|in:Cédula de Ciudadanía,Cédula de Extranjería,Pasaporte',
            'numero_identificacion' => 'nullable|string|regex:/^\d{8,20}$/',
            'nombre'=>'required|string|max:20',
            'apellido'=>'nullable|string|max:20',
            'direccion'=>'nullable|string|max:30',
            'telefono' => 'nullable|string|max:20|regex:/^[\d\s+\-()]+$/',
            'correo'=>'nullable|email',
        ]);
        Cliente::create($request->all());
        return redirect()->route('clientes.index')->with('success', 'Cliente creado correctamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cliente $cliente)
    {
        return Inertia::render('Cliente/Edit', compact('cliente'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cliente $cliente)
    {
        $request->validate([
            'tipo_identificacion' => 'nullable|in:Cédula de Ciudadanía,Cédula de Extranjería,Pasaporte',
            'numero_identificacion' => 'nullable|string|regex:/^\d{8,20}$/',
            'nombre'=>'required|string|max:20',
            'apellido'=>'nullable|string|max:20',
            'direccion'=>'nullable|string|max:30',
            'telefono' => 'nullable|string|max:20|regex:/^[\d\s+\-()]+$/',
            'correo'=>'nullable|email',
        ]);

        $cliente->update($request->all());
        return redirect()->route('clientes.index')->with('success', 'Cliente actualizado correctamente.');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cliente $cliente)
    {
        try {
            $cliente->delete();

            return redirect()->route('clientes.index')->with('success', 'Cliente eliminado correctamente. Sus ventas asociadas han sido conservadas.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al eliminar el cliente: ' . $e->getMessage());
        }
    }
}
