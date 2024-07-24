<?php

namespace Tests\Checks;

use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Database\Migrations\Migrator;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;
use UKFast\HealthCheck\Checks\MigrationUpToDateHealthCheck;

class MigrationUpToDateHealthCheckTest extends TestCase
{
    protected MockObject $healthCheck;

    protected MockObject $migratorMock;

    protected MockObject $migrationRepository;


    public function prepare(): void
    {
        $this->healthCheck = $this->getMockBuilder(MigrationUpToDateHealthCheck::class)
            ->onlyMethods(['getMigrator', 'getMigrationPath'])->getMock();

        $this->migratorMock = $this->getMockBuilder(Migrator::class)
            ->onlyMethods(['getRepository','repositoryExists','getMigrationFiles'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->migrationRepository = $this->getMockBuilder(MigrationRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->migratorMock->expects(($this->any()))
            ->method('getRepository')
            ->willReturn($this->migrationRepository);

        $this->migratorMock->expects($this->any())
            ->method('repositoryExists')
            ->willReturn(true);

        $this->healthCheck->expects($this->any())
            ->method('getMigrator')
            ->willReturn($this->migratorMock);
    }

    public function testCanReturnFalseWhenSchemaIsOutdated(): void
    {
        $this->prepare();
        $this->migratorMock->expects($this->once())
            ->method('getMigrationFiles')
            ->willReturn([
                'missing_migration.php' => 2
            ]);

        $this->migrationRepository->expects($this->once())
            ->method('getRan')
            ->willReturn([]);

        $status = $this->healthCheck->status();
        $this->assertFalse($status->isOkay());
        $this->assertSame(['pending_migrations' => ['missing_migration.php']], $status->context());
    }

    public function testCanReturnFalseWhenRanMigrationCouldNotBeRetrieved(): void
    {
        $this->prepare();
        $this->migratorMock->expects($this->once())
            ->method('getMigrationFiles')
            ->willReturn([
                'executed_migration.php' => 2
            ]);

        $this->migratorMock->expects($this->once())
            ->method('repositoryExists')
            ->willReturn(false);

        $this->migrationRepository->expects($this->any())
            ->method('getRan')
            ->willReturn([]);

        $this->assertFalse($this->healthCheck->status()->isOkay());
    }

    public function testCanReturnTrueWhenMigrationsAreUpToDate(): void
    {
        $this->prepare();
        $this->migratorMock->expects($this->once())
            ->method('getMigrationFiles')
            ->willReturn([
                'executed_migration.php' => 2
            ]);

        $this->migrationRepository->expects($this->any())
            ->method('getRan')
            ->willReturn([
                'executed_migration.php'
            ]);

        $this->assertTrue($this->healthCheck->status()->isOkay());
    }
}
