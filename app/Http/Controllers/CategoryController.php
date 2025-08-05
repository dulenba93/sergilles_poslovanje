<?php

namespace App\Http\Controllers;

use App\Services\FirestoreRestService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $firestore;
    protected $collection = 'kategorija_proizvoda';

    public function __construct(FirestoreRestService $firestore)
    {
        $this->firestore = $firestore;
    }

    public function index()
    {
        return $this->firestore->listDocuments($this->collection);
    }

    public function show($id)
    {
        return $this->firestore->getDocument($this->collection, $id);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'naziv' => 'required|string',
            'opis' => 'nullable|string',
        ]);

        return $this->firestore->addDocument($this->collection, $validated);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'naziv' => 'sometimes|string',
            'opis' => 'sometimes|string',
        ]);

        return $this->firestore->updateDocument($this->collection, $id, $validated);
    }

    public function destroy($id)
    {
        return $this->firestore->deleteDocument($this->collection, $id);
    }
}
