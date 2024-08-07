<?php

use Rector\CodeQuality\Rector\Concat\JoinStringConcatRector;
use Rector\CodeQuality\Rector\Empty_\SimplifyEmptyCheckOnEmptyArrayRector;
use Rector\Config\RectorConfig;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/',
    ])
    ->withSkip([
        __DIR__ . '/vendor',
    ])
    ->withSkip([
        AddOverrideAttributeToOverriddenMethodsRector::class,
        JoinStringConcatRector::class,
        SimplifyEmptyCheckOnEmptyArrayRector::class,
        DisallowedEmptyRuleFixerRector::class
    ])
    ->withPhpSets()
    ->withPreparedSets(
        true,
        true,
        false,
        true
    );
