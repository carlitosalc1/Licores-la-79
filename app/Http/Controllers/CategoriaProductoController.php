<?php

namespace App\Http\Controllers;

use App\Models\CategoriaProducto;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoriaProductoController extends Controller
{
    public function index()
    {
         $categoriadeproductos = CategoriaProducto::paginate(10);
         return Inertia::render('CategoriaProducto/Index', [
         'categoriadeproductos' => $categoriadeproductos
         ]);
    }

    public function create()
    {
        return Inertia::render('CategoriaProducto/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:30',
            'descripcion' => 'nullable|string',
        ]);
        CategoriaProducto::create($request->all());
        return redirect()->route('categoria_productos.index');
    }

    public function edit(CategoriaProducto $categoriaProducto)
    {
        return Inertia::render('CategoriaProducto/Edit', ['categoria_producto' => $categoriaProducto]);
    }

    public function update(Request $request, CategoriaProducto $categoriaProducto)
    {
        $request->validate([
            'nombre' => 'required|string|max:30',
            'descripcion' => 'nullable|string',
        ]);
        $categoriaProducto->update($request->all());
        return redirect()->route('categoria_productos.index');
    }

    public function destroy(CategoriaProducto $categoriaProducto)
    {
        $categoriaProducto->delete();
        return redirect()->route('categoria_productos.index');
    }
}
