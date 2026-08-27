<?php

declare(strict_types=1);

namespace UKFast\HealthCheck\Checks;

use Exception;
use Illuminate\Database\DatabaseManager;
use UKFast\HealthCheck\HealthCheck;

class DatabaseHealthCheck extends HealthCheck
{
    protected string $name = 'database';

    public function __construct(
        protected DatabaseManager $database,
    ) {
    }

    public function check(): void
    {
        foreach (config('healthcheck.database.connections') as $connection) {
            try {
                if ($connection == 'default') {
                    $connection = '';
                }

                $this->database->connection($connection)->getPdo();
            } catch (Exception $exception) {
                $this->fail('Could not connect to db', [
                    'connection' => $connection,
                    'exception' => $this->exceptionContext($exception),
                ]);
            }
        }
    }
}
