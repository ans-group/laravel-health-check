<?php

declare(strict_types=1);

namespace UKFast\HealthCheck\Checks;

use League\Flysystem\FilesystemException;
use League\Flysystem\Ftp\FtpAdapter;
use UKFast\HealthCheck\HealthCheck;

class FtpHealthCheck extends HealthCheck
{
    protected string $name = 'ftp';

    public function __construct(
        protected FtpAdapter $ftpAdapter,
    ) {
    }

    public function check(): void
    {
        try {
            $this->ftpAdapter->listContents('', false);
        } catch (FilesystemException $exception) {
            $this->fail('Could not connect to FTP server', [
                'exception' => $this->exceptionContext($exception),
            ]);
        }
    }
}
