<?php

use Illuminate\Support\Facades\Route;
use App\Services\FirestoreRestService;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WorkOrderController;



Route::get('/', function () {
    return view('welcome');
});

Route::get('/work-orders/{workOrder}/pdf', [WorkOrderController::class, 'exportPdf'])
     ->name('work-orders.pdf');

     // Export profaktura (pro-forma invoice) to Excel
Route::get('/work-orders/{workOrder}/proforma', [WorkOrderController::class, 'exportProforma'])
    ->name('work-orders.proforma');