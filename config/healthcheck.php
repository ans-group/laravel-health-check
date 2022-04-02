<?php

return [
    /*
     * Base path for the health check endpoints, by default will use /
     */
    'base-path' => '',

    /**
     * Paths to host the health check and ping endpoints
     */
    'route-paths' => [
        'health' => '/health',
        'ping' => '/ping',
    ],

    /*
     * List of health checks to run when determining the health
     * of the service
     */
    'checks' => [
        UKFast\HealthCheck\Checks\LogHealthCheck::class,
        UKFast\HealthCheck\Checks\DatabaseHealthCheck::class,
        UKFast\HealthCheck\Checks\EnvHealthCheck::class,
    ],

    /*
     * A list of middleware to run on the health-check route
     * It's recommended that you have a middleware that only
     * allows admin consumers to see the endpoint.
     *
     * See UKFast\HealthCheck\Middleware\BasicAuth for a one-size-fits all
     * solution
     */
    'middleware' => [],

    /*
     * Used by the basic auth middleware
     */
    'auth' => [
        'user' => env('HEALTH_CHECK_USER'),
        'password' => env('HEALTH_CHECK_PASSWORD'),
    ],

    /*
     * Routename for the healthcheck
     */
    'route-name' => 'healthcheck',

    /*
     * Can define a list of connection names to test. Names can be
     * found in your config/database.php file. By default, we just
     * check the 'default' connection
     */
    'database' => [
        'connections' => ['default'],
    ],

    /*
     * Can give an array of required environment values, for example
     * 'REDIS_HOST'. If any don't exist, then it'll be surfaced in the
     * context of the healthcheck
     */
    'required-env' => [],

    /*
     * List of addresses and expected response codes to
     * monitor when running the HTTP health check
     *
     * e.g. address => response code
     */
    'addresses' => [],

    /*
     * Default response code for HTTP health check. Will be used
     * when one isn't provided in the addresses config.
     */
    'default-response-code' => 200,

    /*
     * Default timeout for cURL requests for HTTP health check.
     */
    'default-curl-timeout' => 2.0,

    /*
     * An array of other services that use the health check package
     * to hit. The URI should reference the endpoint specifically,
     * for example: https://api.example.com/health
     */
    'x-service-checks' => [],

    /*
     * A list of stores to be checked by the Cache health check
     */
    'cache' => [
        'stores' => [
            'array',
        ],
    ],

    /*
     * A list of disks to be checked by the Storage health check
     */
    'storage' => [
        'disks' => [
            'local',
        ],
    ],

    /*
     * A list of packages to be ignored by the Package Security health check
     */
    'package-security' => [
        'ignore' => [],
    ],

    'scheduler' => [
        'cache-key' => 'laravel-scheduler-health-check',
        'minutes-between-checks' => 5,
    ],

    /*
     * Default value for env checks.
     * For each key, the check will call `env(KEY, config('healthcheck.env-default-key'))` 
     * to avoid false positives when `env(KEY)` is defined but is null.
     */
    'env-check-key' => 'HEALTH_CHECK_ENV_DEFAULT_VALUE',

    /*
     * Additional config can be put here. For example, a health check
     * for your .env file needs to know which keys need to be present.
     * You can pass this information by specifying a new key here then
     * accessing it via config('healthcheck.env') in your healthcheck class
     */
];
