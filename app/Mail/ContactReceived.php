<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactReceived extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $messageContent;

    public function __construct(string $name, string $message)
    {
        $this->name = $name;
        $this->messageContent = $message;
    }

    public function build()
    {
        return $this
            ->subject("Gracias por tu mensaje, {$this->name}")
            ->markdown('emails.contact.received')
            ->with([
                'name'    => $this->name,
                'body' => $this->messageContent,
            ]);
    }
}
