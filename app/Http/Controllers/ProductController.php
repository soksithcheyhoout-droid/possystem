<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Services\TelegramService;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    protected $telegramService;
    protected $productService;

    public function __construct(TelegramService $telegramService, ProductService $productService)
    {
        $this->telegramService = $telegramService;
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        $filters = [];
        
        // Apply filters from request
        if ($request->has('search')) {
            $filters['search'] = $request->search;
        }
        
        if ($request->has('category_id')) {
            $filters['category_id'] = $request->category_id;
        }
        
        if ($request->has('active')) {
            $filters['active'] = $request->boolean('active');
        }
        
        if ($request->has('low_stock')) {
            $filters['low_stock'] = $request->boolean('low_stock');
        }
        
        if ($request->has('has_discount')) {
            $filters['has_discount'] = $request->boolean('has_discount');
        }

        $products = $this->productService->getProductsPaginated(15, $filters);
        $categories = Category::where('is_active', true)->get();
        
        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'barcode' => 'nullable|string|unique:products,barcode',
            'price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'cost' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean'
        ]);

        $data = $request->all();
        
        // Calculate discounted price if discount is provided
        if ($request->discount_percentage > 0) {
            $data['discounted_price'] = $request->price * (1 - ($request->discount_percentage / 100));
        } else {
            $data['discount_percentage'] = 0;
            $data['discounted_price'] = null;
        }
        
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);

        // Check if the new product is low stock and send alert
        if ($product->isLowStock()) {
            $this->telegramService->sendLowStockAlert($product);
        }

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        // Load the product with its relationships
        $product = $this->productService->getProductById($product->id);
        
        if (!$product) {
            return redirect()->route('products.index')->with('error', 'Product not found.');
        }
        
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'barcode' => 'nullable|string|unique:products,barcode,' . $product->id,
            'price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'cost' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean'
        ]);

        $data = $request->all();
        
        // Calculate discounted price if discount is provided
        if ($request->discount_percentage > 0) {
            $data['discounted_price'] = $request->price * (1 - ($request->discount_percentage / 100));
        } else {
            $data['discount_percentage'] = 0;
            $data['discounted_price'] = null;
        }
        
        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        // Check if the updated product is low stock and send alert
        if ($product->isLowStock()) {
            $this->telegramService->sendLowStockAlert($product);
        }

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $success = $this->productService->deleteProductModel($product);
        
        if ($success) {
            return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
        } else {
            return redirect()->route('products.index')->with('error', 'Failed to delete product.');
        }
    }

    /**
     * Bulk delete products
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id'
        ]);

        $deletedCount = $this->productService->bulkDeleteProducts($request->product_ids);
        
        return redirect()->route('products.index')
                        ->with('success', "Successfully deleted {$deletedCount} products.");
    }

    /**
     * Soft delete product (mark as inactive)
     */
    public function softDestroy(Product $product)
    {
        $success = $this->productService->softDeleteProduct($product->id);
        
        if ($success) {
            return redirect()->route('products.index')->with('success', 'Product deactivated successfully.');
        } else {
            return redirect()->route('products.index')->with('error', 'Failed to deactivate product.');
        }
    }

    /**
     * Search products via AJAX
     */
    public function search(Request $request)
    {
        $search = $request->get('q', '');
        
        if (empty($search)) {
            return response()->json([]);
        }

        $products = $this->productService->searchProducts($search);
        
        return response()->json($products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'barcode' => $product->barcode,
                'price' => $product->getFinalPrice(),
                'stock_quantity' => $product->stock_quantity,
                'category' => $product->category->name ?? 'No Category',
                'image_url' => $product->image ? asset('storage/' . $product->image) : null,
            ];
        }));
    }

    /**
     * Get products by category via AJAX
     */
    public function getByCategory(Request $request)
    {
        $categoryId = $request->get('category_id');
        
        if (!$categoryId) {
            return response()->json([]);
        }

        $products = $this->productService->getProductsByCategory($categoryId);
        
        return response()->json($products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'barcode' => $product->barcode,
                'price' => $product->getFinalPrice(),
                'stock_quantity' => $product->stock_quantity,
                'image_url' => $product->image ? asset('storage/' . $product->image) : null,
            ];
        }));
    }

    /**
     * Get low stock products
     */
    public function lowStock()
    {
        $products = $this->productService->getLowStockProducts();
        return view('admin.products.low-stock', compact('products'));
    }

    /**
     * Get product statistics
     */
    public function stats()
    {
        $stats = $this->productService->getProductStats();
        return response()->json($stats);
    }
}
