<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use App\Models\Producto;
use App\Models\Inventario;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    /**
     * Listar todos los reportes
     */
    public function index(Request $request)
    {
        $query = Reporte::query();

        // Implementar búsqueda y filtrado si es necesario
        if ($request->has('search')) {
            $query->where('tipo', 'like', '%' . $request->input('search') . '%')
                  ->orWhere('descripcion', 'like', '%' . $request->input('search') . '%');
        }

        // Ordenamiento
        $sortColumn = $request->input('sort_column', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortColumn, $sortDirection);

        $reportes = $query->paginate(10)->withQueryString();

        return Inertia::render('Reporte/Index', [
            'reportes' => $reportes,
            'filters' => $request->all(['search', 'sort_column', 'sort_direction']),
        ]);
    }

    /**
     * Mostrar formulario para generar reporte
     */
    public function create()
    {
        return Inertia::render('Reporte/Create', [
            'tiposReporte' => ['inventario', 'ventas', 'compras'],
        ]);
    }

    /**
     * Generar y almacenar un reporte
     */
    public function store(Request $request)
    {
        $request->validate([
            'tipo' => ['required', 'string', 'in:ventas,compras,inventario'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'filtros' => ['nullable', 'array'],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            DB::beginTransaction();

            $reporte = Reporte::create([
                'user_id' => Auth::id(),
                'tipo' => $request->tipo,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'filtros' => $request->filtros,
                'descripcion' => $request->descripcion,
                'estado' => 'generado', // Estado inicial
            ]);

            // Aquí puedes llamar a getDatos() si necesitas procesar el reporte inmediatamente
            // o en un job en segundo plano
            // $datosReporte = $reporte->getDatos();
            // Y guardar estos datos o generar un archivo, etc.

            DB::commit();

            return redirect()->route('reportes.index') // Redirecciona a la lista de reportes
                             ->with('success', 'Reporte creado exitosamente y en proceso de generación.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Hubo un error al crear el reporte: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified report.
     */
    public function edit(Reporte $reporte)
    {
        // Solo permitir editar si el estado lo permite (ej. si no está 'archivado')
        if ($reporte->estado === 'archivado') {
            return redirect()->back()->with('error', 'No se puede editar un reporte archivado.');
        }

        return Inertia::render('Reporte/Edit', [
            'reporte' => $reporte,
        ]);
    }

    /**
     * Update the specified report in storage.
     */
    public function update(Request $request, Reporte $reporte)
    {
        $request->validate([
            'tipo' => ['required', 'string', 'in:ventas,compras,pedidos,inventario'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'filtros' => ['nullable', 'array'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'estado' => ['required', 'string', 'in:generado,archivado'],
        ]);

        try {
            DB::beginTransaction();

            $reporte->update([
                'tipo' => $request->tipo,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'filtros' => $request->filtros,
                'descripcion' => $request->descripcion,
                'estado' => $request->estado,
            ]);

            DB::commit();

            return redirect()->route('reportes.index') // Redirecciona a la lista de reportes
                             ->with('success', 'Reporte actualizado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Hubo un error al actualizar el reporte: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified report from storage.
     */
    public function destroy(Reporte $reporte)
    {
        try {
            $reporte->delete();
            return redirect()->route('reportes.index')
                             ->with('success', 'Reporte eliminado exitosamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Hubo un error al eliminar el reporte: ' . $e->getMessage()]);
        }
    }
}
