<?php

namespace UKFast\HealthCheck\Checks;

use League\Flysystem\Adapter\Ftp;
use UKFast\HealthCheck\HealthCheck;

class FtpHealthCheck extends HealthCheck
{
    protected $name = 'ftp';

    /**
     * @var \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected $ftp;

    public function __construct(Ftp $ftp)
    {
        $this->ftp = $ftp;
    }

    public function status()
    {
        try {
            $this->ftp->getConnection();
        } catch (\RuntimeException $e) {
            return $this->problem('Could not connect to FTP server', [
                'exception' => $this->exceptionContext($e),
            ]);
        }

        return $this->okay();
    }
}
