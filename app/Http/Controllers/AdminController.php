<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Sale;
use App\Models\Customer;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    protected $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    public function dashboard()
    {
        $stats = [
            'total_products' => Product::count(),
            'total_categories' => Category::count(),
            'total_sales' => Sale::count(),
            'total_customers' => Customer::count(),
            'today_sales' => Sale::whereDate('sale_date', today())->sum('total_amount'),
            'low_stock_products' => Product::whereColumn('stock_quantity', '<=', 'min_stock')->count(),
        ];

        $recent_sales = Sale::with(['customer', 'saleItems.product'])
            ->latest()
            ->take(5)
            ->get();

        $top_products = Product::select('products.*', DB::raw('SUM(sale_items.quantity) as total_sold'))
            ->join('sale_items', 'products.id', '=', 'sale_items.product_id')
            ->groupBy('products.id')
            ->orderBy('total_sold', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recent_sales', 'top_products'));
    }

    public function reports()
    {
        return view('admin.reports');
    }

    public function settings()
    {
        return view('admin.settings');
    }

    public function storeSettings()
    {
        $settings = $this->settingsService->getStoreSettings();
        return view('admin.store-settings', compact('settings'));
    }

    public function updateStoreSettings(Request $request)
    {
        $request->validate([
            'store_name' => 'required|string|max:255',
            'store_tagline' => 'nullable|string|max:255',
            'store_address' => 'nullable|string|max:500',
            'store_phone' => 'nullable|string|max:20',
            'store_email' => 'nullable|email|max:255',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'currency_symbol' => 'nullable|string|max:5',
            'receipt_footer' => 'nullable|string|max:255',
            'store_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'store_banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle file uploads
        $settings = $request->except(['store_logo', 'store_banner', '_token']);
        
        if ($request->hasFile('store_logo')) {
            $this->settingsService->uploadImage($request->file('store_logo'), 'logo');
        }

        if ($request->hasFile('store_banner')) {
            $this->settingsService->uploadImage($request->file('store_banner'), 'banner');
        }

        // Update other settings
        $this->settingsService->updateStoreSettings($settings);

        return redirect()->route('admin.store-settings')
            ->with('success', 'Store settings updated successfully!');
    }

    public function deleteStoreImage(Request $request)
    {
        $type = $request->input('type');
        
        if (in_array($type, ['logo', 'banner'])) {
            $this->settingsService->deleteImage($type);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 400);
    }
}
