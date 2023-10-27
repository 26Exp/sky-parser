<?php

namespace App\Notifications;

use App\Models\Ad;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;


class NewAdParsed extends Notification
{
    use Queueable;

    public function __construct(Ad $ad)
    {
        $this->ad = $ad;
    }
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function via(object $notifiable): array
    {
        return ['telegram'];
    }

    public function toTelegram($notifiable)
    {
        $url = 'https://999.md/' . $this->ad->link;

        return TelegramMessage::create()
            ->content(" *". $this->ad->title. "*\n\n")
            ->line("*Pret:* ". $this->ad->price)
            ->line("*Pret pe m2:* ". $this->ad->per_m2)
            ->button('Deschide anunț-ul', $url);
    }
}
