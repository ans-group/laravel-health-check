<?php

namespace Tests\Stubs\Storage;

use Exception;

class BadDisk
{
    /**
     * @throws Exception
     */
    public function __call($name, $arguments): never
    {
        throw new Exception();
    }
}
