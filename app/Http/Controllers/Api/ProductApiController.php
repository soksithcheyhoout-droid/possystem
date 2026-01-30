<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductApiController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Get all products with optional filters
     * GET /api/products
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [];
        
        if ($request->has('active')) {
            $filters['active'] = $request->boolean('active');
        }
        
        if ($request->has('category_id')) {
            $filters['category_id'] = $request->category_id;
        }
        
        if ($request->has('search')) {
            $filters['search'] = $request->search;
        }
        
        if ($request->has('low_stock')) {
            $filters['low_stock'] = $request->boolean('low_stock');
        }
        
        if ($request->has('has_discount')) {
            $filters['has_discount'] = $request->boolean('has_discount');
        }

        $products = $this->productService->getAllProducts($filters);
        
        return response()->json([
            'success' => true,
            'data' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'barcode' => $product->barcode,
                    'price' => $product->price,
                    'discount_percentage' => $product->discount_percentage,
                    'discounted_price' => $product->discounted_price,
                    'final_price' => $product->getFinalPrice(),
                    'cost' => $product->cost,
                    'stock_quantity' => $product->stock_quantity,
                    'min_stock' => $product->min_stock,
                    'is_active' => $product->is_active,
                    'is_low_stock' => $product->isLowStock(),
                    'has_discount' => $product->hasDiscount(),
                    'category' => $product->category ? [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                    ] : null,
                    'image_url' => $product->image ? asset('storage/' . $product->image) : null,
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                ];
            }),
            'count' => $products->count()
        ]);
    }

    /**
     * Get single product by ID
     * GET /api/products/{id}
     */
    public function show(int $id): JsonResponse
    {
        $product = $this->productService->getProductById($id);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'barcode' => $product->barcode,
                'price' => $product->price,
                'discount_percentage' => $product->discount_percentage,
                'discounted_price' => $product->discounted_price,
                'final_price' => $product->getFinalPrice(),
                'cost' => $product->cost,
                'stock_quantity' => $product->stock_quantity,
                'min_stock' => $product->min_stock,
                'is_active' => $product->is_active,
                'is_low_stock' => $product->isLowStock(),
                'has_discount' => $product->hasDiscount(),
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                ] : null,
                'image_url' => $product->image ? asset('storage/' . $product->image) : null,
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
            ]
        ]);
    }

    /**
     * Search products
     * GET /api/products/search?q=search_term
     */
    public function search(Request $request): JsonResponse
    {
        $search = $request->get('q', '');
        
        if (empty($search)) {
            return response()->json([
                'success' => false,
                'message' => 'Search query is required'
            ], 400);
        }

        $products = $this->productService->searchProducts($search);
        
        return response()->json([
            'success' => true,
            'data' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'barcode' => $product->barcode,
                    'price' => $product->getFinalPrice(),
                    'stock_quantity' => $product->stock_quantity,
                    'category' => $product->category->name ?? 'No Category',
                    'image_url' => $product->image ? asset('storage/' . $product->image) : null,
                ];
            }),
            'count' => $products->count()
        ]);
    }

    /**
     * Get products by category
     * GET /api/products/category/{categoryId}
     */
    public function getByCategory(int $categoryId): JsonResponse
    {
        $products = $this->productService->getProductsByCategory($categoryId);
        
        return response()->json([
            'success' => true,
            'data' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'barcode' => $product->barcode,
                    'price' => $product->getFinalPrice(),
                    'stock_quantity' => $product->stock_quantity,
                    'image_url' => $product->image ? asset('storage/' . $product->image) : null,
                ];
            }),
            'count' => $products->count()
        ]);
    }

    /**
     * Get low stock products
     * GET /api/products/low-stock
     */
    public function lowStock(): JsonResponse
    {
        $products = $this->productService->getLowStockProducts();
        
        return response()->json([
            'success' => true,
            'data' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'stock_quantity' => $product->stock_quantity,
                    'min_stock' => $product->min_stock,
                    'category' => $product->category->name ?? 'No Category',
                ];
            }),
            'count' => $products->count()
        ]);
    }

    /**
     * Get product statistics
     * GET /api/products/stats
     */
    public function stats(): JsonResponse
    {
        $stats = $this->productService->getProductStats();
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Delete single product
     * DELETE /api/products/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $success = $this->productService->deleteProduct($id);
        
        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product or product not found'
            ], 404);
        }
    }

    /**
     * Bulk delete products
     * DELETE /api/products/bulk
     * Body: {"product_ids": [1, 2, 3]}
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'integer|exists:products,id'
        ]);

        $deletedCount = $this->productService->bulkDeleteProducts($request->product_ids);
        
        return response()->json([
            'success' => true,
            'message' => "Successfully deleted {$deletedCount} products",
            'deleted_count' => $deletedCount
        ]);
    }

    /**
     * Soft delete product (mark as inactive)
     * PATCH /api/products/{id}/deactivate
     */
    public function softDestroy(int $id): JsonResponse
    {
        $success = $this->productService->softDeleteProduct($id);
        
        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Product deactivated successfully'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate product or product not found'
            ], 404);
        }
    }
}