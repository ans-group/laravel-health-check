<?php

declare(strict_types=1);

namespace Tests\Stubs\Cache;

use Exception;

class BadStore
{
    /**
     * @param array<int, mixed> $arguments
     * @throws Exception
     */
    public function __call(string $name, array $arguments): never
    {
        throw new Exception();
    }
}
