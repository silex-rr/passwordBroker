<?php

namespace System\Application\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use System\Domain\Backup\Models\Backup;

class BackupEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(private readonly Backup $backup)
    {
        $this->subject('Successful Backup Creation for ' . env('APP_NAME'));
    }
    public function content(): Content
    {
        $content = new Content(view: 'system.mails.backup');
        $content->with('backup', $this->backup);
        return $content;
    }

    public function attachments(): array
    {
        return [
            Attachment::fromStorageDisk('system_backup', $this->backup->file_name->getNativeValue())
                ->as($this->backup->file_name->getNativeValue())
                ->withMime('application/zip'),
        ];
    }
}
