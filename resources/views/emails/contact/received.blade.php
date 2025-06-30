@component('mail::message')
# Hola {{ $name }}

Gracias por escribirnos. Hemos recibido tu mensaje correctamente:

{{ $body }}

@if(isset($link))
@component('mail::button', ['url' => $link])
Ir al chat de soporte
@endcomponent
@endif


Gracias por confiar en nosotros,<br>
{{ config('app.name') }}
@endcomponent