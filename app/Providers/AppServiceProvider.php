<?php

namespace App\Providers;

use App\Notifications\EmailVerifiedNotification;
use App\Notifications\NewRegistrationNotification;
use App\Notifications\PasswordChangedNotification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Account emails. These are courtesy messages, so a mail outage is
        // reported but never breaks the request that triggered them.
        Event::listen(Verified::class, fn (Verified $e) => rescue(
            fn () => $e->user->notify(new EmailVerifiedNotification)
        ));

        Event::listen(PasswordReset::class, fn (PasswordReset $e) => rescue(
            fn () => $e->user->notify(new PasswordChangedNotification)
        ));

        Event::listen(Registered::class, function (Registered $e) {
            if ($admin = config('mail.admin_address')) {
                rescue(fn () => Notification::route('mail', $admin)->notify(new NewRegistrationNotification($e->user)));
            }
        });
    }
}
