@extends('layouts.app')

@section('title', 'Customer Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Customer Info Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Customer Details: {{ $customer->name ?: 'Customer #' . $customer->id }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Customer Information -->
                        <div class="col-md-6">
                            <h5>Contact Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>ID:</strong></td>
                                    <td>{{ $customer->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Name:</strong></td>
                                    <td>{{ $customer->name ?: 'Anonymous Customer' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Phone:</strong></td>
                                    <td>{{ $customer->phone }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $customer->email ?: 'Not provided' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Address:</strong></td>
                                    <td>{{ $customer->full_address ?: 'Not provided' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Member Since:</strong></td>
                                    <td>{{ $customer->created_at->format('M d, Y') }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Customer Statistics -->
                        <div class="col-md-6">
                            <h5>Customer Statistics</h5>
                            <div class="row">
                                <div class="col-6">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body d-flex align-items-center">
                                            <i class="fas fa-shopping-cart fa-2x me-3"></i>
                                            <div>
                                                <div class="fs-6">Total Orders</div>
                                                <div class="fs-4 fw-bold">{{ $customer->sales->count() }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card bg-success text-white">
                                        <div class="card-body d-flex align-items-center">
                                            <i class="fas fa-dollar-sign fa-2x me-3"></i>
                                            <div>
                                                <div class="fs-6">Total Spent</div>
                                                <div class="fs-4 fw-bold">${{ number_format($customer->sales->sum('total_amount'), 2) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body d-flex align-items-center">
                                            <i class="fas fa-star fa-2x me-3"></i>
                                            <div>
                                                <div class="fs-6">Loyalty Points</div>
                                                <div class="fs-4 fw-bold">{{ number_format($customer->loyalty_points, 2) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card bg-info text-white">
                                        <div class="card-body d-flex align-items-center">
                                            <i class="fas fa-box fa-2x me-3"></i>
                                            <div>
                                                <div class="fs-6">Items Purchased</div>
                                                <div class="fs-4 fw-bold">{{ $totalItemsPurchased }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Statistics -->
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="card bg-secondary text-white">
                                <div class="card-body d-flex align-items-center">
                                    <i class="fas fa-chart-line fa-2x me-3"></i>
                                    <div>
                                        <div class="fs-6">Avg Order Value</div>
                                        <div class="fs-4 fw-bold">${{ number_format($averageOrderValue, 2) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-dark text-white">
                                <div class="card-body d-flex align-items-center">
                                    <i class="fas fa-calendar fa-2x me-3"></i>
                                    <div>
                                        <div class="fs-6">Last Purchase</div>
                                        <div class="fs-4 fw-bold">{{ $lastPurchaseDate ? $lastPurchaseDate->format('M d') : 'Never' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card" style="background: purple; color: white;">
                                <div class="card-body d-flex align-items-center">
                                    <i class="fas fa-credit-card fa-2x me-3"></i>
                                    <div>
                                        <div class="fs-6">Preferred Payment</div>
                                        <div class="fs-4 fw-bold">{{ $mostFrequentPaymentMethod ?: 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card" style="background: teal; color: white;">
                                <div class="card-body d-flex align-items-center">
                                    <i class="fas fa-gift fa-2x me-3"></i>
                                    <div>
                                        <div class="fs-6">Points Earned</div>
                                        <div class="fs-4 fw-bold">{{ number_format($customer->points_earned, 2) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Purchase History -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Purchase History</h3>
                </div>
                <div class="card-body">
                    @if($customer->sales->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Sale ID</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Payment Method</th>
                                        <th>Subtotal</th>
                                        <th>Discount</th>
                                        <th>Total</th>
                                        <th>Points Activity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($customer->sales->sortByDesc('created_at') as $sale)
                                    <tr>
                                        <td>
                                            <a href="{{ route('pos.receipt', $sale) }}" class="text-primary">
                                                #{{ $sale->id }}
                                            </a>
                                        </td>
                                        <td>{{ $sale->created_at->format('M d, Y H:i') }}</td>
                                        <td>
                                            <small>
                                                @foreach($sale->saleItems->take(3) as $item)
                                                    {{ $item->product->name }} ({{ $item->quantity }})<br>
                                                @endforeach
                                                @if($sale->saleItems->count() > 3)
                                                    <em>+{{ $sale->saleItems->count() - 3 }} more items</em>
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $sale->payment_method }}</span>
                                        </td>
                                        <td>${{ number_format($sale->subtotal, 2) }}</td>
                                        <td>
                                            @if($sale->discount_amount > 0)
                                                <span class="text-success">-${{ number_format($sale->discount_amount, 2) }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td><strong>${{ number_format($sale->total_amount, 2) }}</strong></td>
                                        <td>
                                            @if($sale->points_earned > 0)
                                                <span class="badge badge-warning">+{{ number_format($sale->points_earned, 0) }}</span>
                                            @else
                                                -
                                            @endif
                                            @if($sale->points_redeemed > 0)
                                                <br><span class="badge badge-danger">-{{ number_format($sale->points_redeemed, 0) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Purchase History</h5>
                            <p class="text-muted">This customer hasn't made any purchases yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection