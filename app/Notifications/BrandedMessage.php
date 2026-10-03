<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

/** Builds a mail message on the shared AutoNova email template. */
class BrandedMessage
{
    /**
     * @param  array{title:string, lines:array<int,string>, kicker?:string, greeting?:string, preheader?:string,
     *     details?:array<string,string>, actionText?:string, actionUrl?:string, outro?:array<int,string>}  $data
     */
    public static function make(string $subject, array $data): MailMessage
    {
        return (new MailMessage)
            ->subject($subject)
            ->view(['emails.branded', 'emails.branded-text'], $data);
    }
}
