<?php

namespace App\Mail;

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
    private $backupData;

    /**
     * Create a new message instance.
     *
     * @param BackupData $backupData
     */
    public function __construct(BackupData $backupData)
    {
        $this->backupData = $backupData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $data = [
            'sqlFileSize' => $this->backupData->getSqlFileSize(),
            'gzipFileSize' => $this->backupData->getGzipFileSize(),
            'url' => $this->backupData->getUrl(),
        ];
        return $this->view('mails.db-completed')->with('data', $data);
    }
}
