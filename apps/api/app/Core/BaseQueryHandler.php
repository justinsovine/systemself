<?php

declare(strict_types=1);

namespace App\Core;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class BaseQueryHandler
{
    /**
     * Execute the query with error handling and caching support
     */
    public function execute(mixed $parameters = null): JsonResponse
    {
        try {
            // Validate query parameters
            $validatedParams = $this->validate($parameters);

            // Check cache if enabled
            if ($this->shouldCache()) {
                $cacheKey = $this->getCacheKey($validatedParams);
                $cached = cache()->get($cacheKey);

                if ($cached !== null) {
                    return response()->json([
                        'success' => true,
                        'data' => $cached,
                        'cached' => true
                    ]);
                }
            }

            // Execute the query logic
            $result = $this->handle($validatedParams);

            // Cache the result if caching is enabled
            if ($this->shouldCache()) {
                $cacheKey = $this->getCacheKey($validatedParams);
                cache()->put($cacheKey, $result, $this->getCacheDuration());
            }

            return response()->json([
                'success' => true,
                'data' => $result,
                'cached' => false
            ]);

        } catch (Throwable $e) {
            Log::error('Query execution failed', [
                'handler' => static::class,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'parameters' => $parameters
            ]);

            return response()->json([
                'success' => false,
                'message' => $this->getErrorMessage($e),
                'errors' => $this->formatErrors($e)
            ], $this->getErrorStatusCode($e));
        }
    }

    /**
     * Validate query parameters (override in concrete handlers)
     */
    abstract protected function validate(mixed $parameters): array;

    /**
     * Handle the main query logic (override in concrete handlers)
     */
    abstract protected function handle(array $validatedParams): mixed;

    /**
     * Whether this query should be cached (override if needed)
     */
    protected function shouldCache(): bool
    {
        return false;
    }

    /**
     * Generate cache key for this query (override if caching enabled)
     */
    protected function getCacheKey(array $params): string
    {
        return static::class . ':' . md5(serialize($params));
    }

    /**
     * Cache duration in seconds (override if caching enabled)
     */
    protected function getCacheDuration(): int
    {
        return 300; // 5 minutes default
    }

    /**
     * Get error message (override for custom error handling)
     */
    protected function getErrorMessage(Throwable $e): string
    {
        return app()->environment('production')
            ? 'An error occurred while retrieving data'
            : $e->getMessage();
    }

    /**
     * Format validation or other errors
     */
    protected function formatErrors(Throwable $e): array
    {
        // Override in concrete handlers for specific error formatting
        return [];
    }

    /**
     * Get HTTP status code for errors
     */
    protected function getErrorStatusCode(Throwable $e): int
    {
        return 500; // Override in concrete handlers for specific status codes
    }
}
