<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\EmailVerifiedNotification;
use App\Notifications\NewRegistrationNotification;
use App\Notifications\PasswordChangedNotification;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AccountEmailsTest extends TestCase
{
    use RefreshDatabase;

    private function register(): User
    {
        $this->post('/register', [
            'name' => 'Test User', 'email' => 'test@example.com', 'account_type' => 'private',
            'password' => 'password', 'password_confirmation' => 'password',
        ]);

        return User::where('email', 'test@example.com')->firstOrFail();
    }

    public function test_registration_sends_the_confirmation_email_and_notifies_the_owner(): void
    {
        Notification::fake();
        config(['mail.admin_address' => 'owner@example.com']);

        $user = $this->register();

        $this->assertFalse($user->hasVerifiedEmail());
        Notification::assertSentTo($user, VerifyEmailNotification::class);
        Notification::assertSentTo(new AnonymousNotifiable, NewRegistrationNotification::class,
            fn ($n, $channels, $notifiable) => $notifiable->routes['mail'] === 'owner@example.com');
    }

    public function test_owner_is_not_notified_when_no_admin_address_is_set(): void
    {
        Notification::fake();
        config(['mail.admin_address' => null]);

        $this->register();

        Notification::assertNotSentTo(new AnonymousNotifiable, NewRegistrationNotification::class);
    }

    public function test_confirming_the_email_sends_the_confirmed_email_once(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();
        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), ['id' => $user->id, 'hash' => sha1($user->email)]);

        $this->actingAs($user)->get($url)->assertSessionHas('success');
        $this->actingAs($user->fresh())->get($url);

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        Notification::assertSentToTimes($user, EmailVerifiedNotification::class, 1);
    }

    public function test_resetting_the_password_sends_the_password_changed_email(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
            $this->post('/reset-password', [
                'token' => $notification->token, 'email' => $user->email,
                'password' => 'new-password', 'password_confirmation' => 'new-password',
            ])->assertSessionHasNoErrors();

            return true;
        });

        Notification::assertSentTo($user, PasswordChangedNotification::class);
    }

    public function test_unconfirmed_users_cannot_post_listings_or_message_sellers(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
        $this->actingAs($user)->get(route('vehicles.create'))->assertRedirect(route('verification.notice'));
        $this->actingAs($user)->post(route('vehicles.store'), [])->assertRedirect(route('verification.notice'));
    }

    public function test_emails_render_in_macedonian_and_english(): void
    {
        $user = User::factory()->make(['name' => 'Марко']);

        foreach (['mk' => 'Потврди е-пошта', 'en' => 'Confirm email'] as $locale => $button) {
            app()->setLocale($locale);
            $html = (string) (new VerifyEmailNotification)->toMail($user->forceFill(['id' => 1]))->render();

            $this->assertStringContainsString($button, $html);
            $this->assertStringContainsString('Марко', $html);
            $this->assertStringContainsString('/verify-email/1/', $html);
        }
    }
}
