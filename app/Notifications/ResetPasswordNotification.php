<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = url('http://localhost:8001/reset-password/' . $this->token) . '?email=' . urlencode($notifiable->getEmailForPasswordReset());

        return (new MailMessage)
            ->subject('Restablecer Contraseña')
            ->line('Estás recibiendo este email porque recibimos una solicitud de restablecimiento de contraseña para tu cuenta.')
            ->action('Restablecer Contraseña', $url)
            ->line('Este enlace de restablecimiento de contraseña expirará en '.config('auth.passwords.'.config('auth.defaults.passwords').'.expire').' minutos.')
            ->line('Si no solicitaste un restablecimiento de contraseña, no se requiere ninguna acción adicional.');
    }
}