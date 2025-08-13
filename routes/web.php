<?php

use Illuminate\Support\Facades\Route;
use App\Services\FirestoreRestService;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\DashboardExportController;
use App\Http\Controllers\SalesExportController;
use App\Http\Controllers\BusinessOverviewExportController;
use App\Http\Controllers\WorkOrderViewController;




Route::get('/work-orders/{workOrder}/expand', [WorkOrderViewController::class, 'show'])
    ->name('workorders.expand');

Route::get('/business-overview/export/pdf', [BusinessOverviewExportController::class, 'pdf'])
    ->name('business.overview.export.pdf');

Route::get('/business-overview/export/excel', [BusinessOverviewExportController::class, 'excel'])
    ->name('business.overview.export.excel');

Route::get('/sales/export/pdf', [SalesExportController::class, 'pdf'])->name('sales.export.pdf');
Route::get('/sales/export/excel', [SalesExportController::class, 'excel'])->name('sales.export.excel');

Route::get('/dashboard/export/pdf', [DashboardExportController::class, 'pdf'])
    ->name('dashboard.export.pdf');

Route::get('/dashboard/export/excel', [DashboardExportController::class, 'excel'])
    ->name('dashboard.export.excel');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/work-orders/{workOrder}/pdf', [WorkOrderController::class, 'exportPdf'])
     ->name('work-orders.pdf');

     // Export profaktura (pro-forma invoice) to Excel
Route::get('/work-orders/{workOrder}/proforma', [WorkOrderController::class, 'exportProforma'])
    ->name('work-orders.proforma');