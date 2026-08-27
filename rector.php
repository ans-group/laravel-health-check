<?php

use Rector\CodeQuality\Rector\Empty_\SimplifyEmptyCheckOnEmptyArrayRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withSkip([
        SimplifyEmptyCheckOnEmptyArrayRector::class,
    ])
    ->withPhpSets()
    ->withPreparedSets(
        true,
        true,
        false,
        true
    );
