<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CreditReportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// rutas para reporte de creditos
Route::prefix('credit-reports')->group(function () {
    // Generate report (async)
    Route::post('/generate', [CreditReportController::class, 'generate'])
        ->name('credit-reports.generate');
    
    // Check job status
    Route::get('/status/{jobId}', [CreditReportController::class, 'status'])
        ->name('credit-reports.status');
    
    // Generate report (sync) - for testing/small datasets
    Route::post('/generate-sync', [CreditReportController::class, 'generateSync'])
        ->name('credit-reports.generate-sync');
});