<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GlobalNotification extends Notification
{
    use Queueable;

    protected $receiver_id, $sender, $title, $code, $message, $data, $icon;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($receiver_id, $sender, $title, $code, $message, $data, $icon)
    {
        // Initialize
        $this->receiver_id  = $receiver_id;
        $this->sender       = $sender;
        $this->title        = $title;
        $this->code         = $code;
        $this->message      = $message;
        $this->data         = $data;
        $this->icon         = $icon;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the database representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toDatabase($notifiable)
    {
        return [
            'receiver_id'   => $this->receiver_id,
            'sender'        => $this->sender,
            'title'         => $this->title,
            'code'          => $this->code,
            'message'       => $this->message,
            'data'          => $this->data,
            'icon'          => $this->icon
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    // public function toMail($notifiable)
    // {
    //     return (new MailMessage)
    //                 ->line('The introduction to the notification.')
    //                 ->action('Notification Action', url('/'))
    //                 ->line('Thank you for using our application!');
    // }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'data' => [
                'receiver_id'   => $this->receiver_id,
                'sender'        => $this->sender,
                'title'         => $this->title,
                'code'          => $this->code,
                'message'       => $this->message,
                'data'          => $this->data,
                'icon'          => $this->icon
            ],
            'created_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d h:m:s'),
        ];
    }
}
