<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DonePaymentWithBalance extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $courseTransaction;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $courseTransaction)
    {
        // Initialize
        $this->user              = $user;
        $this->courseTransaction = $courseTransaction;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.done_payment_with_balance', ['user' => $this->user, 'courseTransaction' => $this->courseTransaction])
                    ->subject('Paket Kursus Telah Aktif')
                    ->from(env('MAIL_FROM'));
    }
}
