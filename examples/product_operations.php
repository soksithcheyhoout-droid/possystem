<?php

/**
 * Examples of Product Operations
 * This file shows how to delete products and get products from database
 */

use App\Models\Product;
use App\Services\ProductService;

// Initialize the service
$productService = new ProductService();

// ============================================================================
// GETTING PRODUCTS FROM DATABASE
// ============================================================================

// 1. Get all products
$allProducts = $productService->getAllProducts();
echo "Total products: " . $allProducts->count() . "\n";

// 2. Get products with filters
$activeProducts = $productService->getAllProducts(['active' => true]);
echo "Active products: " . $activeProducts->count() . "\n";

// 3. Get products by category
$categoryProducts = $productService->getProductsByCategory(1);
echo "Products in category 1: " . $categoryProducts->count() . "\n";

// 4. Get single product by ID
$product = $productService->getProductById(1);
if ($product) {
    echo "Product found: " . $product->name . "\n";
    echo "Price: $" . $product->getFinalPrice() . "\n";
    echo "Stock: " . $product->stock_quantity . "\n";
    echo "Category: " . $product->category->name . "\n";
} else {
    echo "Product not found\n";
}

// 5. Get product by barcode
$product = $productService->getProductByBarcode('1234567890');
if ($product) {
    echo "Product found by barcode: " . $product->name . "\n";
}

// 6. Search products
$searchResults = $productService->searchProducts('milk');
echo "Search results for 'milk': " . $searchResults->count() . "\n";

// 7. Get low stock products
$lowStockProducts = $productService->getLowStockProducts();
echo "Low stock products: " . $lowStockProducts->count() . "\n";
foreach ($lowStockProducts as $product) {
    echo "- {$product->name}: {$product->stock_quantity} left (min: {$product->min_stock})\n";
}

// 8. Get discounted products
$discountedProducts = $productService->getDiscountedProducts();
echo "Discounted products: " . $discountedProducts->count() . "\n";
foreach ($discountedProducts as $product) {
    echo "- {$product->name}: {$product->discount_percentage}% off\n";
}

// 9. Get products for POS (active with stock)
$posProducts = $productService->getActiveProductsForPOS();
echo "Products available for POS: " . $posProducts->count() . "\n";

// 10. Get products with pagination
$paginatedProducts = $productService->getProductsPaginated(10);
echo "Page 1 of products (10 per page): " . $paginatedProducts->count() . "\n";
echo "Total pages: " . $paginatedProducts->lastPage() . "\n";

// 11. Get products with multiple filters
$filteredProducts = $productService->getAllProducts([
    'active' => true,
    'search' => 'coca',
    'has_discount' => true
]);
echo "Active products with 'coca' in name that have discounts: " . $filteredProducts->count() . "\n";

// 12. Get product statistics
$stats = $productService->getProductStats();
echo "Product Statistics:\n";
echo "- Total: " . $stats['total_products'] . "\n";
echo "- Active: " . $stats['active_products'] . "\n";
echo "- Inactive: " . $stats['inactive_products'] . "\n";
echo "- Low Stock: " . $stats['low_stock_products'] . "\n";
echo "- Discounted: " . $stats['discounted_products'] . "\n";
echo "- Out of Stock: " . $stats['out_of_stock_products'] . "\n";

// ============================================================================
// DELETING PRODUCTS
// ============================================================================

// 1. Delete product by ID
$productId = 1;
$success = $productService->deleteProduct($productId);
if ($success) {
    echo "Product {$productId} deleted successfully\n";
} else {
    echo "Failed to delete product {$productId}\n";
}

// 2. Delete product using model instance
$product = Product::find(2);
if ($product) {
    $success = $productService->deleteProductModel($product);
    if ($success) {
        echo "Product '{$product->name}' deleted successfully\n";
    } else {
        echo "Failed to delete product '{$product->name}'\n";
    }
}

// 3. Bulk delete products
$productIds = [3, 4, 5];
$deletedCount = $productService->bulkDeleteProducts($productIds);
echo "Bulk delete: {$deletedCount} products deleted\n";

// 4. Soft delete (mark as inactive instead of deleting)
$productId = 6;
$success = $productService->softDeleteProduct($productId);
if ($success) {
    echo "Product {$productId} deactivated successfully\n";
} else {
    echo "Failed to deactivate product {$productId}\n";
}

// ============================================================================
// DIRECT ELOQUENT QUERIES (Alternative Methods)
// ============================================================================

// Get all products directly
$products = Product::all();

// Get products with category relationship
$products = Product::with('category')->get();

// Get active products
$products = Product::where('is_active', true)->get();

// Get products by category
$products = Product::where('category_id', 1)->get();

// Get low stock products
$products = Product::whereRaw('stock_quantity <= min_stock')->get();

// Search products
$products = Product::where('name', 'LIKE', '%milk%')
                  ->orWhere('barcode', 'LIKE', '%milk%')
                  ->get();

// Get single product
$product = Product::find(1);
$product = Product::where('barcode', '1234567890')->first();

// Delete product directly
$product = Product::find(1);
if ($product) {
    // Delete image if exists
    if ($product->image) {
        Storage::disk('public')->delete($product->image);
    }
    $product->delete();
}

// Bulk delete directly
Product::whereIn('id', [1, 2, 3])->delete();

// Soft delete (mark as inactive)
Product::where('id', 1)->update(['is_active' => false]);

echo "\nAll operations completed!\n";