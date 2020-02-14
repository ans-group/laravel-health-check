<?php

return [
    /**
     * Base path for the health check endpoints, by default will use /
     */
    'base-path' => '',

    /**
     * List of health checks to run when determining the health
     * of the service
     */
    'checks' => [
        UKFast\HealthCheck\Checks\LogHealthCheck::class,
        UKFast\HealthCheck\Checks\DatabaseHealthCheck::class,
        UKFast\HealthCheck\Checks\EnvHealthCheck::class
    ],

    /**
     * A list of middleware to run on the health-check route
     * It's recommended that you have a middleware that only
     * allows admin consumers to see the endpoint.
     */
    'middleware' => [],

    /**
     * Can define a list of connection names to test. Names can be
     * found in your config/database.php file. By default, we just
     * check the 'default' connection
     */
    'database' => [
        'connections' => ['default'],
    ],

    /**
     * Can give an array of required environment values, for example
     * 'REDIS_HOST'. If any don't exist, then it'll be surfaced in the
     * context of the healthcheck
     */
    'required-env' => [],

    /**
     * List of addresses and expected response codes to
     * monitor when running the HTTP health check
     *
     * e.g. address => response code
     */
    'addresses' => [],

    /**
     * Default response code for HTTP health check. Will be used
     * when one isn't provided in the addresses config.
     */
    'default-response-code' => 200,

    /**
     * Default timeout for cURL requests for HTTP health check.
     */
    'default-curl-timeout' => 2.0,

    /**
     * An array of other services that use the health check package
     * to hit. The URI should reference the endpoint specifically,
     * for example: https://api.example.com/health
     */
    'x-service-checks' => [],

    /**
     * Additional config can be put here. For example, a health check
     * for your .env file needs to know which keys need to be present.
     * You can pass this information by specifying a new key here then
     * accessing it via config('healthcheck.env') in your healthcheck class
     */
];
