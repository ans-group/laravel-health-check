# Laravel Health Check

A package for checking the health of your Laravel or Lumen applications.

## Purpose

This package provides a simple, extensible way to monitor the health of your Laravel/Lumen application and its dependencies. It is designed to help developers and DevOps teams ensure that critical services (such as databases, cache, storage, and more) are operational, and to surface problems before they impact end users.

## Main Features

- **Pluggable Health Checks**: Includes checks for database, cache, environment variables, logs, storage, Redis, HTTP endpoints, FTP, scheduler, package security, and more.
- **Custom Health Checks**: Easily add your own health checks to monitor additional services or business logic.
- **Configurable Endpoints**: Exposes `/health` and `/ping` endpoints for health monitoring and uptime checks.
- **Middleware Support**: Protect health endpoints with middleware (e.g., Basic Auth) to restrict access.
- **Status Reporting**: Returns detailed status for each check (OK, Degraded, Problem) with context and messages.
- **Artisan Commands**: Run health checks via CLI for integration with CI/CD or scheduled jobs.
- **Extensive Test Coverage**: Ensures reliability and maintainability.

## Target Users

- **Developers**: Integrate health checks into Laravel/Lumen projects to monitor application and service health.
- **DevOps/SRE Teams**: Use endpoints for external monitoring, alerting, and automated recovery.
- **Platform Engineers**: Extend with custom checks for infrastructure or business-specific requirements.

## How It Fits Into System Architecture

- **Endpoints**: The package registers `/health` and `/ping` HTTP endpoints. `/health` returns a JSON summary of all configured checks; `/ping` is a lightweight liveness probe.
- **Checks**: Each check is a class implementing a standard interface. Checks are configured in `config/healthcheck.php` and instantiated by the service provider.
- **Middleware**: Endpoints can be protected using middleware (e.g., BasicAuth) to restrict access to authorized users or systems.
- **CLI Integration**: Health checks can be run via Artisan commands for use in CI/CD pipelines or scheduled tasks.
- **Extensibility**: Developers can add custom checks by creating new classes and registering them in the config.

## Getting Started

### Installation

1. **Install via Composer**:
   ```bash
   composer require ans-group/laravel-health-check
   ```
2. **Publish the config (optional)**:
   ```bash
   php artisan vendor:publish --provider="UKFast\HealthCheck\HealthCheckServiceProvider"
   ```

### Configuration

- Edit `config/healthcheck.php` to enable, disable, or customize the built-in health checks.
- Add your own check class to the `checks` array to register it for execution. For example:
  ```php
  // config/healthcheck.php
  return [
      'checks' => [
          UKFast\HealthCheck\Checks\DatabaseHealthCheck::class,
          App\HealthChecks\MyCustomCheck::class, // Add your custom check here
      ],
      // ...other config options...
  ];
  ```
- You can set options for each check (such as connection names, required environment variables, etc.) in this config file. For example:
  ```php
  // config/healthcheck.php
  return [
      'checks' => [
          UKFast\HealthCheck\Checks\EnvHealthCheck::class => [
              'required' => ['APP_KEY', 'DB_CONNECTION'],
          ],
      ],
  ];
  ```
- Protect the `/health` and `/ping` endpoints by adding middleware in your `routes/web.php` or `routes/api.php` as needed. For example:
  ```php
  // routes/web.php
  Route::middleware(['basicAuth'])->group(function () {
      Route::get('/health', [HealthCheckController::class, 'index']);
      Route::get('/ping', [PingController::class, 'index']);
  });
  ```

### Creating Custom Health Checks

1. **Generate a stub:**
   ```bash
   php artisan make:health-check MyCustomCheck
   ```
   This will create a new check class in `app/HealthChecks` (or your default namespace).

2. **Implement your logic:**
   ```php
   // app/HealthChecks/MyCustomCheck.php
   use UKFast\HealthCheck\HealthCheck;
   use UKFast\HealthCheck\Status;

   class MyCustomCheck extends HealthCheck
   {
       public function name(): string
       {
           return 'my_custom_check';
       }

       public function run(): Status
       {
           // Your custom logic here
           if (/* healthy */) {
               return Status::ok('Everything is fine!');
           }
           return Status::problem('Something is wrong!');
       }
   }
   ```

3. **Register your check:**
   - Add your new class to the `checks` array in `config/healthcheck.php` as shown above.
   - Optionally, add any configuration or dependencies your check needs.

4. **Test your check:**
   - Run `php artisan health:check` or visit `/health` to see your custom check in action.

### Usage

- **Web Endpoints**:
  - Visit `/health` for a full JSON report of all checks.
  - Visit `/ping` for a lightweight liveness check.
- **CLI**:
  - Run all health checks via Artisan:
    ```bash
    php artisan health:check
    ```
  - Create a new custom health check stub:
    ```bash
    php artisan make:health-check MyCustomCheck
    ```

### Extending

- Create a new class that implements the `HealthCheck` interface (see `src/Checks/` for examples).
- Register your custom check in the `checks` array in `config/healthcheck.php`.
- Optionally, add configuration or dependencies as needed.

## Example Checks
- Database connectivity
- Cache store availability
- Required environment variables
- Log file writability
- Storage disk health
- Redis connection
- HTTP/FTP endpoint monitoring
- Package security (vulnerable dependencies)
- Scheduler status

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

## Security

If you discover a security vulnerability, please see [SECURITY.md](SECURITY.md) for reporting instructions.

## License

MIT
