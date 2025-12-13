<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

/**
 * Проверки жизнеспособности для балансировщика и оркестратора. 
 *
 * @todo подключить Prometheus метрики
 */
class HealthController extends Controller
{
    /**
     * Проверка доступности MySQL и Redis
     */
    public function check(): JsonResponse
    {
        $status = [
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'services' => [
                'mysql' => $this->checkMySQL(),
                'redis' => $this->checkRedis(),
            ],
        ];

        // Если хотя бы один сервис недоступен, возвращаем 503
        $hasFailure = collect($status['services'])
            ->contains(fn($service) => $service['status'] !== 'ok');

        if ($hasFailure) {
            $status['status'] = 'degraded';
            return response()->json($status, 503);
        }

        return response()->json($status);
    }

    /**
     * Проверка доступности MySQL
     */
    private function checkMySQL(): array
    {
        try {
            DB::connection()->getPdo();
            return [
                'status' => 'ok',
                'message' => 'MySQL connection successful',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'MySQL connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Проверка доступности Redis
     */
    private function checkRedis(): array
    {
        try {
            Redis::connection()->ping();
            return [
                'status' => 'ok',
                'message' => 'Redis connection successful',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Redis connection failed: ' . $e->getMessage(),
            ];
        }
    }
}

