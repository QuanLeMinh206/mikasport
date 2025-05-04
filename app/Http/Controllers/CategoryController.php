<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Product;
class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Category::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:categories'
        ]);

        $category = Category::create(['name' => $request->name]);
        return response()->json($category, 201);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $category = Category::findOrFail($id);
    $products = Product::where('category_id', $id)->get();
        return $category ? response()->json($category) : response()->json(['message' => 'Not found'], 404);
    }




    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Not found'], 404);
        }


        $category->update($request->only(['name']));

        return response()->json($category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $category = Category::find($id);
        if (!$category) return response()->json(['message' => 'Not found'], 404);

        $category->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function showcate($id)
    {
        try {
            // Thực hiện truy vấn JOIN giữa categories và products
            $products = DB::table('categories')
                ->join('products', 'categories.category_id', '=', 'products.category_id')
                ->where('categories.category_id', $id)
                ->select('categories.*', 'products.*')
                ->get();

            // Kiểm tra kết quả
            return response()->json($products);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi máy chủ nội bộ'], 500);
        }
    }



        public function getProducts($id)
        {
            $category = Category::with(['products' => function ($query) {
                $query->select(
                    'product_id',
                    'name',
                    'description',
                    'price',
                    'price_sale',
                    'image',
                    'category_id'
                );
            }])->find($id);

            if (!$category) {
                return response()->json(['message' => 'Category not found'], 404);
            }

            return response()->json([
                'category' => $category->name,
                'products' => $category->products
            ]);
        }





public function catecolor(Request $request)
{
    $color=Product::getColors();
    return response()->json($color);
}


public function filterByColor(Request $request)
{
    $color = $request->query('color');

    // Nếu không có color (null hoặc rỗng) thì trả về tất cả sản phẩm
    if (empty($color)) {
        $products = Product::all();
    } else {
        $products = Product::where('color', $color)->get();
    }

    return response()->json($products);
}

public function Size(Request $request)
{
    $size = $request->query('Size');
}


public function filterBySize(Request $request)
{
    $sizeName = $request->query('size'); // ví dụ ?size=M

    $products = Product::whereHas('sizes', function ($query) use ($sizeName) {
        $query->where('name', $sizeName);
    })->get();

    return response()->json($products);
}




}
