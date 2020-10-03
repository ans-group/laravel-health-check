<?php

namespace UKFast\HealthCheck\Checks;

use Exception;
use Illuminate\Support\Facades\Storage;
use UKFast\HealthCheck\HealthCheck;

class StorageHealthCheck extends HealthCheck
{
    protected $name = 'storage';

    protected $workingDisks = [];
    
    protected $corruptedFiles = [];

    protected $exceptions = [];

    public function status()
    {
        $uniqueString = uniqid('laravel-health-check_');

        foreach (config('healthcheck.storage.disks') as $disk) {
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
            } catch (Exception $e) {
                $this->exceptions[] = [
                    'disk' => $disk,
                    'error' => $this->exceptionContext($e),
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
