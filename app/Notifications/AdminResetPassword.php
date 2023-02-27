<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminResetPassword extends Notification
{
    use Queueable;

    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->greeting('Hello '.$this->name)
            ->line('Please reset your password by clicking below link!')
            ->action('Reset Password', route('admin.set-password-form', $this->id))
            ->line('Thank you for using our application!');
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
