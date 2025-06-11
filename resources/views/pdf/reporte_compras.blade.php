<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Compras</title>
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
    <h1>Reporte de Compras</h1>
    @if($request->proveedor_id)
        <p><strong>Proveedor:</strong> {{ $proveedores->firstWhere('id', $request->proveedor_id)->nombre ?? 'N/A' }}</p>
    @endif
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
                <th>Proveedor</th>
                <th>Fecha Compra</th>
                <th>Productos</th>
                <th>Total</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($compras as $compra)
            <tr>
                <td>{{ $compra->id }}</td>
                <td>{{ $compra->proveedor->nombre ?? 'N/A' }}</td>
                <td>{{ $compra->fecha_compra->format('d/m/Y H:i') }}</td>
                <td>
                    <ul>
                        @foreach($compra->detalleCompras as $detalle)
                            <li>{{ $detalle->producto->nombre }} ({{ $detalle->cantidad }} x {{ $detalle->precio_unitario }})</li>
                        @endforeach
                    </ul>
                </td>
                <td>${{ number_format($compra->total_compra, 2) }}</td>
                <td>{{ ucfirst($compra->estado) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <h2>Total General de Compras: ${{ number_format($totalCompras, 2) }}</h2>
    <div class="footer">
        Generado el: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
