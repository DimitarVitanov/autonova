<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Conversation;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\NewMessageNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Tests\TestCase;

class MessageEmailsTest extends TestCase
{
    use RefreshDatabase;

    private User $seller;

    private User $buyer;

    private Vehicle $car;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seller = User::factory()->create(['name' => 'Петар']);
        $this->buyer = User::factory()->create(['name' => 'Марко']);

        $category = Category::create(['slug' => 'cars', 'name' => 'Car', 'name_plural' => 'Cars', 'sort' => 0]);
        $this->car = Vehicle::create([
            'user_id' => $this->seller->id, 'category_id' => $category->id,
            'title' => 'Audi A4', 'slug' => 'audi-a4', 'price' => 15000, 'year' => 2018, 'mileage_km' => 90000,
            'fuel' => 'diesel', 'transmission' => 'automatic', 'condition' => 'used', 'city' => 'Strumica',
            'seller_type' => 'private', 'status' => 'active',
        ]);
    }

    private function contactSeller(string $body = 'Is it still available?'): void
    {
        $this->actingAs($this->buyer)
            ->post(route('conversations.start', $this->car), ['body' => $body])
            ->assertSessionHas('success');
    }

    private function conversation(): Conversation
    {
        return Conversation::firstOrFail();
    }

    public function test_contacting_a_seller_emails_them_a_macedonian_inquiry(): void
    {
        Notification::fake();

        $this->withSession(['locale' => 'en'])->contactSeller();

        Notification::assertSentToTimes($this->seller, NewMessageNotification::class, 1);
        Notification::assertSentTo($this->seller, NewMessageNotification::class,
            fn ($notification, $channels, $notifiable, $locale) => $locale === 'mk');
        Notification::assertNotSentTo($this->buyer, NewMessageNotification::class);
    }

    public function test_follow_ups_are_emailed_again_only_after_the_recipient_reads_the_thread(): void
    {
        Notification::fake();

        $this->contactSeller();
        $this->actingAs($this->buyer)->post(route('conversations.message', $this->conversation()), ['body' => 'Hello?']);
        Notification::assertSentToTimes($this->seller, NewMessageNotification::class, 1);

        $this->actingAs($this->seller)->get(route('inbox', ['conversation' => $this->conversation()->id]))->assertOk();
        $this->actingAs($this->buyer)->post(route('conversations.message', $this->conversation()), ['body' => 'Any news?']);
        Notification::assertSentToTimes($this->seller, NewMessageNotification::class, 2);
    }

    public function test_a_reply_from_the_seller_emails_the_buyer(): void
    {
        Notification::fake();

        $this->contactSeller();
        $this->actingAs($this->seller)
            ->post(route('conversations.message', $this->conversation()), ['body' => 'Yes, come by tomorrow.'])
            ->assertSessionHas('success');

        Notification::assertSentTo($this->buyer, NewMessageNotification::class);
    }

    public function test_a_mail_outage_does_not_stop_the_message_from_being_sent(): void
    {
        Mail::extend('down', fn () => throw new RuntimeException('SMTP server unreachable'));
        config(['mail.mailers.down' => ['transport' => 'down'], 'mail.default' => 'down']);

        $this->contactSeller();

        $this->assertSame('Is it still available?', $this->conversation()->messages()->sole()->body);
    }

    public function test_the_inquiry_email_quotes_the_buyer_safely_and_links_to_the_inbox(): void
    {
        Notification::fake();
        $this->contactSeller('<script>alert(1)</script> Дали е достапно?');

        Notification::assertSentTo($this->seller, NewMessageNotification::class, function ($notification) {
            app()->setLocale('mk');
            $html = (string) $notification->toMail($this->seller)->render();

            $this->assertStringContainsString('Некој е заинтересиран за твоето возило', $html);
            $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt; Дали е достапно?', $html);
            $this->assertStringNotContainsString('<script>', $html);
            $this->assertStringContainsString('Audi A4', $html);
            $this->assertStringContainsString(route('inbox', ['conversation' => $this->conversation()->id]), $html);

            return true;
        });
    }
}
