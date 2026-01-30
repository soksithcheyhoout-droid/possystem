<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    /**
     * Get all products with optional filters
     */
    public function getAllProducts(array $filters = []): Collection
    {
        $query = Product::with('category');

        // Apply filters
        if (isset($filters['active'])) {
            $query->where('is_active', $filters['active']);
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'LIKE', "%{$filters['search']}%")
                  ->orWhere('barcode', 'LIKE', "%{$filters['search']}%");
            });
        }

        if (isset($filters['low_stock']) && $filters['low_stock']) {
            $query->whereRaw('stock_quantity <= min_stock');
        }

        if (isset($filters['has_discount']) && $filters['has_discount']) {
            $query->where('discount_percentage', '>', 0);
        }

        return $query->get();
    }

    /**
     * Get products with pagination
     */
    public function getProductsPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Product::with('category');

        // Apply same filters as getAllProducts
        if (isset($filters['active'])) {
            $query->where('is_active', $filters['active']);
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'LIKE', "%{$filters['search']}%")
                  ->orWhere('barcode', 'LIKE', "%{$filters['search']}%");
            });
        }

        if (isset($filters['low_stock']) && $filters['low_stock']) {
            $query->whereRaw('stock_quantity <= min_stock');
        }

        if (isset($filters['has_discount']) && $filters['has_discount']) {
            $query->where('discount_percentage', '>', 0);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get single product by ID
     */
    public function getProductById(int $id): ?Product
    {
        return Product::with('category')->find($id);
    }

    /**
     * Get product by barcode
     */
    public function getProductByBarcode(string $barcode): ?Product
    {
        return Product::with('category')->where('barcode', $barcode)->first();
    }

    /**
     * Get products by category
     */
    public function getProductsByCategory(int $categoryId): Collection
    {
        return Product::with('category')
                     ->where('category_id', $categoryId)
                     ->where('is_active', true)
                     ->get();
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts(): Collection
    {
        return Product::with('category')
                     ->whereRaw('stock_quantity <= min_stock')
                     ->get();
    }

    /**
     * Get products with discounts
     */
    public function getDiscountedProducts(): Collection
    {
        return Product::with('category')
                     ->where('discount_percentage', '>', 0)
                     ->get();
    }

    /**
     * Get active products for POS
     */
    public function getActiveProductsForPOS(): Collection
    {
        return Product::with('category')
                     ->where('is_active', true)
                     ->where('stock_quantity', '>', 0)
                     ->orderBy('name')
                     ->get();
    }

    /**
     * Delete product by ID
     */
    public function deleteProduct(int $id): bool
    {
        $product = Product::find($id);
        
        if (!$product) {
            return false;
        }

        return $this->deleteProductModel($product);
    }

    /**
     * Delete product model instance
     */
    public function deleteProductModel(Product $product): bool
    {
        try {
            // Delete associated image file if it exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            
            // Delete the product from database
            $product->delete();
            
            return true;
        } catch (\Exception $e) {
            // Log the error if needed
            \Log::error('Failed to delete product: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Bulk delete products
     */
    public function bulkDeleteProducts(array $productIds): int
    {
        $deletedCount = 0;
        
        foreach ($productIds as $id) {
            if ($this->deleteProduct($id)) {
                $deletedCount++;
            }
        }
        
        return $deletedCount;
    }

    /**
     * Soft delete product (mark as inactive instead of deleting)
     */
    public function softDeleteProduct(int $id): bool
    {
        $product = Product::find($id);
        
        if (!$product) {
            return false;
        }

        return $product->update(['is_active' => false]);
    }

    /**
     * Search products
     */
    public function searchProducts(string $search): Collection
    {
        return Product::with('category')
                     ->where('name', 'LIKE', "%{$search}%")
                     ->orWhere('barcode', 'LIKE', "%{$search}%")
                     ->orWhere('description', 'LIKE', "%{$search}%")
                     ->get();
    }

    /**
     * Get product statistics
     */
    public function getProductStats(): array
    {
        return [
            'total_products' => Product::count(),
            'active_products' => Product::where('is_active', true)->count(),
            'inactive_products' => Product::where('is_active', false)->count(),
            'low_stock_products' => Product::whereRaw('stock_quantity <= min_stock')->count(),
            'discounted_products' => Product::where('discount_percentage', '>', 0)->count(),
            'out_of_stock_products' => Product::where('stock_quantity', 0)->count(),
        ];
    }
}