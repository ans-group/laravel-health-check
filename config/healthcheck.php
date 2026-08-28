<?php

return [
    /**
     * URI for the health endpoint. Same role as Laravel's
     * `withRouting(health: ...)` — set this instead of the framework
     * `health:` argument (omit `health:` so this package owns the route).
     */
    'path' => env('HEALTHCHECK_PATH', '/up'),

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
     * A list of middleware to run on the health endpoint.
     * It's recommended that you have a middleware that only
     * allows admin consumers to see the endpoint.
     *
     * See UKFast\HealthCheck\Middleware\Authenticate for a one-size-fits-all
     * solution that gates the endpoint behind basic auth, a header token,
     * or both.
     */
    'middleware' => [],

    /*
     * Used by the Authenticate middleware. Each method only authenticates
     * if it's configured - leave user/password unset to disable basic
     * auth, or token unset to disable the header token, independently.
     */
    'auth' => [
        // HTTP basic auth, for older monitoring systems that can't send
        // custom headers.
        'user' => env('HEALTH_CHECK_USER'),
        'password' => env('HEALTH_CHECK_PASSWORD'),

        // A shared secret sent as a request header, for anything that can
        // send custom headers.
        'header' => env('HEALTH_CHECK_AUTH_HEADER', 'X-Health-Check-Token'),
        'token' => env('HEALTH_CHECK_AUTH_TOKEN'),
    ],

    /*
     * Route name for the health endpoint
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
     * Default code for HTTP health check when there any problem occured.
     * Will be used in the health endpoint response.
     */
    'default-problem-http-code' => 500,

    /*
     * Default timeout for cURL requests for HTTP health check.
     */
    'default-curl-timeout' => 2.0,

    /*
     * An array of other services that use the health check package
     * to hit. The URI should reference that service's health endpoint
     * specifically.
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
     * Settings for the Package Security health check
     */
    'package-security' => [
        'exclude-dev' => false, // Exclude dev dependencies from the security check
        'ignore' => [], // Packages to ignore
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
];
