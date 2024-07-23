<?php

namespace Tests\Stubs\Database;

use Illuminate\Database\Connection;

class HealthyConnection extends Connection
{
    public function __construct()
    {
    }

    public function getPdo(): bool
    {
        return true;
    }
}
