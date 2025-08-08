<?php

use Illuminate\Support\Facades\Route;
use App\Services\FirestoreRestService;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WorkOrderExportController;


Route::get('/', function () {
    return view('welcome');
});



Route::get('/work-orders/{workOrder}/export/pdf', [WorkOrderExportController::class, 'pdf'])
    ->name('work-orders.export.pdf');

Route::get('/work-orders/{workOrder}/export/excel', [WorkOrderExportController::class, 'excel'])
    ->name('work-orders.export.excel');