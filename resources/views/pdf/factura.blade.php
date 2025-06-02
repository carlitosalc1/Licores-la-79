<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura #{{ $venta->id }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #000;
            width: 100%;
            margin: 0;
            padding: 5px;
        }

        .text-center {
            text-align: center;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        .footer {
            margin-top: 10px;
            text-align: center;
        }

        table {
            width: 100%;
        }

        td, th {
            padding: 2px 0;
        }

        .item-header {
            font-weight: bold;
            border-bottom: 1px dashed #000;
        }

        .total-final {
            font-weight: bold;
            text-align: right;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="text-center">
        <strong>Licores la 79</strong><br>
        <small>calle 79 # 3 bn 87 Esquina B /Floralia</small><br>
        Factura #{{ $venta->id }}<br>
        {{ $fecha_actual }}
    </div>

    <div class="line"></div>

    <div>
        <strong>Cliente:</strong> {{ $cliente->nombre }}<br>
        <strong>Tel:</strong> {{ $cliente->telefono }}<br>
        <strong>Dir:</strong> {{ $cliente->direccion }}<br>
        <strong>Pago:</strong> {{ ucfirst($venta->metodo_pago) }}<br>
        <strong>Cajero:</strong> {{ $cajero->name }}<br>
    </div>

    <div class="line"></div>

    <table>
        <thead>
            <tr class="item-header">
                <th>Producto</th>
                <th>Cant</th>
                <th>Precio</th>
                <th>Subt</th>
                <th>IVA</th> <th>Total Item</th> </tr>
        </thead>
        <tbody>
            @php
                $subtotalGeneral = 0; // Para el subtotal de todos los productos sin IVA
                $ivaGeneral = 0; // Para el total del IVA de todos los productos
                $totalGeneral = 0; // Para el total final de la venta (subtotal + IVA)
            @endphp
            @foreach ($detalles_venta as $detalle)
                @php
                    $subtotalItem = $detalle->cantidad * $detalle->precio_unitario;
                    $ivaItem = $subtotalItem * 0.19; // Calcula el IVA por item
                    $totalItem = $subtotalItem + $ivaItem; // Total por item (Subtotal + IVA)

                    $subtotalGeneral += $subtotalItem;
                    $ivaGeneral += $ivaItem;
                    $totalGeneral += $totalItem;
                @endphp
                <tr>
                    <td class="producto-col">{{ $detalle->producto->nombre }}</td>
                    <td>{{ number_format($detalle->cantidad, 0, ',', '.') }}</td> {{-- Formato de cantidad sin decimales --}}
                    <td> {{ number_format($detalle->precio_unitario, 0, ',', '.') }}</td> {{-- Formato de precio sin decimales --}}
                    <td> {{ number_format($subtotalItem, 0, ',', '.') }}</td> {{-- Formato de subtotal sin decimales --}}
                    <td> {{ number_format($ivaItem, 0, ',', '.') }}</td> {{-- Formato de IVA por item sin decimales --}}
                    <td> {{ number_format($totalItem, 0, ',', '.') }}</td> {{-- Formato de Total Item sin decimales --}}
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="line"></div>

    {{-- Totales detallados --}}
    <p class="total-final">Subtotal Venta: $ {{ number_format($subtotalGeneral, 0, ',', '.') }}</p>
    <p class="total-final">Total IVA: $ {{ number_format($ivaGeneral, 0, ',', '.') }}</p>
    <p class="total-final">Total a Pagar: $ {{ number_format($totalGeneral, 0, ',', '.') }}</p>

    <div class="footer">
        <p>Gracias por su compra</p>
        <p>
            @licoresla79
        </p>
    </div>
</body>
</html>
