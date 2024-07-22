<?php

namespace UKFast\HealthCheck\Checks;

use League\Flysystem\FilesystemException;
use League\Flysystem\Ftp\FtpAdapter;
use UKFast\HealthCheck\HealthCheck;
use UKFast\HealthCheck\Status;

class FtpHealthCheck extends HealthCheck
{
    protected string $name = 'ftp';

    public function __construct(
        protected FtpAdapter $ftpAdapter,
    ) {
    }

    public function status(): Status
    {
        try {
            $this->ftpAdapter->listContents('', false);
        } catch (FilesystemException $exception) {
            return $this->problem('Could not connect to FTP server', [
                'exception' => $this->exceptionContext($exception),
            ]);
        }

        return $this->okay();
    }
}
