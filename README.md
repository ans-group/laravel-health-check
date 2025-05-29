![ANS Logo](https://www.ans.co.uk/wp-content/themes/ans/images/logo.svg)

# 🚦 Laravel Health Check

🩺 A package for checking the health of your Laravel or Lumen applications.

## 🎯 Purpose

🔍 Provides a simple, extensible way to monitor the health of your Laravel/Lumen app & dependencies. Helps devs & DevOps ensure critical services (🗄️, 🗃️, 💾, etc.) are operational & surfaces problems before they impact users.

## ✨ Main Features

- 🔌 Pluggable Health Checks: database 🗄️, cache 🗃️, env vars ⚙️, logs 📝, storage 💾, Redis 🟥, HTTP 🌐, FTP 📡, scheduler ⏰, package security 🛡️, & more
- 🛠️ Custom Health Checks: add your own for extra services or logic
- 🌐 Configurable Endpoints: `/health` & `/ping` for monitoring & uptime
- 🛡️ Middleware Support: protect endpoints (e.g., Basic Auth)
- 📊 Status Reporting: detailed status (✅, ⚠️, ❌) with context
- 🖥️ Artisan Commands: run checks via CLI for CI/CD or scheduled jobs
- 🧪 Test Coverage: reliable & maintainable

## 👥 Target Users

- 👨‍💻 Developers: monitor app & service health
- 🛠️ DevOps/SRE: external monitoring, alerting, recovery
- 🏗️ Platform Engineers: extend with custom checks

## 🏗️ System Architecture

- 🌐 Endpoints: `/health` (JSON summary), `/ping` (liveness)
- 🧩 Checks: each is a class (see `config/healthcheck.php`)
- 🛡️ Middleware: protect endpoints
- 🖥️ CLI: run via Artisan for CI/CD
- 🧩 Extensible: add custom checks

## 🚀 Getting Started

### 📦 Installation

1. `composer require ans-group/laravel-health-check`
2. `php artisan vendor:publish --provider="UKFast\HealthCheck\HealthCheckServiceProvider"`

### ⚙️ Configuration

- Edit `config/healthcheck.php` to enable/disable/customize checks
- Add your check class to `checks` array:
  ```php
  // ...existing code...
  'checks' => [
      UKFast\HealthCheck\Checks\DatabaseHealthCheck::class, // 🗄️
      App\HealthChecks\MyCustomCheck::class, // 🛠️
  ],
  // ...existing code...
  ```
- Set options for each check (e.g., env vars):
  ```php
  // ...existing code...
  'checks' => [
      UKFast\HealthCheck\Checks\EnvHealthCheck::class => [
          'required' => ['APP_KEY', 'DB_CONNECTION'], // ⚙️
      ],
  ],
  // ...existing code...
  ```
- Protect endpoints with middleware:
  ```php
  // ...existing code...
  Route::middleware(['basicAuth'])->group(function () {
      Route::get('/health', [HealthCheckController::class, 'index']);
      Route::get('/ping', [PingController::class, 'index']);
  });
  // ...existing code...
  ```

### 📝 Configuration File Reference

- **checks**: array of health check classes (see above)
- **Per-check config**: options for each check (e.g., `required`, `disks`, `urls`, `connections`, `hosts`)
- **Disable a check**: remove/comment it out
- **Add custom check**: add your class, optionally with config
- **Other options**: see config/check class docs

## 🧑‍💻 Creating Custom Health Checks

1. `php artisan make:health-check MyCustomCheck` 🛠️
2. Implement your logic:
   ```php
   // ...existing code...
   class MyCustomCheck extends HealthCheck {
       public function name(): string { return 'my_custom_check'; }
       public function run(): Status {
           // ...existing code...
           if (/* healthy */) return Status::ok('Everything is fine!');
           return Status::problem('Something is wrong!');
       }
   }
   // ...existing code...
   ```
3. Register in `config/healthcheck.php`
4. Test: `php artisan health:check` or visit `/health`

## 🛠️ Usage

- 🌐 `/health`: JSON report
- 🌐 `/ping`: liveness
- 🖥️ CLI: `php artisan health:check`
- 🛠️ Make custom: `php artisan make:health-check MyCustomCheck`

## 🧩 Extending

- Create class implementing `HealthCheck` (see `src/Checks/`)
- Register in config
- Add config/deps as needed

## 📝 Example Checks
- 🗄️ Database
- 🗃️ Cache
- ⚙️ Env vars
- 📝 Log file
- 💾 Storage
- 🟥 Redis
- 🌐 HTTP/FTP
- 🛡️ Package security
- ⏰ Scheduler

## 🤝 Contributing
See [CONTRIBUTING.md](CONTRIBUTING.md)

## 🔒 Security
See [SECURITY.md](SECURITY.md)

## 📄 License
MIT
