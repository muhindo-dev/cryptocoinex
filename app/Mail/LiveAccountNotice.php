<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * A single, flexible Live Account email. Rendered through the shared
 * emails.live.notice template so deposit/withdrawal messages stay consistent.
 * Queued so SMTP latency never blocks the request that triggers it.
 */
class LiveAccountNotice extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string,string>  $rows
     */
    public function __construct(
        public string $subjectLine,
        public string $heading,
        public string $intro,
        public array $rows = [],
        public ?string $ctaUrl = null,
        public ?string $ctaLabel = null,
        public ?string $accent = null,
        public ?string $noteTitle = null,
        public ?string $noteBody = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.live.notice', with: [
            'heading' => $this->heading,
            'intro' => $this->intro,
            'rows' => $this->rows,
            'ctaUrl' => $this->ctaUrl,
            'ctaLabel' => $this->ctaLabel,
            'accent' => $this->accent,
            'noteTitle' => $this->noteTitle,
            'noteBody' => $this->noteBody,
        ]);
    }
}
