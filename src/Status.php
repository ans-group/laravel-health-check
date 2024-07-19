<?php

namespace UKFast\HealthCheck;

class Status
{
    const PROBLEM = 'PROBLEM';

    const DEGRADED = 'DEGRADED';

    const OKAY = 'OK';

    protected string|null $status = null;

    protected string $name = '';

    protected string $message = '';

    /**
     * @var array<string, string|array|int>
     */
    protected array $context = [];

    /**
     * Marks the status as a problem
     */
    public function problem(string $message = ''): self
    {
        $this->status = Status::PROBLEM;
        $this->message = $message;

        return $this;
    }

    /**
     * Marks the status as degraded
     */
    public function degraded(string $message = ''): self
    {
        $this->status = Status::DEGRADED;
        $this->message = $message;

        return $this;
    }

    /**
     * Marks status as okay
     */
    public function okay(): self
    {
        $this->status = Status::OKAY;
        return $this;
    }

    /**
     * Returns the status string
     */
    public function getStatus(): string|null
    {
        return $this->status;
    }

    /**
     * Returns if the status is a problem
     */
    public function isProblem(): bool
    {
        return $this->status == Status::PROBLEM;
    }

    /**
     * Returns if the status is degraded
     */
    public function isDegraded(): bool
    {
        return $this->status == Status::DEGRADED;
    }

    /**
     * Returns if the status is okay
     */
    public function isOkay(): bool
    {
        return $this->status == Status::OKAY;
    }

    /**
     * Sets the context for the status
     *
     * @param $context array<string, string|array|int>
     */
    public function withContext(array $context): self
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Returns the status context, an array of arbitrary key/value
     * pairs to help with debugging. So long as the array can be
     * json encoded, it'll be outputted
     *
     * @return array<string, string|array|int>
     */
    public function context(): array
    {
        return $this->context;
    }

    /**
     * Sets the display name for the status, so when it's
     * being outputted in the healthcheck endpoint, this
     * is the key that the status and context will fall
     * under
     */
    public function withName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Returns the display name
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    public function message(): string
    {
        return $this->message;
    }
}
