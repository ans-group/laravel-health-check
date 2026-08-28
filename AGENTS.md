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
propose a separate `/health` route, a second JSON encoder, or anything that
would make this package diverge from how `DiagnosingHealth` behaves in a
plain Laravel app.

## Invariants — don't change these without discussing it first

- **Checks fail by throwing.** `HealthCheck::fail()` throws
  `HealthCheckFailedException`; `degrade()` throws
  `HealthCheckDegradedException`. Nothing catches broadly and converts to a
  boolean.
- **Degraded never fails the HTTP response.** A degraded check must never
  turn the endpoint's status code into the configured problem code. Only
  `down` does that.
- **The endpoint is the framework's health route, not a second one.** The
  package must never register anything at a hardcoded `/health` path, and
  should never assume `HEALTHCHECK_PATH`/`config('healthcheck.path')` is any
  particular value — users can and do change it.
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
- Any new bundled check that catches an exception internally should route it
  through `exceptionContext()` rather than building its own context array —
  that's the one place the "don't leak trace details" rule is enforced.
- **`Middleware/Authenticate`** ([src/Middleware/Authenticate.php](src/Middleware/Authenticate.php))
  gates the endpoint behind basic auth, a header token, and an IP allowlist
  — any one configured method passing is sufficient. Each method only ever
  authenticates if it's actually configured: empty config must never match
  empty/absent credentials, and an empty allowlist must never match any IP.
  It intentionally runs the check suite before verifying credentials, and
  returns the *real* status code with an empty body on failed/missing auth
  (see `tests/Middleware/AuthenticateTest.php`) — a caller without
  credentials should learn "up or down" but nothing else. Don't "fix" this
  into a generic 401 without discussing it; it's a deliberate, tested design.

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

## Scope

- This is the 4.x line. 2.x is maintained separately for consumers who
  can't take 4.x's breaking, core-aligned design — there is no config flag
  to restore 2.x behavior (old `/health` path, old JSON shape) on 4.x.
  Point people at 2.x instead of trying to reintroduce it here.
- Laravel only — Lumen isn't supported. Use
  `Illuminate\Foundation\Application`-only APIs freely (e.g.
  `hasDebugModeEnabled()`), but prefer the plainest option that works (e.g.
  `config('app.debug')`) when both are equally correct, since it's one less
  framework-internal API to depend on.
