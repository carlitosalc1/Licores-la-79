<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Productos Más Vendidos</title>
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
    <h1>Reporte de Productos Más Vendidos</h1>
    @if($request->fecha_inicio || $request->fecha_fin)
        <p><strong>Periodo:</strong>
            @if($request->fecha_inicio) Desde {{ \Carbon\Carbon::parse($request->fecha_inicio)->format('d/m/Y') }} @endif
            @if($request->fecha_fin) Hasta {{ \Carbon\Carbon::parse($request->fecha_fin)->format('d/m/Y') }} @endif
        </p>
    @endif
    <p><strong>Límite:</strong> {{ $request->limite ?? '10' }} productos</p>
    <table>
        <thead>
            <tr>
                <th>Nombre Producto</th>
                <th>Código</th>
                <th>Cantidad Vendida</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productos as $producto)
            <tr>
                <td>{{ $producto->nombre }}</td>
                <td>{{ $producto->codigo }}</td>
                <td>{{ $producto->total_vendido }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">
        Generado el: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
