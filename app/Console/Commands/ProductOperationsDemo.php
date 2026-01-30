<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ProductService;
use App\Models\Product;

class ProductOperationsDemo extends Command
{
    protected $signature = 'demo:products {action} {--id=} {--search=} {--category=}';
    protected $description = 'Demonstrate product operations (get/delete)';

    protected $productService;

    public function __construct(ProductService $productService)
    {
        parent::__construct();
        $this->productService = $productService;
    }

    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'list':
                $this->listProducts();
                break;
            case 'show':
                $this->showProduct();
                break;
            case 'search':
                $this->searchProducts();
                break;
            case 'category':
                $this->getProductsByCategory();
                break;
            case 'low-stock':
                $this->getLowStockProducts();
                break;
            case 'discounted':
                $this->getDiscountedProducts();
                break;
            case 'stats':
                $this->getStats();
                break;
            case 'delete':
                $this->deleteProduct();
                break;
            case 'soft-delete':
                $this->softDeleteProduct();
                break;
            default:
                $this->error('Invalid action. Available actions: list, show, search, category, low-stock, discounted, stats, delete, soft-delete');
                $this->info('Examples:');
                $this->info('  php artisan demo:products list');
                $this->info('  php artisan demo:products show --id=1');
                $this->info('  php artisan demo:products search --search=milk');
                $this->info('  php artisan demo:products category --category=1');
                $this->info('  php artisan demo:products delete --id=1');
        }
    }

    private function listProducts()
    {
        $this->info('Getting all products...');
        $products = $this->productService->getAllProducts();
        
        $this->table(
            ['ID', 'Name', 'Price', 'Stock', 'Category', 'Status'],
            $products->map(function ($product) {
                return [
                    $product->id,
                    $product->name,
                    '$' . number_format($product->getFinalPrice(), 2),
                    $product->stock_quantity,
                    $product->category->name ?? 'No Category',
                    $product->is_active ? 'Active' : 'Inactive'
                ];
            })
        );
        
        $this->info("Total products: " . $products->count());
    }

    private function showProduct()
    {
        $id = $this->option('id');
        if (!$id) {
            $this->error('Product ID is required. Use --id=1');
            return;
        }

        $product = $this->productService->getProductById($id);
        
        if (!$product) {
            $this->error("Product with ID {$id} not found.");
            return;
        }

        $this->info("Product Details:");
        $this->info("ID: {$product->id}");
        $this->info("Name: {$product->name}");
        $this->info("Description: {$product->description}");
        $this->info("Barcode: {$product->barcode}");
        $this->info("Price: $" . number_format($product->price, 2));
        if ($product->hasDiscount()) {
            $this->info("Discount: {$product->discount_percentage}%");
            $this->info("Discounted Price: $" . number_format($product->discounted_price, 2));
        }
        $this->info("Final Price: $" . number_format($product->getFinalPrice(), 2));
        $this->info("Stock: {$product->stock_quantity}");
        $this->info("Min Stock: {$product->min_stock}");
        $this->info("Category: " . ($product->category->name ?? 'No Category'));
        $this->info("Status: " . ($product->is_active ? 'Active' : 'Inactive'));
        $this->info("Low Stock: " . ($product->isLowStock() ? 'Yes' : 'No'));
    }

    private function searchProducts()
    {
        $search = $this->option('search');
        if (!$search) {
            $this->error('Search term is required. Use --search=milk');
            return;
        }

        $this->info("Searching for products with '{$search}'...");
        $products = $this->productService->searchProducts($search);
        
        if ($products->isEmpty()) {
            $this->warn("No products found matching '{$search}'");
            return;
        }

        $this->table(
            ['ID', 'Name', 'Barcode', 'Price', 'Stock'],
            $products->map(function ($product) {
                return [
                    $product->id,
                    $product->name,
                    $product->barcode,
                    '$' . number_format($product->getFinalPrice(), 2),
                    $product->stock_quantity
                ];
            })
        );
        
        $this->info("Found {$products->count()} products");
    }

    private function getProductsByCategory()
    {
        $categoryId = $this->option('category');
        if (!$categoryId) {
            $this->error('Category ID is required. Use --category=1');
            return;
        }

        $this->info("Getting products in category {$categoryId}...");
        $products = $this->productService->getProductsByCategory($categoryId);
        
        if ($products->isEmpty()) {
            $this->warn("No products found in category {$categoryId}");
            return;
        }

        $this->table(
            ['ID', 'Name', 'Price', 'Stock'],
            $products->map(function ($product) {
                return [
                    $product->id,
                    $product->name,
                    '$' . number_format($product->getFinalPrice(), 2),
                    $product->stock_quantity
                ];
            })
        );
        
        $this->info("Found {$products->count()} products in this category");
    }

    private function getLowStockProducts()
    {
        $this->info('Getting low stock products...');
        $products = $this->productService->getLowStockProducts();
        
        if ($products->isEmpty()) {
            $this->info("No low stock products found!");
            return;
        }

        $this->table(
            ['ID', 'Name', 'Current Stock', 'Min Stock', 'Category'],
            $products->map(function ($product) {
                return [
                    $product->id,
                    $product->name,
                    $product->stock_quantity,
                    $product->min_stock,
                    $product->category->name ?? 'No Category'
                ];
            })
        );
        
        $this->warn("Found {$products->count()} low stock products");
    }

    private function getDiscountedProducts()
    {
        $this->info('Getting discounted products...');
        $products = $this->productService->getDiscountedProducts();
        
        if ($products->isEmpty()) {
            $this->info("No discounted products found!");
            return;
        }

        $this->table(
            ['ID', 'Name', 'Original Price', 'Discount %', 'Final Price'],
            $products->map(function ($product) {
                return [
                    $product->id,
                    $product->name,
                    '$' . number_format($product->price, 2),
                    $product->discount_percentage . '%',
                    '$' . number_format($product->getFinalPrice(), 2)
                ];
            })
        );
        
        $this->info("Found {$products->count()} discounted products");
    }

    private function getStats()
    {
        $this->info('Getting product statistics...');
        $stats = $this->productService->getProductStats();
        
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Products', $stats['total_products']],
                ['Active Products', $stats['active_products']],
                ['Inactive Products', $stats['inactive_products']],
                ['Low Stock Products', $stats['low_stock_products']],
                ['Discounted Products', $stats['discounted_products']],
                ['Out of Stock Products', $stats['out_of_stock_products']],
            ]
        );
    }

    private function deleteProduct()
    {
        $id = $this->option('id');
        if (!$id) {
            $this->error('Product ID is required. Use --id=1');
            return;
        }

        // Get product details first
        $product = $this->productService->getProductById($id);
        if (!$product) {
            $this->error("Product with ID {$id} not found.");
            return;
        }

        $this->warn("You are about to DELETE product: {$product->name}");
        if (!$this->confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
            $this->info('Operation cancelled.');
            return;
        }

        $success = $this->productService->deleteProduct($id);
        
        if ($success) {
            $this->info("Product '{$product->name}' deleted successfully!");
        } else {
            $this->error("Failed to delete product '{$product->name}'");
        }
    }

    private function softDeleteProduct()
    {
        $id = $this->option('id');
        if (!$id) {
            $this->error('Product ID is required. Use --id=1');
            return;
        }

        // Get product details first
        $product = $this->productService->getProductById($id);
        if (!$product) {
            $this->error("Product with ID {$id} not found.");
            return;
        }

        $this->warn("You are about to DEACTIVATE product: {$product->name}");
        if (!$this->confirm('Are you sure you want to deactivate this product?')) {
            $this->info('Operation cancelled.');
            return;
        }

        $success = $this->productService->softDeleteProduct($id);
        
        if ($success) {
            $this->info("Product '{$product->name}' deactivated successfully!");
        } else {
            $this->error("Failed to deactivate product '{$product->name}'");
        }
    }
}