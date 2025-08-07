<?php

use Illuminate\Support\Facades\Route;
use App\Services\FirestoreRestService;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;


Route::get('/', function () {
    return view('welcome');
});
