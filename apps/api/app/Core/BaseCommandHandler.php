<?php

declare(strict_types=1);

namespace App\Core;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class BaseCommandHandler
{
    /**
     * Execute the command with transaction support and error handling
     */
    public function execute(mixed $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Validate the input data
            $validatedData = $this->validate($data);

            // Execute the main business logic
            $result = $this->handle($validatedData);

            // Commit the transaction
            DB::commit();

            // Fire any events after successful commit
            $this->fireEvents($result, $validatedData);

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => $this->getSuccessMessage()
            ]);

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Command execution failed', [
                'handler' => static::class,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);

            return response()->json([
                'success' => false,
                'message' => $this->getErrorMessage($e),
                'errors' => $this->formatErrors($e)
            ], $this->getErrorStatusCode($e));
        }
    }

    /**
     * Validate the input data (override in concrete handlers)
     */
    abstract protected function validate(mixed $data): array;

    /**
     * Handle the main business logic (override in concrete handlers)
     */
    abstract protected function handle(array $validatedData): mixed;

    /**
     * Fire events after successful execution (override if needed)
     */
    protected function fireEvents(mixed $result, array $validatedData): void
    {
        // Override in concrete handlers to fire domain events
    }

    /**
     * Get success message (override in concrete handlers)
     */
    protected function getSuccessMessage(): string
    {
        return 'Operation completed successfully';
    }

    /**
     * Get error message (override for custom error handling)
     */
    protected function getErrorMessage(Throwable $e): string
    {
        return app()->environment('production')
            ? 'An error occurred while processing your request'
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
