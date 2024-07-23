<?php

namespace Tests\Stubs\Database;

use Exception;
use Illuminate\Database\Connection;

class BadConnection extends Connection
{
    public function __construct()
    {
    }

    /**
     * @throws Exception
     */
    public function getPdo(): never
    {
        throw new Exception();
    }
}
