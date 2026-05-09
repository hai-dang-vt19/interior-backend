<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerRegistrationVerifyMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  string  $verifyUrl URL đầy đủ chứa token xác thực (HTTPS khuyến nghị)
     */
    public function __construct(
        public Customer $customer,
        public string $verifyUrl
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Xác thực đăng ký tài khoản — '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.customer-registration-verify',
        );
    }

    /** @return array<int, \Illuminate\Mail\Mailables\Attachment> */
    public function attachments(): array
    {
        return [];
    }
}
