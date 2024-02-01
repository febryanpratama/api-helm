<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\Channel;
use Illuminate\Support\Carbon;

class GroupChat extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    protected $receiver_id, $sender, $group, $message;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($receiver_id, $sender, $group)
    {
        $this->group = $group;
        $this->receiver_id = $receiver_id;
        $this->sender = $sender;
        $this->message = "menambahkan anda kedalam grup";
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
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
            'receiver_id' => $this->receiver_id,
            'sender' => $this->sender,
            'group' => $this->group,
            'message' => $this->message,
        ];
    }

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
                'receiver_id' => $this->receiver_id,
                'sender' => $this->sender,
                'group' => $this->group,
                'message' => $this->message,
            ],
            'created_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d h:m:s'),
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('user.notif.' . $this->receiver_id);
    }
}
