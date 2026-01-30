@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="row mb-4">
    <!-- Stats Cards -->
    <div class="col-md-3 mb-3">
        <div class="card text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title">{{ $stats['total_products'] }}</h4>
                        <p class="card-text">Total Products</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-box fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card text-white" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title">{{ $stats['total_categories'] }}</h4>
                        <p class="card-text">Categories</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-tags fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card text-white" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title">{{ $stats['total_sales'] }}</h4>
                        <p class="card-text">Total Sales</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card text-white" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title">${{ number_format($stats['today_sales'], 2) }}</h4>
                        <p class="card-text">Today's Sales</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-dollar-sign fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Sales -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-receipt me-2"></i>Recent Sales
                </h5>
            </div>
            <div class="card-body">
                @if($recent_sales->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Receipt #</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recent_sales as $sale)
                                <tr>
                                    <td>{{ $sale->receipt_number }}</td>
                                    <td>{{ $sale->customer->name ?? 'Walk-in Customer' }}</td>
                                    <td>{{ $sale->saleItems->count() }} items</td>
                                    <td>${{ number_format($sale->total_amount, 2) }}</td>
                                    <td>{{ $sale->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-4">No sales recorded yet.</p>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Quick Actions & Alerts -->
    <div class="col-md-4">
        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt me-2"></i>Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('pos.index') }}" class="btn btn-primary">
                        <i class="fas fa-cash-register me-2"></i>Open POS
                    </a>
                    <a href="{{ route('products.create') }}" class="btn btn-outline-primary">
                        <i class="fas fa-plus me-2"></i>Add Product
                    </a>
                    <a href="{{ route('categories.create') }}" class="btn btn-outline-primary">
                        <i class="fas fa-tag me-2"></i>Add Category
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Low Stock Alert -->
        @if($stats['low_stock_products'] > 0)
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="card-title mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>Low Stock Alert
                </h5>
            </div>
            <div class="card-body">
                <p class="mb-2">{{ $stats['low_stock_products'] }} products are running low on stock.</p>
                <a href="{{ route('products.index') }}?filter=low_stock" class="btn btn-warning btn-sm">
                    View Products
                </a>
            </div>
        </div>
        @endif
    </div>
</div>

@if($top_products->count() > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-star me-2"></i>Top Selling Products
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Total Sold</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($top_products as $product)
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->category->name }}</td>
                                <td>${{ number_format($product->price, 2) }}</td>
                                <td>{{ $product->stock_quantity }}</td>
                                <td>{{ $product->total_sold }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection