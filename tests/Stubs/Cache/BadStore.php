<?php

namespace Tests\Stubs\Cache;

use Exception;

class BadStore
{
    /**
     * @param array<int, mixed> $arguments
     * @throws Exception
     */
    public function __call(string $name, $arguments): never
    {
        throw new Exception();
    }
}
