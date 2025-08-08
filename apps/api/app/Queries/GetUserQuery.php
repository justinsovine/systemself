<?php

declare(strict_types=1);

namespace App\Queries;

use App\Core\BaseQueryHandler;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class GetUserQuery extends BaseQueryHandler
{
    /**
     * Validate the query parameters
     */
    protected function validate(mixed $parameters): array
    {
        if (!is_array($parameters)) {
            throw ValidationException::withMessages([
                'id' => ['User ID is required']
            ]);
        }

        $userId = $parameters['id'] ?? null;

        if (!$userId || !is_numeric($userId)) {
            throw ValidationException::withMessages([
                'id' => ['Valid user ID is required']
            ]);
        }

        return [
            'id' => (int) $userId
        ];
    }

    /**
     * Handle the query execution
     */
    protected function handle(array $validatedParams): array
    {
        $user = User::find($validatedParams['id']);

        if (!$user) {
            throw new \Exception('User not found', 404);
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at?->toISOString(),
            'created_at' => $user->created_at->toISOString(),
            'updated_at' => $user->updated_at->toISOString(),
        ];
    }

    /**
     * Enable caching for user queries
     */
    protected function shouldCache(): bool
    {
        return true;
    }

    /**
     * Cache user data for 10 minutes
     */
    protected function getCacheDuration(): int
    {
        return 600; // 10 minutes
    }

    /**
     * Custom error status code handling
     */
    protected function getErrorStatusCode(\Throwable $e): int
    {
        return match ($e->getCode()) {
            404 => 404,
            default => 500
        };
    }

    /**
     * Format validation errors
     */
    protected function formatErrors(\Throwable $e): array
    {
        if ($e instanceof ValidationException) {
            return $e->errors();
        }

        return [];
    }
}
