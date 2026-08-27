# Upgrade to 4.0

4.0 is a breaking release. Stay on 2.x until you can take these changes.

## Framework health route

Remove `health:` from `withRouting()` in `bootstrap/app.php`. This package registers the health endpoint. If both are set, two handlers compete for the same URI.

Set the URI with `HEALTHCHECK_PATH` or `config('healthcheck.path')` (same idea as Laravel's `health:` argument).

## `/health` is gone

There is no compatibility flag or redirect. Point load balancers, k8s probes, and clients at the health endpoint path you configure.

JSON is no longer `{ "status": "OK"|"PROBLEM"|"DEGRADED", "<check>": { "status": "OK", ... } }`.

4.0 JSON matches Laravel's own health route contract exactly (`{"status": "up"|"down"}`) and extends it with a `checks` breakdown — no other top-level keys:

```json
{
  "status": "up",
  "checks": {
    "database": { "status": "up" },
    "redis": {
      "status": "down",
      "message": "Failed to connect to redis",
      "context": {}
    }
  }
}
```

Overall `status` is `up` or `down` (Laravel-style). Per-check values are `up`, `down`, or `degraded`. A degraded check does not fail the HTTP status; a `down` check uses `healthcheck.default-problem-http-code` (default 500). Browsers get an HTML view (`healthcheck::up`) built on Laravel's own health page markup — same "Application up" / "Application experiencing problems" heading, same Tailwind CDN styling — extended below with a per-check table; `Accept: application/json` gets JSON.

If you were reading the top-level `message` key from 2.x-era 4.0 pre-release builds, it's gone — the per-check `checks.*.message` values it summarized are still there.

## Custom checks must throw

`status()` returning `okay()` / `problem()` / `degraded()` is not the HTTP contract anymore. Implement `check(): void` and call `$this->fail()` or `$this->degrade()`.

```php
public function check(): void
{
    if ($unhealthy) {
        $this->fail('Something went wrong', ['debug' => 'info']);
    }
}
```

`php artisan make:check` generates the new shape. 2.x check classes will not work until you rewrite them.

## Ping is not a Laravel route

The `GET` ping route is removed. For a web-server-only liveness file, set `HEALTHCHECK_PING=true` (or `healthcheck.ping.enabled`). The package writes `pong` to `public/{healthcheck.ping.path}` if that file does not already exist.

## Failure context no longer includes file paths or stack traces

`exceptionContext()` (used by the bundled checks, and available to your own) now returns only `error` and `class`. It used to also include `file`, `line`, and a full `trace` — since the health endpoint is public by default, that meant an unauthenticated caller could read server file paths and stack traces in a failing check's response. The full exception is still passed to `report()` (your normal logger/error tracker), so nothing is lost server-side — it's just no longer in the HTTP response body.

If you built tooling against the old `context.exception.trace`/`file`/`line` shape, it's gone in 4.0.

## Lumen is no longer supported

4.0 drops Lumen. If you're on Lumen, stay on 2.x.

## Other

- `healthcheck.route-paths` is replaced by `healthcheck.path` and `healthcheck.ping`.
- Named route `healthcheck.route-name` still exists; it points at the health endpoint, not the old `/health` URI.
- If a route already exists at the configured health path when the package boots (for example, the framework's own `health:` argument is still set), a warning is logged. This doesn't stop either route from registering — remove `health:` from `withRouting()` as described above to avoid two handlers on the same URI.
- `healthcheck.ping.path` is now validated: a `..` segment, or a path that would resolve outside `public/`, throws an `InvalidArgumentException` at boot instead of writing the file somewhere unexpected.
