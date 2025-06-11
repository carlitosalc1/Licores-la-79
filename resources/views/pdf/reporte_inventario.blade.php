<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Inventario Actual</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        h1, h2 { text-align: center; }
        .footer { text-align: right; font-size: 0.8em; margin-top: 20px; }
    </style>
</head>
<body>
    <h1>Reporte de Inventario Actual</h1>
    @if($request->boolean('stock_bajo'))
        <p><strong>Filtrado por:</strong> Productos con stock bajo.</p>
    @endif
    @if($request->filled('nombre'))
        <p><strong>Filtrado por nombre:</strong> "{{ $request->nombre }}"</p>
    @endif
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Código</th>
                <th>Categoría</th>
                <th>Stock</th>
                <th>Stock Mínimo</th>
                <th>Precio Venta</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productos as $producto)
            <tr>
                <td>{{ $producto->id }}</td>
                <td>{{ $producto->nombre }}</td>
                <td>{{ $producto->codigo }}</td>
                <td>{{ $producto->categoriaProducto->nombre ?? 'N/A' }}</td>
                <td style="{{ $producto->stock <= $producto->stock_minimo ? 'color: red; font-weight: bold;' : '' }}">{{ $producto->stock }}</td>
                <td>{{ $producto->stock_minimo }}</td>
                <td>${{ number_format($producto->precio_venta, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">
        Generado el: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
