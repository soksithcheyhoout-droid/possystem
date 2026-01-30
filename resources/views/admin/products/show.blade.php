@extends('layouts.app')

@section('title', 'Product Details')
@section('page-title', 'Product Details')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">{{ $product->name }}</h5>
                <div>
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                    <a href="{{ route('products.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Back
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" 
                                 class="img-fluid rounded">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center rounded" 
                                 style="height: 200px;">
                                <i class="fas fa-box fa-3x text-muted"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-8">
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%">Category:</th>
                                <td>{{ $product->category->name }}</td>
                            </tr>
                            <tr>
                                <th>Description:</th>
                                <td>{{ $product->description ?: 'No description available' }}</td>
                            </tr>
                            <tr>
                                <th>Barcode:</th>
                                <td>{{ $product->barcode ?: 'Not set' }}</td>
                            </tr>
                            <tr>
                                <th>Original Price:</th>
                                <td>
                                    @if($product->hasDiscount())
                                        <span class="text-decoration-line-through text-muted">${{ number_format($product->price, 2) }}</span>
                                    @else
                                        <strong class="text-success">${{ number_format($product->price, 2) }}</strong>
                                    @endif
                                </td>
                            </tr>
                            @if($product->hasDiscount())
                            <tr>
                                <th>Discount:</th>
                                <td>
                                    <span class="badge bg-danger">{{ $product->discount_percentage }}% OFF</span>
                                    <small class="text-muted ms-2">Save ${{ number_format($product->price - $product->getFinalPrice(), 2) }}</small>
                                </td>
                            </tr>
                            <tr>
                                <th>Final Price:</th>
                                <td><strong class="text-success">${{ number_format($product->getFinalPrice(), 2) }}</strong></td>
                            </tr>
                            @endif
                            <tr>
                                <th>Cost Price:</th>
                                <td>${{ number_format($product->cost, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Stock Quantity:</th>
                                <td>
                                    <span class="badge {{ $product->isLowStock() ? 'bg-warning' : 'bg-success' }}">
                                        {{ $product->stock_quantity }} units
                                    </span>
                                    @if($product->isLowStock())
                                        <small class="text-warning ms-2">Low Stock!</small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Minimum Stock:</th>
                                <td>{{ $product->min_stock }} units</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    @if($product->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Created:</th>
                                <td>{{ $product->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Last Updated:</th>
                                <td>{{ $product->updated_at->format('M d, Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection