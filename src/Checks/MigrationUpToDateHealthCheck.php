<?php

declare(strict_types=1);

namespace UKFast\HealthCheck\Checks;

use Exception;
use Illuminate\Database\Migrations\Migrator;
use UKFast\HealthCheck\Exceptions\HealthCheckFailedException;
use UKFast\HealthCheck\HealthCheck;

class MigrationUpToDateHealthCheck extends HealthCheck
{
    protected string $name = 'migration';

    protected Migrator|null $migrator = null;

    public function check(): void
    {
        try {
            $pendingMigrations = $this->getPendingMigrations();
            $isDatabaseUptoDate = $pendingMigrations === [];
            if (!$isDatabaseUptoDate) {
                $this->fail(
                    'Not all migrations have been executed',
                    ['pending_migrations' => $pendingMigrations]
                );
            }
        } catch (HealthCheckFailedException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            $this->fail('Exceptions during migrations check', [
                'exception' => $this->exceptionContext($exception),
            ]);
        }
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
        $this->migrator ??= app('migrator');

        return $this->migrator;
    }

    protected function getMigrationPath(): string
    {
        return database_path() . DIRECTORY_SEPARATOR . 'migrations';
    }
}
