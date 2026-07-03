<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => 'PHP Users REST API',
        'docs' => url('/api/docs'),
        'schema' => url('/api/schema'),
        'health' => url('/up'),
    ]);
});
