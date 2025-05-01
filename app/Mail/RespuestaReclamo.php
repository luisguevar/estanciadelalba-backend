<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RespuestaReclamo extends Mailable
{
    use Queueable, SerializesModels;
    public $reclamo;
    public $pdfContent;

    /**
     * Create a new message instance.
     */
    public function __construct($reclamo, $pdfContent)
    {
        $this->reclamo = $reclamo;
        $this->pdfContent = $pdfContent;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'RESPUESTA A RECLAMO N° ' . $this->reclamo->cNroHoja
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.reclamo.respuesta-diseño',
            with: ['reclamo' => $this->reclamo],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            \Illuminate\Mail\Mailables\Attachment::fromData(fn() => $this->pdfContent, 'hoja_reclamacion.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
