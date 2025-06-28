<?php

// app/Notifications/MyCustomVerifyEmail.php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;

use Illuminate\Notifications\Messages\MailMessage;

// Si quieres usar colas, implementa ShouldQueue y usa el trait Queueable
class MyCustomVerifyEmail extends BaseVerifyEmail // implements ShouldQueue
{
    // use Queueable;

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        // Puedes acceder a propiedades adicionales del usuario ($notifiable)
        // Por ejemplo, para personalizar el saludo con el nombre del usuario:
        $userName = $notifiable->name ?? 'Usuario';

        // Puedes personalizar el asunto, la línea de saludo, etc.
        return (new MailMessage)
                    ->subject('¡Verifica tu cuenta en nuestra aplicación!')
                    ->greeting('Hola ' . $userName . ',')
                    ->line('Por favor, haz clic en el botón de abajo para verificar tu dirección de correo electrónico.')
                    ->action('Verificar Dirección de Correo Electrónico', $this->verificationUrl($notifiable))
                    ->salutation('Saludos, El equipo de ChinaGo');

        // Si quieres personalizar completamente la vista de Blade del correo:
        /*
        return (new MailMessage)
                    ->subject('¡Verifica tu cuenta!')
                    ->markdown('emails.verify-email', ['url' => $this->verificationUrl($notifiable), 'user' => $notifiable]);
        */
    }

    /**
     * Puedes sobrescribir el método via() para definir cómo se envía la notificación (por ejemplo, a una cola).
     *
     * @param  mixed  $notifiable
     * @return array
     */
    /*
    public function via($notifiable)
    {
        return ['mail']; // O ['mail', 'database'] si también quieres guardarla en la DB
    }
    */
}