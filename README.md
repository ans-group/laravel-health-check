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

## Getting Started

1. **Install via Composer**:
   ```bash
   composer require ans-group/laravel-health-check
   ```
2. **Publish the config (optional)**:
   ```bash
   php artisan vendor:publish --provider="UKFast\HealthCheck\HealthCheckServiceProvider"
   ```
3. **Configure checks** in `config/healthcheck.php`.
4. **Protect endpoints** as needed using middleware.
5. **Access health endpoints** at `/health` and `/ping`.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

## Security

If you discover a security vulnerability, please see [SECURITY.md](SECURITY.md) for reporting instructions.

## License

MIT
