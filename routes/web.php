<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MonitorController;


Route::get('/monitor', [MonitorController::class, 'index']);
Route::get('/monitor/data', [MonitorController::class, 'data']);