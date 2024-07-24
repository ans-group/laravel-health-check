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
            ->map(fn(string $check): string => trim($check))
            ->filter();

        $exceptChecks = Str::of($except)->explode(',')
            ->map(fn(string $check): string => trim($check))
            ->filter();

        /**
         * @var Collection<string, array<int, string>> $problems
         */
        $problems = collect();

        /**
         * @var Check $check
         */
        foreach (HealthCheck::all() as $check) {
            if ($this->shouldSkipHealthCheck($check, $onlyChecks, $exceptChecks)) {
                continue;
            }

            $status = $check->status();

            if ($status->isProblem()) {
                $problems->push([
                    $check->name(),
                    $status->name(),
                    $status->message(),
                ]);
            }
        }

        if ($problems->isEmpty() === false) {
            $this->table(['name', 'status', 'message'], $problems);

            return 1;
        }

        $this->info('All checks passed successfully');

        return 0;
    }

    private function shouldSkipHealthCheck(Check $check, Collection $onlyChecks, Collection $exceptChecks): bool
    {
        if ($onlyChecks->isNotEmpty() && $onlyChecks->contains($check->name()) === false) {
            return true;
        }
        return $exceptChecks->isNotEmpty() && $exceptChecks->contains($check->name());
    }
}
