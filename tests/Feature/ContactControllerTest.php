<?php

namespace Tests\Feature;

use App\Mail\ContactFormMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    #[Test]
    public function contact_page_is_accessible(): void
    {
        $response = $this->get(route('contact'));

        $response->assertStatus(200);
        $response->assertViewIs('contact');
    }

    #[Test]
    public function contact_page_contains_form(): void
    {
        $response = $this->get(route('contact'));

        $response->assertStatus(200);
        $response->assertSee('name');
        $response->assertSee('email');
        $response->assertSee('message');
    }

    #[Test]
    public function it_sends_contact_form_successfully(): void
    {
        $response = $this->post(route('contact.send'), [
            'name' => 'Jan Kowalski',
            'email' => 'jan@gmail.com',
            'message' => 'Test message content here.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Mail::assertQueued(ContactFormMail::class, function ($mail) {
            return $mail->hasTo('contact@carshop.pl');
        });
    }

    #[Test]
    public function it_fails_with_missing_name(): void
    {
        $response = $this->from(route('contact'))
            ->post(route('contact.send'), [
                'email' => 'jan@gmail.com',
                'message' => 'Test message',
            ]);

        $response->assertRedirect(route('contact'));
        $response->assertSessionHasErrors(['name']);

        Mail::assertNothingSent();
    }

    #[Test]
    public function it_fails_with_missing_email(): void
    {
        $response = $this->from(route('contact'))
            ->post(route('contact.send'), [
                'name' => 'Jan Kowalski',
                'message' => 'Test message',
            ]);

        $response->assertRedirect(route('contact'));
        $response->assertSessionHasErrors(['email']);

        Mail::assertNothingSent();
    }

    #[Test]
    public function it_fails_with_invalid_email_format(): void
    {
        $response = $this->from(route('contact'))
            ->post(route('contact.send'), [
                'name' => 'Jan Kowalski',
                'email' => 'invalid-email',
                'message' => 'Test message',
            ]);

        $response->assertRedirect(route('contact'));
        $response->assertSessionHasErrors(['email']);

        Mail::assertNothingSent();
    }

    #[Test]
    public function it_fails_with_missing_message(): void
    {
        $response = $this->from(route('contact'))
            ->post(route('contact.send'), [
                'name' => 'Jan Kowalski',
                'email' => 'jan@gmail.com',
            ]);

        $response->assertRedirect(route('contact'));
        $response->assertSessionHasErrors(['message']);

        Mail::assertNothingSent();
    }

    #[Test]
    public function it_fails_with_too_long_message(): void
    {
        $response = $this->from(route('contact'))
            ->post(route('contact.send'), [
                'name' => 'Jan Kowalski',
                'email' => 'jan@gmail.com',
                'message' => str_repeat('a', 2001),
            ]);

        $response->assertRedirect(route('contact'));
        $response->assertSessionHasErrors(['message']);

        Mail::assertNothingSent();
    }

    #[Test]
    public function it_fails_with_too_long_name(): void
    {
        $response = $this->from(route('contact'))
            ->post(route('contact.send'), [
                'name' => str_repeat('a', 256),
                'email' => 'jan@gmail.com',
                'message' => 'Test message',
            ]);

        $response->assertRedirect(route('contact'));
        $response->assertSessionHasErrors(['name']);

        Mail::assertNothingSent();
    }

    #[Test]
    public function contact_send_is_throttled(): void
    {
        $contactData = [
            'name' => 'Jan Kowalski',
            'email' => 'jan@gmail.com',
            'message' => 'Test message',
        ];

        for ($i = 0; $i < 6; $i++) {
            $response = $this->post(route('contact.send'), $contactData);
        }

        $response->assertStatus(429);
    }
}
