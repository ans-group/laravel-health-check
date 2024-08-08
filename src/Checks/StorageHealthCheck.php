<?php

namespace UKFast\HealthCheck\Checks;

use Exception;
use Illuminate\Support\Facades\Storage;
use UKFast\HealthCheck\HealthCheck;
use UKFast\HealthCheck\Status;

class StorageHealthCheck extends HealthCheck
{
    protected string $name = 'storage';

    /**
     * @var array<int, string> $workingDisks
     */
    protected array $workingDisks = [];

    /**
     * @var array<int, array<string, string>> $corruptedFiles
     */
    protected array $corruptedFiles = [];

    /**
     * @var array<int, array<string, string>> $exceptions
     */
    protected array $exceptions = [];

    public function status(): Status
    {
        $uniqueString = uniqid('laravel-health-check_', true);

        foreach ((array) config('healthcheck.storage.disks') as $disk) {
            try {
                $storage = Storage::disk($disk);

                $storage->put($uniqueString, $uniqueString);

                $contents = $storage->get($uniqueString);

                $storage->delete($uniqueString);

                if ($contents !== $uniqueString) {
                    $this->corruptedFiles[] = [
                        'disk' => $disk,
                        'incorrect_contents' => $contents,
                    ];

                    continue;
                }

                $this->workingDisks[] = $disk;
            } catch (Exception $exception) {
                $this->exceptions[] = [
                    'disk' => $disk,
                    'error' => $this->exceptionContext($exception),
                ];
            }
        }

        if (empty($this->corruptedFiles) && empty($this->exceptions)) {
            return $this->okay();
        }

        return $this->problem(
            'Some storage disks are not working',
            [
                'working' => $this->workingDisks,
                'corrupted_files' => $this->corruptedFiles,
                'exceptions' => $this->exceptions,
            ]
        );
    }
}
