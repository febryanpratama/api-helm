<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewChatGroup extends Mailable
{
    use Queueable, SerializesModels;

    // Initialize
    public $user;
    public $message;
    public $userSender;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $message, $userSender)
    {
        // Initialize
        $this->user         = $user;
        $this->message      = $message;
        $this->userSender   = $userSender;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.new_chat_group', [
                        'user'      => $this->user,
                        'messages'  => $this->message,
                        'sender'    => $this->userSender
                    ])
                    ->subject('Obrolan Grup Baru')
                    ->from(env('MAIL_FROM'));
    }
}
