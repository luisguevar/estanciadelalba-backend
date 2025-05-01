@component('mail::message')
# Confirmación de Reclamo

Hola **{{ $reclamo->cNombreReclamante }}**,

Hemos recibido tu reclamo con éxito.  
Código: **N° {{ $reclamo->cNroHoja }}**<br>
Tipo disconformidad: **{{ $reclamo->nTipoDisconformidad=='0'?'Queja':'Reclamo' }}**<br>
Detalle: **{{ $reclamo->cDetalleDisconformidad }}**<br>
Monto en disconformidad: **{{ $reclamo->nMontoReclamadoBien }}**<br>
Pedido: **{{ $reclamo->cPedidoDisconformidad }}**<br>

Nos pondremos en contacto contigo a la brevedad.

Gracias,  
{{ config('app.name') }}
@endcomponent
