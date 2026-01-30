<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\GoogleController;

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Google OAuth Routes
Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::get('/auth/google/setup', function () {
    return view('auth.google-setup');
})->name('auth.google.setup');

Route::get('/auth/google/test', function () {
    if (!config('services.google.client_id') || !config('services.google.client_secret')) {
        return redirect()->route('auth.google.setup')
            ->with('error', 'Google OAuth is not configured yet.');
    }
    return view('auth.google-test');
})->name('auth.google.test');

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/admin');
    }
    return redirect('/login');
});

// Admin Routes (Protected by authentication)
Route::prefix('admin')->middleware('admin')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/reports', [AdminController::class, 'reports'])->name('admin.reports');
    Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
    
    // Product Management
    Route::resource('products', ProductController::class);
    Route::delete('/products-bulk', [ProductController::class, 'bulkDestroy'])->name('products.bulk-destroy');
    Route::patch('/products/{product}/soft-delete', [ProductController::class, 'softDestroy'])->name('products.soft-destroy');
    Route::get('/products-search', [ProductController::class, 'search'])->name('products.search');
    Route::get('/products-by-category', [ProductController::class, 'getByCategory'])->name('products.by-category');
    Route::get('/products-low-stock', [ProductController::class, 'lowStock'])->name('products.low-stock');
    Route::get('/products-stats', [ProductController::class, 'stats'])->name('products.stats');
    
    // Category Management
    Route::resource('categories', CategoryController::class);
    
    // Customer Management
    Route::resource('customers', CustomerController::class);
    Route::get('/customers/{customer}/success', [CustomerController::class, 'showWithSuccess'])->name('customers.show.success');
    Route::get('/customers-export', [CustomerController::class, 'export'])->name('customers.export');
    Route::delete('/customers-bulk', [CustomerController::class, 'bulkDelete'])->name('customers.bulk-delete');
    
    // Test route for debugging
    Route::get('/test-customer', function() {
        try {
            $customer = \App\Models\Customer::first();
            if (!$customer) {
                return 'No customers found in database';
            }
            
            return 'Customer found: ID=' . $customer->id . ', Name=' . ($customer->name ?: 'No name') . ', Phone=' . $customer->phone . '<br><a href="/admin/customers/' . $customer->id . '">View Customer</a>';
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage() . '<br>File: ' . $e->getFile() . '<br>Line: ' . $e->getLine();
        }
    });
    
    // Telegram Settings
    Route::get('/telegram/settings', [TelegramController::class, 'settings'])->name('telegram.settings');
    Route::post('/telegram/test', [TelegramController::class, 'testConnection'])->name('telegram.test');
    Route::post('/telegram/daily-report', [TelegramController::class, 'sendDailyReport'])->name('telegram.daily-report');
    Route::get('/telegram/get-chat-id', [TelegramController::class, 'getChatId'])->name('telegram.get-chat-id');
    Route::post('/telegram/update-chat-id', [TelegramController::class, 'updateChatId'])->name('telegram.update-chat-id');
    
    // Store Settings
    Route::get('/store-settings', [AdminController::class, 'storeSettings'])->name('admin.store-settings');
    Route::post('/store-settings', [AdminController::class, 'updateStoreSettings'])->name('admin.store-settings.update');
    Route::delete('/store-settings/image', [AdminController::class, 'deleteStoreImage'])->name('admin.store-settings.delete-image');
});

// POS Routes (Protected by authentication)
Route::prefix('pos')->middleware('admin')->group(function () {
    Route::get('/', [POSController::class, 'index'])->name('pos.index');
    Route::get('/search-product', [POSController::class, 'searchProduct'])->name('pos.search-product');
    Route::get('/products-by-category', [POSController::class, 'getProductsByCategory'])->name('pos.products-by-category');
    Route::get('/find-customer', [POSController::class, 'findCustomer'])->name('pos.find-customer');
    Route::post('/process-sale', [POSController::class, 'processSale'])->name('pos.process-sale');
    Route::get('/receipt/{sale}', [POSController::class, 'printReceipt'])->name('pos.receipt');
});

// Telegram Webhook (no CSRF protection needed)
Route::post('/telegram/webhook', [TelegramController::class, 'webhook'])->name('telegram.webhook');
