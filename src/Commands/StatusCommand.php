<?php

namespace UKFast\HealthCheck\Commands;

use Illuminate\Console\Command;
use UKFast\HealthCheck\Facade\HealthCheck;

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

        $onlyChecks = array_map('trim', explode(',', $only));
        $exceptChecks = array_map('trim', explode(',', $except));

        $problems = [];
        /** @var \UKFast\HealthCheck\HealthCheck $check */
        foreach (HealthCheck::all() as $check) {
            if ($only && !in_array($check->name(), $onlyChecks)) {
                continue;
            } elseif ($except && in_array($check->name(), $exceptChecks)) {
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
}
