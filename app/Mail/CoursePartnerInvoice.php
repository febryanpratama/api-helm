<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CoursePartnerInvoice extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $course;
    public $checkout;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $course, $checkout)
    {
        // Initialize
        $this->user     = $user;
        $this->course   = $course;
        $this->checkout = $checkout;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.partner-invoice', ['user' => $this->user, 'course' => $this->course, 'checkout' => $this->checkout]);
    }
}
