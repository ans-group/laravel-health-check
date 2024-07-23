<?php

namespace Tests\Stubs\Cache;

use Exception;

class BadStore
{
    /**
     * @throws Exception
     */
    public function __call($name, $arguments): never
    {
        throw new Exception();
    }
}
