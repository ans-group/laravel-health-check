<?php

declare(strict_types=1);

namespace UKFast\HealthCheck;

class HealthReport
{
    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $checks = [];

    /**
     * @param array<int|string, mixed> $context
     */
    public function recordUp(string $name, array $context = []): void
    {
        $this->checks[$name] = $this->entry('up', context: $context);
    }

    /**
     * @param array<int|string, mixed> $context
     */
    public function recordDegraded(string $name, string $message = '', array $context = []): void
    {
        $this->checks[$name] = $this->entry('degraded', $message, $context);
    }

    /**
     * @param array<int|string, mixed> $context
     */
    public function recordDown(string $name, string $message = '', array $context = []): void
    {
        $this->checks[$name] = $this->entry('down', $message, $context);
    }

    public function statusFor(string $name): string|null
    {
        return $this->checks[$name]['status'] ?? null;
    }

    public function isDown(): bool
    {
        foreach ($this->checks as $check) {
            if ($check['status'] === 'down') {
                return true;
            }
        }

        return false;
    }

    public function overallStatus(): string
    {
        return $this->isDown() ? 'down' : 'up';
    }

    /**
     * Matches Laravel's own health route contract (`{"status": "up"|"down"}`)
     * plus a `checks` breakdown as this package's extension. No aggregate
     * `message` — that's already in `checks`, and native has no analogous
     * field either.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'status' => $this->overallStatus(),
            'checks' => $this->checks,
        ];
    }

    /**
     * A one-line summary of why the report is down, for logging — not part
     * of the JSON/HTML response.
     */
    public function summary(): string
    {
        $messages = [];

        foreach ($this->checks as $name => $check) {
            if ($check['status'] === 'down') {
                $messages[] = $name . ': ' . ($check['message'] ?? 'failed');
            }
        }

        return implode('; ', $messages);
    }

    /**
     * @param array<int|string, mixed> $context
     * @return array<string, mixed>
     */
    protected function entry(string $status, string $message = '', array $context = []): array
    {
        $entry = ['status' => $status];

        if ($message !== '') {
            $entry['message'] = $message;
        }

        if ($context !== []) {
            $entry['context'] = $context;
        }

        return $entry;
    }
}
