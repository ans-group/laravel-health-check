<?php

namespace Tests\Stubs\Storage;

use Exception;

class BadDisk
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
