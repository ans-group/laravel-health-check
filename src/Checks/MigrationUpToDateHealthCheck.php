<?php

namespace UKFast\HealthCheck\Checks;

use Illuminate\Database\Migrations\Migrator;
use UKFast\HealthCheck\HealthCheck;
use UKFast\HealthCheck\Status;

class MigrationUpToDateHealthCheck extends HealthCheck
{
    protected $name = 'migration';

    /**
     * @var Migrator
     */
    protected $migrator;

    /**
     * @return Status
     */
    public function status()
    {
        try {
            $pendingMigrations = (array)$this->getPendingMigrations();
            $isDatabaseUptoDate = count($pendingMigrations) === 0;
            if (!$isDatabaseUptoDate) {
                return $this->problem(
                    'Not all migrations have been executed',
                    ['pending_migrations' => $pendingMigrations]
                );
            }
        } catch (\Exception $e) {
            return $this->problem('Exceptions during migrations check', [
                'exception' => $this->exceptionContext($e),
            ]);
        }

        return $this->okay();
    }

    /**
     * @return array
     */
    protected function getPendingMigrations()
    {
        $files = $this->getMigrator()->getMigrationFiles($this->getMigrationPath());
        return array_diff(array_keys($files), $this->getRanMigrations());
    }

    /**
     * Gets ran migrations with repository check
     *
     * @return array
     */
    protected function getRanMigrations()
    {
        if (!$this->getMigrator()->repositoryExists()) {
            return [];
        }

        return $this->getMigrator()->getRepository()->getRan();
    }

    /**
     * @return Migrator
     */
    protected function getMigrator()
    {
        if (is_null($this->migrator)) {
            $this->migrator = app('migrator');
        }

        return $this->migrator;
    }

    /**
     * @return string
     */
    protected function getMigrationPath()
    {
        return database_path() . DIRECTORY_SEPARATOR . 'migrations';
    }
}
