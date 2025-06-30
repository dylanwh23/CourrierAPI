<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactReceived extends Mailable
{
    use Queueable, SerializesModels;

    public string $name;
    public string $messageContent;
    public ?string $link;

    /**
     * Crear una nueva instancia del mensaje.
     */
    public function __construct(string $name, string $message, ?string $link = null)
    {
        $this->name = $name;
        $this->messageContent = $message;
        $this->link = $link;
    }

    /**
     * Construir el mensaje.
     */
    public function build()
    {
        return $this
            ->from('no.reply.chinago@gmail.com', 'GoChina Soporte')
            ->subject("Gracias por tu mensaje, {$this->name}")
            ->markdown('emails.contact.received')
            ->with([
                'name' => $this->name,
                'body' => $this->messageContent,
                'link' => $this->link,
            ]);
    }
}