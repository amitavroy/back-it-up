<?php

namespace App\Mail;

use App\Records;
use App\Services\BackupData;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class DBBackupCompleted extends Mailable
{
    use Queueable, SerializesModels;
    /**
     * @var BackupData
     */
    private $record;

    /**
     * Create a new message instance.
     *
     * @param Records $record
     * @internal param BackupData $backupData
     */
    public function __construct(Records $record)
    {
        $this->record = $record;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subjectEmail = config('backitup.mail-subject');

        $lastRecord = Records::where('id', '!=', $this->record->id)
            ->orderBy('created_at', 'desc')
            ->first();

        // when this is the first record. Backup running for the first time
        // compare with itself
        if (!$lastRecord) {
            $lastRecord = $this->record;
        }

        return $this->view('mails.db-completed')
            ->subject($subjectEmail)
            ->with('lastRecord', $lastRecord)
            ->with('data', $this->record);
    }
}
