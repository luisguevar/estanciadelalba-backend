<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Hoja de Reclamación</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td, th {
            border: 1px solid black;
            padding: 4px;
            vertical-align: top;
        }
        .title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
        }
        .section-title {
            background-color: #eaeaea;
            font-weight: bold;
        }
        .no-border {
            border: none;
        }
        .center {
            text-align: center;
        }
        .signature-space {
            height: 50px;
        }
    </style>
</head>
<body>

    <table>
        <tr>
            <td colspan="6" class="title" style="background-color: #eaeaea">LIBRO DE RECLAMACIONES</td>
            <td colspan="2" class="title">HOJA DE RECLAMACIÓN</td>
        </tr>
        <tr>
            <td colspan="1">FECHA:</td>
            <td colspan="1">{{$reclamo->created_at->day}}</td>
            <td colspan="2">{{$reclamo->created_at->month}}</td>
            <td colspan="2">{{$reclamo->created_at->year}}</td>
            <td colspan="2">N° {{$reclamo->cNroHoja}}</td>
        </tr>
        <tr>
            <td colspan="8">
                <strong>RAZÓN SOCIAL: </strong>ESTANCIA DEL ALBA HOTEL S.A.C /
                <strong>RUC: </strong>20610803645
                <br>
                <strong>DIRECCIÓN: </strong>AV. VÍA DE EVITAMIENTO SUR 1260, CAJAMARCA
            </td>
        </tr>
        <tr>
            <td colspan="8" class="section-title">1. IDENTIFICACIÓN DEL CONSUMIDOR RECLAMANTE</td>
        </tr>
        <tr>
            <td colspan="8"><strong>NOMBRE:</strong> {{$reclamo->cNombreReclamante}}</td>
        </tr>
        <tr>
            <td colspan="8"><strong>DOMICILIO:</strong> {{$reclamo->cDomicilioReclamante}}</td>
        </tr>
        <tr>
            <td colspan="1">
                <strong>
                    {{ 
                        $reclamo->nTipoDocumentoReclamante == '1' ? 'DNI' : 
                        ($reclamo->nTipoDocumentoReclamante == '2' ? 'CE' : 
                        ($reclamo->nTipoDocumentoReclamante == '3' ? 'RUC' : 'PASAPORTE'))
                    }}:                    
                </strong> 
                {{$reclamo->cNroDocumentoReclamante}}
            </td>
            <td colspan="3"><strong>TELÉFONO:</strong> {{$reclamo->cTelefonoReclamante}}</td>
            <td colspan="4"><strong>E-MAIL:</strong> {{$reclamo->cEmailReclamante}}</td>
        </tr>
        <tr>
            <td colspan="8"><strong>PADRE O MADRE:</strong> {{$reclamo->cApoderadoReclamante??''}}</td>
        </tr>
        <tr>
            <td colspan="8" class="section-title">2. IDENTIFICACIÓN DEL BIEN CONTRATADO</td>
        </tr>
        <tr>
            <td colspan="1" style="padding: 0">
                <table style="border: 1.5px solid white">
                    <tr>
                        <td>PRODUCTO</td>
                        <td>{{$reclamo->nTipoBien=='0'?'X':''}}</td>
                    </tr>
                    <tr>
                        <td>SERVICIO</td>
                        <td>{{$reclamo->nTipoBien=='1'?'X':''}}</td>
                    </tr>
                </table>
            </td>
            <td colspan="7">
                <strong>MONTO RECLAMADO:</strong>  {{$reclamo->nMoneda=='0'?'S/.':'$.'}} {{$reclamo->nMontoReclamadoBien}}<br>
                <strong>DESCRIPCIÓN:</strong> {{$reclamo->cDescripcionBien}}
            </td>
          
        </tr>
        <tr>
            <td colspan="2" class="section-title">3. DETALLE DE LA RECLAMACIÓN Y PEDIDO DEL CONSUMIDOR</td>
            <td colspan="3" class="center"><strong>RECLAMO</strong><sup>1</sup></td>
            <td colspan="1" class="center">{{$reclamo->nTipoDisconformidad=='1'?'X':''}}</td>
            <td colspan="1" class="center"><strong>QUEJA</strong><sup>2</sup></td>
            <td colspan="1" class="center">{{$reclamo->nTipoDisconformidad=='0'?'X':''}}</td>
        </tr>
        <tr>
            <td colspan="8"><strong>DETALLE:</strong> {{$reclamo->cDetalleDisconformidad}}<div class="signature-space"></div></td>
        </tr>
        <tr>
            <td colspan="6"><strong>PEDIDO:</strong> {{$reclamo->cPedidoDisconformidad}} <div class="signature-space"></div></td>
            <td colspan="2" class="center">
                <br>
                Firmado electrónicamente por {{$reclamo->cNombreReclamante}} el {{$reclamo->created_at->format('d/m/Y H:i:s')}}
                <div class="signature-line"></div><br>
                <strong>FIRMA DEL CONSUMIDOR</strong>
            </td>
        </tr>
        @if($reclamo->dFechaRespuestaProveedor && $reclamo->cRespuestaProveedor)
            <tr>
                <td colspan="8" class="section-title">4. OBSERVACIONES Y ACCIONES ADOPTADAS POR EL PROVEEDOR</td>
            </tr>
            <tr>
                <td colspan="3"><strong>FECHA DE COMUNICACIÓN DE LA RESPUESTA:</strong></td>
                <td colspan="1">{{$reclamo->dFechaRespuestaProveedor->day}}</td>
                <td colspan="1">{{$reclamo->dFechaRespuestaProveedor->month}}</td>
                <td colspan="1">{{$reclamo->dFechaRespuestaProveedor->year}}</td>
                <td colspan="2" style="border-bottom: 1px solid white;"></td>
            </tr>
            <tr>
                <td colspan="6" style="height: 80px;">{{$reclamo->cRespuestaProveedor}}</td>
                <td colspan="2" class="center">
                    Firmado electrónicamente por Estancia del Alba el {{$reclamo->dFechaRespuestaProveedor->format('d/m/Y H:i:s')}}
                    <div class="signature-line"></div><br>
                    <strong>FIRMA DEL PROVEEDOR</strong>
                </td>
            </tr>
        @endif
        <tr>
            <td colspan="8" class="no-border" style="font-size: 10px;">
                <sup>1</sup> <strong>RECLAMO:</strong> Disconformidad relacionada a los productos o servicios.<br>
                <sup>2</sup> <strong>QUEJA:</strong> Disconformidad no relacionada a los productos o servicios; o, malestar o descontento respecto a la atención al público.
            </td>
        </tr>
    </table>

</body>
</html>
