<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ClapNotification extends Notification
{
    protected $msg;
    protected $user_id;
    protected $data;
    protected $title;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user_id, $title, $msg, $data)
    {
        $this->msg = $msg;
        $this->user_id = $user_id;
        $this->data = $data;
        $this->title = $title;
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

    public function toDatabase($notifiable)
    {
        return [
            "sender_id" => $this->user_id,
            "msg" => $this->msg,
            "title" => $this->title,
            "data" => $this->data
        ];
    }
}