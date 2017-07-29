<?php

namespace App\Services;

class BackupData
{
    private $sqlFileSize;
    private $url;
    private $gzipFileSize;
    private $sqlTime;
    private $compressTime;

    /**
     * BackupData constructor.
     * @param $sqlFileSize
     * @param $gzipFileSize
     * @param $url
     * @param $sqlTime
     * @param $compressTime
     */
    public function __construct($sqlFileSize, $gzipFileSize, $url, $sqlTime, $compressTime)
    {
        $this->sqlFileSize = $sqlFileSize;
        $this->url = $url;
        $this->gzipFileSize = $gzipFileSize;
        $this->sqlTime = $sqlTime;
        $this->compressTime = $compressTime;
    }

    /**
     * @return mixed
     */
    public function getSqlFileSize()
    {
        return $this->sqlFileSize;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return mixed
     */
    public function getGzipFileSize()
    {
        return $this->gzipFileSize;
    }
}