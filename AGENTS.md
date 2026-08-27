# AGENTS.md

Guidance for AI coding agents working in this repository. Human contributors
should read [CONTRIBUTING.md](CONTRIBUTING.md) instead — this file exists
because an agent starting a session has no memory of prior sessions and
needs the same orientation restated each time.

## What this package is

This package *is* Laravel's [health route](https://laravel.com/docs/deployment#the-health-route)
— it registers the endpoint itself, dispatches the same `DiagnosingHealth`
event, and fails the same way (throwing), then adds a library of checks,
JSON/HTML presentation, and tooling on top. See the [README](README.md) for
the full pitch. The important consequence for any change you make: this is
**alignment with core Laravel direction, not a competing feature**. Don't
propose bringing back a separate `/health` route, a second JSON encoder, or
anything that would make this package diverge from how `DiagnosingHealth`
behaves in a plain Laravel app.

## Invariants — don't change these without discussing it first

- **Checks fail by throwing.** `HealthCheck::fail()` throws
  `HealthCheckFailedException`; `degrade()` throws
  `HealthCheckDegradedException`. Nothing catches broadly and converts to a
  boolean — that was the 2.x `Status` design and it's gone.
- **Degraded never fails the HTTP response.** A degraded check must never
  turn the endpoint's status code into the configured problem code. Only
  `down` does that.
- **The endpoint is the framework's health route, not a second one.** The
  package must never register anything at a hardcoded `/health` path, and
  should never assume `HEALTHCHECK_PATH`/`config('healthcheck.path')` is any
  particular value — users can and do change it.
- **The ping file is not a route.** It's a static file copied under
  `public/` (see [PingFilePublisher.php](src/PingFilePublisher.php)) that the
  web server serves without booting PHP. It must never become a registered
  Laravel route, and must never overwrite a file that's already there.
- **Debug mode rethrows, matching core.** `UpController` rethrows in debug
  mode instead of rendering — that's intentional parity with how Laravel's
  own exception handler behaves.

## Security-sensitive areas — read before touching

The health endpoint is **public by default** unless the consuming app adds
its own middleware. Treat every response body as something an
unauthenticated caller can read.

- **`HealthCheck::exceptionContext()`** ([src/HealthCheck.php](src/HealthCheck.php))
  deliberately returns only `error` and `class` — no file paths, no line
  numbers, no stack trace. It reports the full exception via `report()`
  (guarded against the logger itself being what's broken) so the detail is
  still available server-side. If you're tempted to add more detail here for
  debugging convenience, it will leak to anonymous callers — don't.
- **`PingFilePublisher`** ([src/PingFilePublisher.php](src/PingFilePublisher.php))
  rejects any ping path containing `..` or empty segments, and re-verifies
  the resolved directory is still under `public_path()` before writing.
  `healthcheck.ping.path` is env-configurable, so a typo or misconfiguration
  should fail loudly (`InvalidArgumentException`) rather than write outside
  `public/`.
- **`Middleware/BasicAuth`** intentionally runs the check suite before
  verifying credentials, and returns the *real* status code with an empty
  body on failed/missing auth (see `tests/Middleware/BasicAuthTest.php`).
  That's a deliberate, tested design — a caller without credentials should
  learn "up or down" but nothing else. Don't "fix" this into a generic 401
  without discussing it; it's a documented behavior change, not a bug.
- Any new bundled check that catches an exception internally should route it
  through `exceptionContext()` rather than building its own context array —
  that's the one place the "don't leak trace details" rule is enforced.

## Before finishing a change

Run the same checks CI and `composer standards:check` run:

```bash
composer test
composer standards:check   # phpcs, phpmd, rector --dry-run
composer larastan
```

All of these should be clean before you consider a change done. If
`composer standards:fix` changes something you don't understand, read the
diff rather than blindly re-running it.

## Compatibility notes

- 2.x is a separate maintained line for consumers who can't move to 3.x's
  breaking, core-aligned design. There is no 3.x config flag to restore 2.x
  behavior (old `/health` path, old JSON shape) — that would just delay the
  same removal to a future major. Point people at 2.x instead.
- 3.x does not support Lumen. It's fine to use
  `Illuminate\Foundation\Application`-only APIs (e.g. `hasDebugModeEnabled()`)
  directly rather than routing around them for Lumen compatibility — but
  prefer the plainest option that works (e.g. `config('app.debug')` over
  `app()->hasDebugModeEnabled()`) when both are equally correct, since it's
  one less framework-internal API to depend on.
