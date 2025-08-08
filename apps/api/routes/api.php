<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Queries\GetUserQuery;

Route::get('/user', function (Request $request) {
    return response()->json(['message' => 'Hello API']);
});

Route::get('/user/{id}', function (Request $request, string $id) {
    $getUserQuery = new GetUserQuery();
    return $getUserQuery->execute(['id' => $id]);
});

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
