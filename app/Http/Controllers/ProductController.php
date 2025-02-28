<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    return response()->json(Product::with('category', 'sizes')->get());
}

public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:50',
        'category_id' => 'required|exists:categories,category_id',
        'price' => 'required|numeric|min:0',
    ]);

    $product = Product::create($request->all());

    if ($request->has('sizes')) {
        $product->sizes()->attach($request->sizes);
    }

    return response()->json($product->load('category', 'sizes'), 201);
}

public function show($id)
{
    $product = Product::with('category', 'sizes')->find($id);
    return $product ? response()->json($product) : response()->json(['message' => 'Not found'], 404);
}

public function update(Request $request, $id)
{
    $product = Product::find($id);
    if (!$product) return response()->json(['message' => 'Not found'], 404);

    $product->update($request->all());

    if ($request->has('sizes')) {
        $product->sizes()->sync($request->sizes);
    }

    return response()->json($product->load('category', 'sizes'));
}

public function destroy($id)
{
    $product = Product::find($id);
    if (!$product) return response()->json(['message' => 'Not found'], 404);

    $product->delete();
    return response()->json(['message' => 'Deleted']);
}


// sort
public function search(Request $request)
{
    try {
        $query = Product::query();

        // Kiểm tra nếu request có tham số 'name' và không rỗng
        if ($request->has('name') && !empty($request->name)) {
            $query->where('name', 'LIKE', '%' . $request->name . '%');
        }

        // Trả về danh sách sản phẩm
        return response()->json($query->paginate(10));
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}




}