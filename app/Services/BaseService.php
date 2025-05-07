<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BaseService
{
    public function __construct()
    {
    }

    /**
     * Write debug log.
     *
     * @param string      $message
     * @param null|string $code
     */
    protected function logDebug($message, $code = null): void
    {
        // Log context
        Log::withContext([
            'code' => $code,
            ...$this->getLogCallerInfo(),
        ]);

        Log::debug($message);
    }

    /**
     * Write info log.
     *
     * @param string      $message
     * @param null|string $code
     */
    protected function logInfo($message, $code = null): void
    {
        // Log context
        Log::withContext([
            'code' => $code,
            ...$this->getLogCallerInfo(),
        ]);

        Log::info($message);
    }

    /**
     * Write info log.
     *
     * @param string      $message
     */
    protected function logError($message): void
    {
        Log::error($message);
    }

    /**
     * Write warning log.
     *
     * @param string      $message
     * @param null|string $code
     */
    protected function logWarning($message, $code = null): void
    {
        // Log context
        Log::withContext([
            'code' => $code,
            ...$this->getLogCallerInfo(),
        ]);

        Log::warning($message);
    }

    /**
     * Get log caller info.
     * @author Sakina Maezawa
     * @return array
     */
    private function getLogCallerInfo()
    {
        return [
            'call' => debug_backtrace()[1]['file'] . '->' . debug_backtrace()[2]['function'] . ':' . debug_backtrace()[1]['line'],
            'called_arg' => debug_backtrace()[2]['args'],
            'parent_call' => debug_backtrace()[2]['file'] . '->' . debug_backtrace()[3]['function'] . ':' . debug_backtrace()[2]['line'],
            'parent_called_arg' => debug_backtrace()[3]['args'],
        ];
    }

    /**
     * @throws AuthorizationException
     */
    public function createJobKey(string $classes)
    {
        $jobType = class_basename($classes);
        $runningJobs = Cache::get('running_jobs', []);
        if (in_array($jobType, $runningJobs, true)) {
            throw new AuthorizationException('Vui lòng đợi quá trình tạo luận giải hoàn thành!');
        }

        $runningJobs[] = $jobType;
        Cache::put('running_jobs', $runningJobs, 3600);

        return [$jobType, $runningJobs];
    }
}