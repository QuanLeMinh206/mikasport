<?php

namespace App\Http\Controllers;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $categoryId = $request->query('categoryId');

        if ($categoryId) {
            $products = Product::where('category_id', $categoryId)->get();
        } else {
            $products = Product::all(); // Lấy tất cả sản phẩm nếu không có categoryId
        }

        return response()->json($products);
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
    $category = Category::findOrFail($id);
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

public function showpro($id)
{
    // Lấy sản phẩm và các dữ liệu liên quan, sử dụng eager loading
    $product = Product::with([
        'sizes',
        'comments' => function ($query) {
            $query->orderBy('timestamp', 'desc');
        }
    ])->findOrFail($id);

    // Trả về dữ liệu dưới dạng JSON
    return response()->json([
        'status' => 'success',
        'data' => $product
    ]);
}

public function topferproduct()
{
    $top =Product::topSellingWomenProducts();
    return response()->json($top);
}


public function topProducts()
{
    $top = Product::topSellingProducts(); // mặc định lấy 10
    return response()->json($top);
}

public function saleproduct()
{
    $sale = Product::saleProducts();
    return response()->json($sale);
}

//chua xong
public function maleProducts()
{
    $products = Product::where('gender', 'Nam')->get();
    return response()->json($products);
}


public function topsellman()
{
    $fel = Product::topSellingWManProducts();
    return response()->json($fel);
}


public function topsellwomen()
{
    $fel = Product::topSellingWonmenProducts();
    return response()->json($fel);
}

public function sortByPriceAsc()
{
    $products = Product::orderBy('price', 'asc')->get();

    return response()->json([
        'products' => $products
    ]);
}

public function filterByPrice(Request $request)
{
    $min = $request->query('min', 0);
    $max = $request->query('max', 100000000); // hoặc số nào đó thật lớn

    $products = Product::whereBetween('price', [$min, $max])->get();

    return response()->json($products);
}



public function getByGender($gender)
{
    $allowedGenders = ['nam', 'nu']; // kiểm soát input tránh lỗi SQL

    if (!in_array($gender, $allowedGenders)) {
        return response()->json(['error' => 'Giới tính không hợp lệ'], 400);
    }

    $products = Product::where('gender', $gender)->get();

    return response()->json([
        'success' => true,
        'data' => $products,
    ]);
}
}