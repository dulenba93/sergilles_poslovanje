<?php

namespace App\Http\Controllers;

use App\Services\FirestoreRestService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $firestore;
    protected $collection = 'product';

    public function __construct(FirestoreRestService $firestore)
    {
        $this->firestore = $firestore;
    }

    public function index()
    {
        $products = $this->firestore->getDocuments($this->collection);
        return view('product.index', compact('products'));
    }

    public function create()
    {
        return view('product.create');
    }

    public function store(Request $request)
    {
        $this->firestore->addDocument($this->collection, [
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'purchase_price' => $request->input('purchase_price'),
            'selling_price' => $request->input('selling_price'),
            'category_code' => $request->input('category_code'),
            'model_label' => $request->input('model_label'),
            'max_height' => $request->input('max_height'),
            'composition' => $request->input('composition'),
        ]);

        return redirect()->route('product.index')->with('success', 'Product added successfully.');
    }

    public function edit($id)
    {
        $product = $this->firestore->getDocument($this->collection, $id);
        return view('product.edit', compact('product', 'id'));
    }

    public function update(Request $request, $id)
    {
        $this->firestore->updateDocument($this->collection, $id, [
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'purchase_price' => $request->input('purchase_price'),
            'selling_price' => $request->input('selling_price'),
            'category_code' => $request->input('category_code'),
            'model_label' => $request->input('model_label'),
            'max_height' => $request->input('max_height'),
            'composition' => $request->input('composition'),
        ]);

        return redirect()->route('product.index')->with('success', 'Product updated successfully.');
    }

    public function destroy($id)
    {
        $this->firestore->deleteDocument($this->collection, $id);
        return redirect()->route('product.index')->with('success', 'Product deleted.');
    }
}
