<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class SendTickets extends Mailable
{
    use Queueable, SerializesModels;

    public $payment;
    public $event;
    public $tickets;
    public $accesses;

    /**
     * Create a new message instance.
     */
    public function __construct($payment, $event, $tickets, $accesses)
    {
        $this->payment  = $payment;
        $this->event    = $event;
        $this->tickets  = $tickets;
        $this->accesses = $accesses;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Boletos para el evento '.$this->event->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.sendTickets',
            with: [
                'payment' => $this->payment,
                'event'   => $this->event,
                'tickets' => $this->tickets,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $adjuntos = [];

        foreach ($this->accesses as $path => $a) {
            // $adjuntos[] = Attachment::fromPath($path)
            $adjuntos[] = Attachment::fromPath('events/pdf/'.$this->event->id.'/'.$a->folio.'.pdf')
            ->as($a->ticket->name.'_'.$a->name.'.pdf')
            ->withMime('application/pdf'); // opcional
        }

        return $adjuntos;
    }
}
