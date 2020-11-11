<?php

namespace UKFast\HealthCheck\Commands;

use Illuminate\Console\Command;
use UKFast\HealthCheck\Facade\HealthCheck;

class Status extends Command
{
    /**
     * @var string
     */
    protected $signature = 'health-check:status';

    /**
     * @var string
     */
    protected $description = 'Check health status';

    public function handle()
    {
        $problems = [];
        $isOkay = true;
        /** @var \UKFast\HealthCheck\HealthCheck $check */
        foreach (HealthCheck::all() as $check) {
            $status = $check->status();

            if (!$status->isOkay()) {
                $problems[] = [$check->name(), $status->name(), $status->message()];

                if ($isOkay) {
                    $isOkay = false;
                }
            }
        }

        if (!$isOkay) {
            $this->table(['name', 'status', 'message'], $problems);
        }

        return !$isOkay;
    }
}
