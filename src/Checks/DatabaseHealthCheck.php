<?php

namespace UKFast\HealthCheck\Checks;

use Exception;
use Illuminate\Database\DatabaseManager;
use UKFast\HealthCheck\HealthCheck;

class DatabaseHealthCheck extends HealthCheck
{
    protected $name = 'database';

    /** @var \Illuminate\Database\DatabaseManager */
    protected $db;

    public function __construct(DatabaseManager $db)
    {
        $this->db = $db;
    }

    public function status()
    {
        foreach (config('healthcheck.database.connections') as $connection) {
            try {
                if ($connection == 'default') {
                    $connection = '';
                }

                $pdo = $this->db->connection($connection)->getPdo();
            } catch (Exception $e) {
                return $this->problem('Could not connect to db', [
                    'connection' => $connection,
                    'exception' => $this->exceptionContext($e),
                ]);
            }
        }

        return $this->okay();
    }
}
