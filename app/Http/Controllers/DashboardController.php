<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Lấy các thống kê tổng quan.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats()
    {
        try {
            Log::info('Entering getStats');
            $totalOrders = Order::count();
            Log::info('Total Orders: ' . $totalOrders);
            $totalEarnings = Order::sum('total_price'); // Sử dụng đúng tên cột total_price
            Log::info('Total Earnings: ' . $totalEarnings);
            $totalUsers = User::count();
            Log::info('Total Users: ' . $totalUsers);
            $totalProducts = Product::count();
            Log::info('Total Products: ' . $totalProducts);

            return response()->json([
                'totalOrders' => $totalOrders,
                'totalEarnings' => $totalEarnings,
                'totalUsers' => $totalUsers,
                'totalProducts' => $totalProducts,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getStats: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    /**
     * Lấy doanh thu theo tháng (hoặc ngày, năm).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMonthlySales(Request $request)
    {
        try {
            $timeFilter = $request->input('timeFilter', 'month');
            $salesData = [];

            if ($timeFilter === 'day') {
                $salesData = Order::selectRaw('DATE(created_at) as name, SUM(total_price) as total') // Thay 'total' bằng 'total_price'
                    ->groupBy('name')
                    ->orderBy('name')
                    ->get();
            } elseif ($timeFilter === 'year') {
                $salesData = Order::selectRaw('YEAR(created_at) as name, SUM(total_price) as total') // Thay 'total' bằng 'total_price'
                    ->groupBy('name')
                    ->orderBy('name')
                    ->get();
            } else {
                $salesData = Order::selectRaw('MONTH(created_at) as name, SUM(total_price) as total') // Thay 'total' bằng 'total_price'
                    ->groupBy('name')
                    ->orderBy('name')
                    ->get()
                    ->map(function ($item) {
                        $item->name = Carbon::createFromDate(null, $item->name, 1)->format('M');
                        return $item;
                    });
            }

            return response()->json($salesData);
        } catch (\Exception $e) {
            Log::error('Error in getMonthlySales: ' . $e->getMessage());
return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    /**
     * Lấy doanh thu theo danh mục.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategorySales()
    {
        try {
            $categorySales = Category::with('products.sizeProducts.order_details.order')
                ->get()
                ->map(function ($category) {
                    $totalSold = $category->products->sum(function ($product) {
                        return $product->sizeProducts->sum(function ($sizeProduct) {
                            return $sizeProduct->order_details->sum('quantity');
                        });
                    });

                    return [
                        'name' => $category->name,
                        'totalSold' => $totalSold, // Thay 'orders' bằng 'totalSold'
                    ];
                });

            return response()->json($categorySales);
        } catch (\Exception $e) {
            Log::error('Error in getCategorySales: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    /**
     * Lấy các giao dịch gần đây.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRecentSales()
    {
        try {
            $recentSales = Order::with('user', 'orderDetails.sizeProduct.product')
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($order) {
                    $firstProductImage = null;
                    if ($order->orderDetails->isNotEmpty() && $order->orderDetails->first()->sizeProduct && $order->orderDetails->first()->sizeProduct->product) {
                        $firstProductImage = $order->orderDetails->first()->sizeProduct->product->image;
                    }

                    return [
                        'id' => $order->id,
                        'name' => $order->user->name,
                        'email' => $order->user->email,
                        'image' => $firstProductImage,
                        'total' => $order->total_price,
                    ];
                });

            return response()->json($recentSales);
        } catch (\Exception $e) {
            Log::error('Error in getRecentSales: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    /**
     * Lấy danh sách các sản phẩm bán chạy nhất.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTopProducts()
    {
        try {
            $topProducts = Product::withCount('orderItems')
                ->orderBy('order_items_count', 'desc')
                ->take(5)
                ->get()
                ->map(function ($product, $index) {
                    return [
                        'number' => $index + 1,
                        'image' => $product->image,
                        'name' => $product->name,
                        'description' => $product->description,
                        'price' => $product->price,
                        'status' => $product->status,
                        'rating' => $product->rating,
                    ];
                });

            return response()->json($topProducts);
        } catch (\Exception $e) {
            Log::error('Error in getTopProducts: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
}
