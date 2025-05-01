<?php

namespace App\Mail;

use App\Models\Habitacion;
use App\Models\ReservaHabitacionWeb;
use App\Models\ReservaWeb;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PreReservaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $dataReserva;
    public $lstHabitaciones;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $dataReserva, $lstHabitaciones)
    {
        $this->user = $user;
        $this->dataReserva = $dataReserva;
        $this->lstHabitaciones = $lstHabitaciones;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Se ha registrado tu reserva',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $user =  $this->user;
        $dataReserva =  $this->dataReserva;
        $lstHabitaciones =  $this->lstHabitaciones;

       


        return new Content(
            view: 'mail.prereserva'
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
