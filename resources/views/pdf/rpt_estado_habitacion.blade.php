<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte PDF</title>
    <style>
        /* Estilos para la página en formato A4 */
        @page {
            size: A4;
            margin: 20mm;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        h3 {
            text-align: center;
            margin-bottom: 20px;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .header img {
            height: 50px;
            /* Ajusta el tamaño del logo */
        }

        .header .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        .table th,
        .table td {
            font-size: 10px;
        }

        .fixed-column {
            width: 10%;
        }

        .align-middle {
            vertical-align: middle;
        }

        .text-gray-500 {
            color: #6c757d;
        }

        .text-gray-600 {
            color: #495057;
        }

        .footer {
            font-size: 10px;
            text-align: center;
            margin-top: 20px;
            color: #6c757d;
        }

        .generated-by {
            font-size: 10px;
            color: #6c757d;
            text-align: right;
            margin-top: 10px;
        }

        .parameters-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .parameters-table td {
            padding: 5px 10px;
        }

        .parameters-table th {
            padding: 5px 10px;
            text-align: left;
            font-weight: bold;
        }

        .parameters-table .label {
            width: 25%;
            font-weight: normal;
            color: #495057;
            font: bold;
        }

        .parameters-table .value {
            width: 25%;
            font-size: 10px
        }
    </style>
</head>

<body>
    <div class="header">
        <!-- Logo de la empresa -->
        {{--    <img src="logo_empresa.png" alt="Logo de la Empresa"> --}}
        <img src="assets/images/logo_hotel.png" alt="feature icon">
        {{--  <div class="company-name">Nombre de la Empresa</div> --}}
    </div>

    <h3>Reporte de Estado de Habitaciones

      

    </h3>

    <table class="parameters-table">
        <tr>
            <th class="label">Estado Habitación: </th>
            <td class="value">{{ $estadoHabitacion }}</td>
            <th class="label">Nivel:</th>
            <td class="value">{{ $nivel }}</td>
        </tr>
        <tr>
            <th class="label">Desde:</th>
            <td class="value">{{ $desde }}</td>
            <th class="label">Hasta:</th>
            <td class="value">{{ $hasta }}</td>
        </tr>

    </table>

    <!-- Mostrar los registros en la tabla -->
    @if (count($registros) > 0)
        <table class="table align-middle table-row-dashed fs-6 gy-5 w-100" id="kt_customers_table">
            <colgroup>
                <col style="width: 4%;">
                <col style="width: 20%;">
                <col style="width: 16%;">
                <col style="width: 20%;">
                <col style="width: 20%;">
                <col style="width: 20%;">
               
            </colgroup>
            <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th>N°</th>
                    <th>NIVEL</th>
                    <th>HABITACIÓN</th>
                    <th>TIPO HABITACIÓN</th>
                    <th>ESTADO</th>
                    <th>ÚLTIMA ACTUALIZACIÓN</th>

                </tr>
            </thead>
            <tbody class="fw-semibold text-gray-600">
                @foreach ($registros as $index => $registro)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $registro['cNivelHabitacion'] }}</td>
                        <td>{{ $registro['nNroHabitacion'] }}</td>
                        <td>{{ $registro['cTipoHabitacion'] }}
                        <td>{{ $registro['cEstadoLimpieza'] }}</td>
                        <td>{{ \Carbon\Carbon::parse($registro['dFechaActualizacion'])->format('d/m/Y H:i:s') }}</td>

                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No hay registros disponibles.</p>
    @endif

    <!-- Información adicional fuera de la tabla -->
    <div class="generated-by">
        <p>Generado por {{ $cUsuarioResponsable }}. El {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}.</p>
        <!-- Aquí se pasa la variable del controlador -->
    </div>
</body>

</html>
