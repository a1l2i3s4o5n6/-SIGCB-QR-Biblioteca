<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de Auditoría</title>
    <style>
        @page {
            margin: 18mm 14mm 16mm 14mm;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            color: #1f2937;
        }
        .header {
            border-bottom: 3px solid #14532d;
            padding-bottom: 8px;
            margin-bottom: 14px;
        }
        .brand {
            font-size: 20px;
            font-weight: bold;
            color: #14532d;
            letter-spacing: 1px;
        }
        .brand span {
            color: #b8860b;
        }
        .title {
            font-size: 14px;
            font-weight: bold;
            color: #14532d;
            margin-top: 4px;
        }
        .meta {
            margin-top: 8px;
            width: 100%;
            border-collapse: collapse;
        }
        .meta td {
            padding: 2px 0;
            color: #4b5563;
        }
        .meta .label {
            color: #9ca3af;
            width: 120px;
        }
        .total {
            margin: 10px 0 8px;
            text-align: right;
            font-weight: bold;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
        }
        table.data th {
            background: #14532d;
            color: #ffffff;
            text-align: left;
            padding: 6px 8px;
            font-size: 9px;
            text-transform: uppercase;
        }
        table.data td {
            padding: 5px 8px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }
        table.data tr:nth-child(even) {
            background: #f6faf7;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
        }
        .accion { background: #14532d; color: #ffffff; }
        .fecha { white-space: nowrap; color: #6b7280; }
        .empty {
            text-align: center;
            padding: 30px 0;
            color: #9ca3af;
        }
        .footer {
            position: fixed;
            bottom: -10mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 4px;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="brand">SIGCB-QR <span>|</span> Biblioteca</div>
        <div class="title">Reporte de Auditoría</div>
    </div>

    <table class="meta">
        <tr>
            <td class="label">Rango consultado</td>
            <td>{{ $desde }} — {{ $hasta }}</td>
        </tr>
        <tr>
            <td class="label">Total de actividades</td>
            <td>{{ number_format($total) }}</td>
        </tr>
        <tr>
            <td class="label">Generado por</td>
            <td>{{ $usuario }}</td>
        </tr>
        <tr>
            <td class="label">Fecha de generación</td>
            <td>{{ $generado }}</td>
        </tr>
    </table>

    @if (!empty($registros))
        <table class="data">
            <thead>
                <tr>
                    <th style="width:15%">Fecha y hora</th>
                    <th style="width:14%">Usuario</th>
                    <th style="width:12%">Acción</th>
                    <th style="width:14%">Entidad</th>
                    <th>Detalle</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($registros as $registro)
                    <tr>
                        <td class="fecha">{{ \Carbon\Carbon::parse($registro['createdAt'])->format('d/m/Y H:i') }}</td>
                        <td>{{ $registro['usuarioNombre'] ?? 'Sistema' }}</td>
                        <td><span class="badge accion">{{ $registro['accion'] ?? '—' }}</span></td>
                        <td>{{ $registro['entidad'] ?? '—' }}@isset($registro['entidadId']) #{{ $registro['entidadId'] }}@endisset</td>
                        <td>{{ $registro['detalle'] ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty">No hay registros de auditoría en el rango seleccionado.</div>
    @endif

    <div class="footer">SIGCB-QR · Sistema Integral de Gestión Básica - Reporte generado el {{ $generado }}</div>

</body>
</html>