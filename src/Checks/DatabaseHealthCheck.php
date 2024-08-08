<?php

namespace UKFast\HealthCheck\Checks;

use Exception;
use Illuminate\Database\DatabaseManager;
use UKFast\HealthCheck\HealthCheck;
use UKFast\HealthCheck\Status;

class DatabaseHealthCheck extends HealthCheck
{
    protected string $name = 'database';

    public function __construct(
        protected DatabaseManager $database,
    ) {
    }

    public function status(): Status
    {
        foreach (config('healthcheck.database.connections') as $connection) {
            try {
                if ($connection == 'default') {
                    $connection = '';
                }

                $this->database->connection($connection)->getPdo();
            } catch (Exception $exception) {
                return $this->problem('Could not connect to db', [
                    'connection' => $connection,
                    'exception' => $this->exceptionContext($exception),
                ]);
            }
        }

        return $this->okay();
    }
}
