<?php

namespace App\Mail;

use App\Models\CurrencySetting;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public CurrencySetting $currency;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Order $order,
        public string $paymentType = 'reservation'
    ) {
        $this->currency = CurrencySetting::getDefaultCurrency();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->paymentType === 'final'
            ? __('messages.email_final_payment_success')
            : __('messages.email_reservation_payment_success');

        return new Envelope(subject: $subject);
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.payment_success',
            text: 'emails.payment_success_text',
            with: [
                'order' => $this->order,
                'paymentType' => $this->paymentType,
                'currency' => $this->currency,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
