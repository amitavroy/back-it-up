<?php

namespace App\Console\Commands;

use App\Records;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Http\File;
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
    protected $gzipSize;

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

        \Log::info("Backup done. Sql file size {$this->sqlSize} and after zip {$this->gzipSize} uploaded at url {$this->s3Url}");
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
        \Log::info('Start dump');

        $databaseName = 'trualta';
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

        $dumpTimeInSec = microtime(TRUE) - $dumpStart;
        \Log::info('End dump took ' . $dumpTimeInSec . ' seconds');
    }

    private function compressSqlFile()
    {
        $fileName = $this->fileName;
        \Log::info('Start compression');
        $compressTime = microtime(TRUE);

        // compress the sql file
        $process = new Process("tar -zcf {$fileName}.tar.gz {$fileName}.sql");
        $process->run();

        $compressTimeInSec = microtime(TRUE) - $compressTime;
        $file = new File("{$fileName}.tar.gz");
        $fileSize = $this->getFileSize($file);
        $this->gzipSize = $fileSize;
        \Log::info("End compression took {$compressTimeInSec} seconds to compress file of {$fileSize}");
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
        $fileName = $this->fileName;

        // upload file to S3
        $filePath = Carbon::now()->format('Y/F');
        $uploadTime = microtime(TRUE);
        $this->s3Url = "db-backups/{$filePath}/{$fileName}.tar.gz";
        Storage::disk('s3')->putFileAs("db-backups/{$filePath}", new File("{$fileName}.tar.gz"), "{$fileName}.tar.gz");
        $uploadTimeInSec = microtime(TRUE) - $uploadTime;
        \Log::info('Upload of file took ' . $uploadTimeInSec . ' seconds');
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

        Records::create([
            'sql_file_size' => $this->sqlSize,
            'gzip_file_size' => $this->gzipSize,
            'url' => "https://s3.amazonaws.com/{$bucket}/$this->s3Url",
        ]);
    }
}
