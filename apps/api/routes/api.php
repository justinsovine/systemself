<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Commands\CreateUserCommand;
use App\Queries\GetUserQuery;


Route::get('/user', function (Request $request) {
    return response()->json(['message' => 'Hello API']);
});

Route::get('/user/{id}', function (Request $request, string $id) {
    $getUserQuery = new GetUserQuery();
    return $getUserQuery->execute(['id' => $id]);
});

Route::post('/user', function (Request $request) {
    $createUserCommand = new CreateUserCommand();
    return $createUserCommand->execute($request->all());
});

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::match(['GET', 'POST'], '/trpc/{procedure}', function (Request $request, string $procedure) {
    // Handle batch requests
    if ($request->has('batch')) {
        $input = json_decode($request->input('input'), true);
        $input = $input['0'] ?? $input;
    } else {
        $input = $request->input('0.json') ?? $request->all();
    }

    return match($procedure) {
        'user.get' => (new GetUserQuery())->execute($input),
        'user.create' => (new CreateUserCommand())->execute($input),
        default => response()->json(['error' => 'Procedure not found'], 404)
    };
});
