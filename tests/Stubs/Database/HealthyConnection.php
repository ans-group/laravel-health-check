<?php

namespace Tests\Stubs\Database;

use Illuminate\Database\Connection;
use Mockery;
use PDO;

class HealthyConnection extends Connection
{
    public function __construct()
    {
    }

    public function getPdo(): PDO
    {
        return Mockery::mock(PDO::class);
    }
}
