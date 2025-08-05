<?php

use Illuminate\Support\Facades\Route;
use App\Services\FirestoreRestService;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-rest-proizvod', function (FirestoreRestService $firestore) {
    return $firestore->addDocument('proizvod', [
        'sifra' => 'TEST-RS',
        'naziv' => 'Test REST proizvod',
        'opis' => 'Dodato preko REST API-ja',
        'nabavna_cena' => 1000,
        'prodajna_cena' => 1800,
        'sifra_kategorije' => 'Test',
        'oznaka_modela' => 'REST-001',
        'max_visina' => 200,
        'sastav' => 'pamuk'
    ]);
});

