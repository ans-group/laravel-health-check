<?php

namespace UKFast\HealthCheck\Checks;

use Exception;
use Illuminate\Database\Migrations\Migrator;
use UKFast\HealthCheck\HealthCheck;
use UKFast\HealthCheck\Status;

class MigrationUpToDateHealthCheck extends HealthCheck
{
    protected string $name = 'migration';

    protected Migrator|null $migrator = null;

    public function status(): Status
    {
        try {
            $pendingMigrations = $this->getPendingMigrations();
            $isDatabaseUptoDate = $pendingMigrations === [];
            if (!$isDatabaseUptoDate) {
                return $this->problem(
                    'Not all migrations have been executed',
                    ['pending_migrations' => $pendingMigrations]
                );
            }
        } catch (Exception $exception) {
            return $this->problem('Exceptions during migrations check', [
                'exception' => $this->exceptionContext($exception),
            ]);
        }

        return $this->okay();
    }

    /**
     * @return array<int, string>
     */
    protected function getPendingMigrations(): array
    {
        $files = $this->getMigrator()->getMigrationFiles($this->getMigrationPath());
        return array_diff(array_keys($files), $this->getRanMigrations());
    }

    /**
     * Gets ran migrations with repository check
     * @return array<int, string>
     *
     */
    protected function getRanMigrations(): array
    {
        if (!$this->getMigrator()->repositoryExists()) {
            return [];
        }

        return $this->getMigrator()->getRepository()->getRan();
    }

    protected function getMigrator(): Migrator
    {
        if (is_null($this->migrator)) {
            $this->migrator = app('migrator');
        }

        return $this->migrator;
    }

    protected function getMigrationPath(): string
    {
        return database_path() . DIRECTORY_SEPARATOR . 'migrations';
    }
}
