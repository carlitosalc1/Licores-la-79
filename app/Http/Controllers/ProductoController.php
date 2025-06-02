<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\CategoriaProducto;
use Inertia\Inertia;

class ProductoController extends Controller
{
    /**
     * Listado de productos
     */
    public function index()
    {
       $productos = Producto::with('categoriaProducto')->paginate(10);
       return Inertia::render('Producto/Index', ['productos' => $productos]);
    }

    /**
     *  Mostrar formulario de creación
     */
    public function create()
    {
        $categoriadeproductos = CategoriaProducto::all();
        return Inertia::render('Producto/Create', ['categoriadeproductos' => $categoriadeproductos]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->merge([
        'precio_compra' => str_replace('.', '', $request->precio_compra),
        'precio_venta' => str_replace('.', '', $request->precio_venta),
        ]);
       $validated = $request->validate([
            'nombre' => 'required|string|max:30',
            'descripcion' => 'nullable|string|max:300',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'unidad_medida' => 'required|in:Kilogramos,Gramos,Galón,Litros,Mililitros,Panal',
            'stock' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'categoria_producto_id' => 'required|exists:categoria_productos,id',
            // Agrega más validaciones si lo necesitas
        ]);
        Producto::create($validated);
        return redirect()->route('productos.index');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Producto $producto)
    {
       $categoriadeproductos = CategoriaProducto::all();
        return Inertia::render('Producto/Edit', ['producto' => $producto, 'categoriadeproductos' => $categoriadeproductos]);
    }

    /**
     * Mostrar formulario de Actualización de producto
     */
    public function update(Request $request, Producto $producto)
    {
          $request->merge([
        'precio_compra' => str_replace('.', '', $request->precio_compra),
        'precio_venta' => str_replace('.', '', $request->precio_venta),
        ]);
        $validated = $request->validate([
            'nombre' => 'required|string|max:30',
            'descripcion' => 'nullable|string|max:300',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'unidad_medida' => 'required|in:Kilogramos,Gramos,Galón,Litros,Mililitros,Panal',
            'stock' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'categoria_producto_id' => 'required|exists:categoria_productos,id',
            // Agrega más validaciones si lo necesitas
        ]);
        $producto->update($validated);
        return redirect()->route('productos.index');
    }

    /**
     * Eliminar producto
     */
    public function destroy(Producto $producto)
    {
        $producto->delete();
        return redirect()->route('productos.index');
    }
}
