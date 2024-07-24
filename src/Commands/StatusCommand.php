<?php

namespace UKFast\HealthCheck\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use UKFast\HealthCheck\Facade\HealthCheck;
use UKFast\HealthCheck\HealthCheck as Check;

class StatusCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = '
        health-check:status 
        {--only= : comma separated checks names to run}
        {--except= : comma separated checks names to skip}
    ';

    /**
     * @var string
     */
    protected $description = 'Check health status';

    public function handle(): int
    {
        $only = (string) $this->option('only');
        $except = (string) $this->option('except');

        if ($only && $except) {
            $this->error('Pass --only OR --except, but not both!');

            return 1;
        }

        $onlyChecks = Str::of($only)->explode(',')
            ->map(fn(string $check) => trim($check));

        $exceptChecks = Str::of($except)->explode(',')
            ->map(fn(string $check) => trim($check));

        $problems = [];
        /** @var \UKFast\HealthCheck\HealthCheck $check */
        foreach (HealthCheck::all() as $check) {
            if ($this->shouldSkipHealthCheck($check, $onlyChecks, $exceptChecks)) {
                continue;
            }

            $status = $check->status();

            if ($status->isProblem()) {
                $problems[] = [$check->name(), $status->name(), $status->message()];
            }
        }

        $isOkay = empty($problems);

        if ($isOkay === false) {
            $this->table(['name', 'status', 'message'], $problems);
        }

        $this->info('All checks passed successfully');

        return $isOkay ? 0 : 1;
    }

    private function shouldSkipHealthCheck(Check $check, Collection $onlyChecks, Collection $exceptChecks): bool
    {
        if ($onlyChecks->isNotEmpty() && $onlyChecks->contains($check->name()) === false) {
            return true;
        }

        if ($exceptChecks->isNotEmpty() && $exceptChecks->contains($check->name())) {
            return true;
        }

        return false;
    }
}
