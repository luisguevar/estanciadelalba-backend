@component('mail::message')
# Estimado/a {{ $reclamo->cNombreReclamante }},

Adjunto encontrará la hoja de reclamación correspondiente al registro con código **N° {{ $reclamo->cNroHoja }}**.

Gracias por comunicarse con nosotros.

Saludos cordiales,  
**{{ config('app.name') }}**
@endcomponent
