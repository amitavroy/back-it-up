<?php

namespace App\Console\Commands;

use App\Mail\DBBackupCompleted;
use App\Records;
use App\Services\BackupData;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Spatie\DbDumper\Databases\MySql;
use Symfony\Component\Process\Process;

class BackupDatbase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Take a backup of your database';

    protected $fileName;
    protected $s3Url;
    protected $sqlSize;
    protected $sqlTime;
    protected $gzipSize;
    protected $compressTime;
    protected $record;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->fileName = uniqid();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->takeMySQLDump();
        $this->compressSqlFile();
        $this->removeTheSqlFile();
        $this->uploadToS3();
        $this->removeTheZipFile();
        $this->makeDBEntry();

        if (config('backitup.send-email')) {
            $this->sendEmailNotification();
        }
    }

    private function getFileSize(File $file)
    {
        $fileSize = $file->getSize();
        $unit = 'Bytes';
        if ($fileSize > 1024) {
            $fileSize = $fileSize / 1024;
            $unit = 'KB';
        }
        if ($fileSize > 1024) {
            $fileSize = $fileSize / 1024;
            $unit = 'MB';
        }

        return round($fileSize) . ' ' . $unit;
    }

    private function takeMySQLDump()
    {
        $dumpStart = microtime(TRUE);

        $databaseName = 'testdb';
        $userName = 'root';
        $password = '';
        $fileName = $this->fileName;

        MySql::create()
            ->setDbName($databaseName)
            ->setUserName($userName)
            ->setPassword($password)
            ->dumpToFile($fileName  . '.sql');

        $file = new File("{$fileName}.sql");
        $this->sqlSize = $this->getFileSize($file);

        $this->compressTime = microtime(TRUE) - $dumpStart;
    }

    private function compressSqlFile()
    {
        $fileName = $this->fileName;
        $compressTime = microtime(TRUE);

        // compress the sql file
        $process = new Process("tar -zcf {$fileName}.tar.gz {$fileName}.sql");
        $process->run();

        $this->sqlTime = microtime(TRUE) - $compressTime;
        $file = new File("{$fileName}.tar.gz");
        $fileSize = $this->getFileSize($file);
        $this->gzipSize = $fileSize;
    }

    private function removeTheSqlFile()
    {
        $fileName = $this->fileName;
        // remove the raw sql file
        $process = new Process("rm {$fileName}.sql");
        $process->run();
    }

    private function uploadToS3()
    {
        $folderName = config('backitup.', 'db-backups');
        $fileName = $this->fileName;

        // upload file to S3
        $filePath = Carbon::now()->format('Y/F');
        $this->s3Url = "{$folderName}/{$filePath}/{$fileName}.tar.gz";
        Storage::disk('s3')->putFileAs("db-backups/{$filePath}", new File("{$fileName}.tar.gz"), "{$fileName}.tar.gz");
    }

    private function removeTheZipFile()
    {
        $fileName = $this->fileName;
        // remove the compressed file
        $process = new Process("rm {$fileName}.tar.gz");
        $process->run();
    }

    private function makeDBEntry()
    {
        $bucket = env('AWS_BUCKET');

        $record = Records::create([
            'sql_file_size' => $this->sqlSize,
            'gzip_file_size' => $this->gzipSize,
            'url' => "https://s3.amazonaws.com/{$bucket}/$this->s3Url",
            'dump_time' => date('H:i:s', $this->sqlTime),
            'compress_time' => date('H:i:s', $this->compressTime),
        ]);

        $this->record = $record;
    }

    /**
     *
     */
    private function sendEmailNotification()
    {
        $toEmail = config('backitup.mail-to');
        Mail::to($toEmail)->send(new DBBackupCompleted($this->record));
    }
}
