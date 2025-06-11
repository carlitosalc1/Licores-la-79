<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Ventas</title>
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
    <h1>Reporte de Ventas</h1>
    @if($request->fecha_inicio || $request->fecha_fin)
        <p><strong>Periodo:</strong>
            @if($request->fecha_inicio) Desde {{ \Carbon\Carbon::parse($request->fecha_inicio)->format('d/m/Y') }} @endif
            @if($request->fecha_fin) Hasta {{ \Carbon\Carbon::parse($request->fecha_fin)->format('d/m/Y') }} @endif
        </p>
    @endif
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Fecha</th>
                <th>Productos</th>
                <th>Total</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ventas as $venta)
            <tr>
                <td>{{ $venta->id }}</td>
                <td>{{ $venta->cliente ? $venta->cliente->nombre . ' ' . $venta->cliente->apellido : 'Consumidor Final' }}</td>
                <td>{{ $venta->fecha_venta->format('d/m/Y H:i') }}</td>
                <td>
                    <ul>
                        @foreach($venta->detalleVentas as $detalle)
                            <li>{{ $detalle->producto->nombre }} ({{ $detalle->cantidad }} x {{ $detalle->precio_unitario }})</li>
                        @endforeach
                    </ul>
                </td>
                <td>${{ number_format($venta->total_venta, 2) }}</td>
                <td>{{ ucfirst($venta->estado) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <h2>Total General de Ventas: ${{ number_format($totalVentas, 2) }}</h2>
    <div class="footer">
        Generado el: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
