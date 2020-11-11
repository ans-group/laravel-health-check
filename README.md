<img src="https://images.ukfast.co.uk/logos/ukfast/441x126_transparent_strapline.png" alt="UKFast Logo" width="350px" height="auto" />

![Tests](https://github.com/ukfast/laravel-health-check/workflows/Run%20tests/badge.svg?branch=master)

# Health Check Package

The purpose of this package is to surface a health-check endpoint on `/health` which, when hit, returns the status of all the services and dependencies your project relies on, along with the overall health of your system. This is useful in both development and production for debugging issues with a faulty application.

This package also adds a `/ping` endpoint. Just hit `/ping` and receive `pong` in response. 


## Installation

To install the package:

Run  `composer require ukfast/laravel-health-check` to add the package to your dependencies.

This will automatically install the package to your vendor folder.

#### Laravel

In Laravel applications, the service provider should be automatically registered, but you may register it manually in your `config/app.php` file:

```php
'providers' => [
    // ...
    UKFast\HealthCheck\HealthCheckServiceProvider::class,
];  
```

#### Lumen

To have the package function in Lumen, you need to register the service provider. add the following to your `bootstrap/app.php` file:

```php
$app->register(\UKFast\HealthCheck\HealthCheckServiceProvider::class);
```

You can test that the package is working correctly by hitting the `/health` endpoint.


## Configuration

### Laravel


##### Facade

We surface a `HealthCheck` facade with the package. You can use the `passes`, `fails`, or `all` methods, if you want to access the results of a check or the number of checks running from within your code.

```php
if (HealthCheck::passes('env')) {
    // check passed
}

if (HealthCheck::fails('http')) {
    // check failed
}

$numberOfChecks = HealthCheck::all()->count();
```

If one of the checks provided cannot be resolved from the service container, we'll throw a `CheckNotFoundException` with the name of the missing check.


##### Config

If you'd like to tweak the config file (helpful for configuring the `EnvHealthCheck`, for example), you can publish it with:

```php
php artisan vendor:publish --provider="UKFast\HealthCheck\HealthCheckServiceProvider" --tag="config"
```


##### Console command

Check all: `php artisan health-check:status`
Only specific checks: `php artisan health-check:status --only=log,cache`
Except specific checks: `php artisan health-check:status --except=cache`

##### Middleware

You can register custom middleware to run on requests to the `/health` endpoint. You can add this to the middleware array in the `config/healthcheck.php` config file created by the command above, as shown in the example below:

```php
/**
 * A list of middleware to run on the health-check route
 * It's recommended that you have a middleware that only
 * allows admin consumers to see the endpoint.
 *
 * See UKFast\HealthCheck\BasicAuth for a one-size-fits all
 * solution
 */
'middleware' => [
    App\Http\Middleware\CustomMiddleware::class
],
```

Now your `CustomMiddleware` middleware will be ran on every request to the `/health` endpoint.


### Lumen


##### Facade

We surface a `HealthCheck` facade with the package. You can use the `passes`, `fails`, or `all` methods, if you want to access the results of a check or the number of checks running from within your code.

```php
if (HealthCheck::passes('env')) {
    // check passed
}

if (HealthCheck::fails('http')) {
    // check failed
}

$numberOfChecks = HealthCheck::all()->count();
```

If one of the checks provided cannot be resolved from the service container, we'll throw a `CheckNotFoundException` with the name of the missing check.


##### Config

If you'd like to tweak the config file (helpful for configuring the `EnvHealthCheck`, for example):

Manually copy the package config file (see example below) to `config\healthcheck.php` (you may need to create the config directory if it does not already exist).

```php
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
     * 
     * See UKFast\HealthCheck\BasicAuth for a one-size-fits all
     * solution
     */
    'middleware' => [],

    /**
     * Used by the basic auth middleware
     */
    'auth' => [
        'user' => env('HEALTH_CHECK_USER'),
        'password' => env('HEALTH_CHECK_PASSWORD'),
    ],

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
     * A list of stores to be checked by the Cache health check
     */
    'cache' => [
        'stores' => [
            'array'
        ]
    ],

    /**
     * A list of disks to be checked by the Storage health check
     */
    'storage' => [
        'disks' => [
            'local',
        ]
    ],

    /**
     * Additional config can be put here. For example, a health check
     * for your .env file needs to know which keys need to be present.
     * You can pass this information by specifying a new key here then
     * accessing it via config('healthcheck.env') in your healthcheck class
     */
];
```

Update your `bootstrap/app.php` file to override the default package config:

```php
$app->configure('healthcheck');
```


##### Middleware

You can register custom middleware to run on requests to the `/health` endpoint. You can add this to the middleware array in the `config/healthcheck.php` config file you created using the config above, as shown in the example below:

```php
/**
 * A list of middleware to run on the health-check route
 * It's recommended that you have a middleware that only
 * allows admin consumers to see the endpoint.
 *
 * See UKFast\HealthCheck\BasicAuth for a one-size-fits all
 * solution
 */
'middleware' => [
    App\Http\Middleware\CustomMiddleware::class
],
```

Now your `CustomMiddleware` middleware will be ran on every request to the `/health` endpoint.

Out of the box, the health check package provides:

 * BasicAuth - Requires that basic auth credentials be sent in order to see full status
 * AddHeaders - Adds X-check-status headers to the response, so you can avoid having to parse JSON

### Checks

##### Scheduler Health Check

The scheduler health check works by using a time limited cache key on your project every minute. You will need to register the
CacheSchedulerRunning command to run every minute in your projects `Kernel.php`.

You can customise the cache key and length of time in minutes before the scheduler not running will trigger an error.

```php
$schedule->command(CacheSchedulerRunning::class)->everyMinute();
```

## Creating your own health checks

It's very simple to create your own health checks.

In this example, we'll create a health check for Redis.

You first need to create your health-check class, you can put this inside `App\HealthChecks`.
In this case, the class would be `App\HealthChecks\RedisHealthCheck`

Every health check needs to extend the base `HealthCheck` class and implement a `status()` method. You should also set the `$name` property for display purposes.

```php
<?php

namespace App\HealthChecks;

use UKFast\HealthCheck\HealthCheck;

class RedisHealthCheck extends HealthCheck
{
    protected $name = 'my-fancy-redis-check';

    public function status()
    {
        return $this->okay();
    }
}
```

Now we've got our basic class setup, we can add it to the list of checks to run in our `config/healthcheck.php` file.

Open up `config/healthcheck.php` and go to the `'checks'` array. Add your class to the list of those checks:

```php
'checks' => [
    // ...
    App\HealthChecks\RedisHealthCheck::class,
]
```

If you hit the `/health` endpoint now, you'll see that there's a `my-fancy-redis-check` property and it should return `OK` for the status.

We can now go about actually implementing the check properly.

Go back to the `status()` method in the `RedisHealthCheck` class.

Add in the following code:

```php
public function status()
{
    try {
        Redis::ping();
    } catch (Exception $e) {
        return $this->problem('Failed to connect to redis', [
            'exception' => $this->exceptionContext($e),
        ]);
    }

    return $this->okay();
}
```

You'll need to import the following at the top as well

```php
use Illuminate\Support\Facades\Redis;
use UKFast\HealthCheck\HealthCheck;
use Exception;
```

Finally, hit the `/health` endpoint, depending on if your app can actually hit Redis, you'll see the status of Redis. If it's still returning `OK` try changing `REDIS_HOST` to something that doesn't exist to trip the error.


## Contributing

We welcome contributions to this package that will be beneficial to the community.

You can reach out to our open-source team via **open-source@ukfast.co.uk** who will get back to you as soon as possible.

Please refer to our [CONTRIBUTING](CONTRIBUTING.md) file for more information.


## Security

If you think you have identified a security vulnerability, please contact our team via **security@ukfast.co.uk** who will get back to you as soon as possible, rather than using the issue tracker.


## Licence

This project is licenced under the MIT Licence (MIT). Please see the [Licence](LICENCE) file for more information.
