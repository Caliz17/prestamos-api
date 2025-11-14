<x-mail::message>
# 🔐 Restablecer contraseña

Hola, recibimos una solicitud para restablecer tu contraseña.  
Haz clic en el botón para continuar:

<x-mail::button :url="$actionUrl" color="primary">
Restablecer contraseña
</x-mail::button>

Si no realizaste esta solicitud, ignora este mensaje.

Gracias,<br>
{{ config('app.name') }}

<x-slot:subcopy>
Si no funciona el botón, copia y pega este enlace en tu navegador:<br>
<span class="break-all">{{ $displayableActionUrl }}</span>
</x-slot:subcopy>

</x-mail::message>
