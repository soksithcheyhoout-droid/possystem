@extends('layouts.app')

@section('title', 'Products Management')
@section('page-title', 'Products Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Products</h4>
    <a href="{{ route('products.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add New Product
    </a>
</div>

<div class="card">
    <div class="card-body">
        @if($products->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Discount</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                        <tr class="{{ $product->isLowStock() ? 'table-warning' : '' }}">
                            <td>
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" 
                                         class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center" 
                                         style="width: 50px; height: 50px;">
                                        <i class="fas fa-box text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $product->name }}</strong>
                                @if($product->barcode)
                                    <br><small class="text-muted">{{ $product->barcode }}</small>
                                @endif
                            </td>
                            <td>{{ $product->category->name }}</td>
                            <td>
                                @if($product->hasDiscount())
                                    <div>
                                        <span class="text-decoration-line-through text-muted">${{ number_format($product->price, 2) }}</span>
                                        <br>
                                        <strong class="text-success">${{ number_format($product->getFinalPrice(), 2) }}</strong>
                                    </div>
                                @else
                                    <strong>${{ number_format($product->price, 2) }}</strong>
                                @endif
                            </td>
                            <td>
                                @if($product->hasDiscount())
                                    <span class="badge bg-danger">{{ $product->discount_percentage }}% OFF</span>
                                    <br>
                                    <small class="text-success">Save ${{ number_format($product->price - $product->getFinalPrice(), 2) }}</small>
                                @else
                                    <span class="text-muted">No discount</span>
                                @endif
                            </td>
                            <td>
                                {{ $product->stock_quantity }}
                                @if($product->isLowStock())
                                    <i class="fas fa-exclamation-triangle text-warning ms-1" title="Low Stock"></i>
                                @endif
                            </td>
                            <td>
                                @if($product->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                onclick="return confirm('Are you sure you want to delete this product?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center">
                {{ $products->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-box fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No products found</h5>
                <p class="text-muted">Start by adding your first product.</p>
                <a href="{{ route('products.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add Product
                </a>
            </div>
        @endif
    </div>
</div>
@endsection