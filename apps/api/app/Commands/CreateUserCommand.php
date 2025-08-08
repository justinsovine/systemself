<?php

declare(strict_types=1);

namespace App\Commands;

use App\Core\BaseCommandHandler;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CreateUserCommand extends BaseCommandHandler
{
    /**
     * Validate the input data
     * @throws ValidationException
     */
    protected function validate(mixed $data): array
    {
        if (!is_array($data)) {
            throw ValidationException::withMessages([
                'data' => ['Invalid data format']
            ]);
        }

        // Basic validation - convert this to Laravel's validator
        $required = ['name', 'email', 'password'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw ValidationException::withMessages([
                    $field => ["The {$field} field is required"]
                ]);
            }
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email format']
            ]);
        }

        if (User::where('email', $data['email'])->exists()) {
            throw ValidationException::withMessages([
                'email' => ['Email already exists']
            ]);
        }

        return [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password']
        ];
    }

    /**
     * Handle the command execution
     */
    protected function handle(array $validatedData): array
    {
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password'])
        ]);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at->toISOString()
        ];
    }

    /**
     * Success message for user creation
     */
    protected function getSuccessMessage(): string
    {
        return 'User created successfully';
    }

    /**
     * Custom error status codes
     */
    protected function getErrorStatusCode(\Throwable $e): int
    {
        if ($e instanceof ValidationException) {
            return 422;
        }

        return 500;
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
