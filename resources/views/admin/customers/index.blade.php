@extends('layouts.app')

@section('title', 'Customer Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Customer Management</h3>
                    <div class="btn-group">
                        <a href="{{ route('customers.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Customer
                        </a>
                        <button type="button" class="btn btn-success" onclick="exportCustomers()">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body d-flex align-items-center">
                                    <i class="fas fa-users fa-2x me-3"></i>
                                    <div>
                                        <div class="fs-6">Total Customers</div>
                                        <div class="fs-4 fw-bold">{{ number_format($totalCustomers) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body d-flex align-items-center">
                                    <i class="fas fa-user-check fa-2x me-3"></i>
                                    <div>
                                        <div class="fs-6">Active Customers</div>
                                        <div class="fs-4 fw-bold">{{ number_format($activeCustomers) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body d-flex align-items-center">
                                    <i class="fas fa-star fa-2x me-3"></i>
                                    <div>
                                        <div class="fs-6">Total Loyalty Points</div>
                                        <div class="fs-4 fw-bold">{{ number_format($totalLoyaltyPoints) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body d-flex align-items-center">
                                    <i class="fas fa-chart-line fa-2x me-3"></i>
                                    <div>
                                        <div class="fs-6">Avg Loyalty Points</div>
                                        <div class="fs-4 fw-bold">{{ number_format($averageLoyaltyPoints, 1) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Filters -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="Search customers..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <select name="points_filter" class="form-control">
                                    <option value="">All Points</option>
                                    <option value="high" {{ request('points_filter') == 'high' ? 'selected' : '' }}>High (50+)</option>
                                    <option value="medium" {{ request('points_filter') == 'medium' ? 'selected' : '' }}>Medium (10-49)</option>
                                    <option value="low" {{ request('points_filter') == 'low' ? 'selected' : '' }}>Low (1-9)</option>
                                    <option value="none" {{ request('points_filter') == 'none' ? 'selected' : '' }}>None (0)</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="activity_filter" class="form-control">
                                    <option value="">All Activity</option>
                                    <option value="active" {{ request('activity_filter') == 'active' ? 'selected' : '' }}>Active (30 days)</option>
                                    <option value="inactive" {{ request('activity_filter') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="new" {{ request('activity_filter') == 'new' ? 'selected' : '' }}>New (7 days)</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="sort_by" class="form-control">
                                    <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Date Added</option>
                                    <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Name</option>
                                    <option value="loyalty_points" {{ request('sort_by') == 'loyalty_points' ? 'selected' : '' }}>Loyalty Points</option>
                                    <option value="sales_count" {{ request('sort_by') == 'sales_count' ? 'selected' : '' }}>Sales Count</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <select name="sort_order" class="form-control">
                                    <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>↓</option>
                                    <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>↑</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('customers.index') }}" class="btn btn-secondary">Clear</a>
                            </div>
                        </div>
                    </form>

                    <!-- Bulk Actions -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="bulk-actions" style="display: none;">
                                <button type="button" class="btn btn-danger btn-sm" onclick="bulkDelete()">
                                    <i class="fas fa-trash"></i> Delete Selected
                                </button>
                                <button type="button" class="btn btn-success btn-sm" onclick="bulkExport()">
                                    <i class="fas fa-download"></i> Export Selected
                                </button>
                                <span class="selected-count ml-2"></span>
                            </div>
                        </div>
                    </div>
                    <!-- Customers Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="30">
                                        <input type="checkbox" id="select-all">
                                    </th>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Loyalty Points</th>
                                    <th>Total Sales</th>
                                    <th>Last Purchase</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customers as $customer)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="customer-checkbox" value="{{ $customer->id }}">
                                    </td>
                                    <td>{{ $customer->id }}</td>
                                    <td>
                                        <strong>{{ $customer->name ?: 'Customer #' . $customer->id }}</strong>
                                        @if($customer->created_at >= now()->subDays(7))
                                            <span class="badge badge-success">New</span>
                                        @endif
                                    </td>
                                    <td>{{ $customer->phone }}</td>
                                    <td>{{ $customer->email ?: '-' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $customer->loyalty_points >= 50 ? 'warning' : ($customer->loyalty_points >= 10 ? 'info' : 'secondary') }}">
                                            {{ number_format($customer->loyalty_points, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $customer->sales_count }} sales
                                        @php
                                            $salesCollection = $customer->sales ?? collect();
                                            $totalSpent = $salesCollection->sum('total_amount');
                                        @endphp
                                        @if($totalSpent > 0)
                                            <br><small class="text-muted">${{ number_format($totalSpent, 2) }} total</small>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $salesCollection = $customer->sales ?? collect();
                                            $lastPurchaseDate = $salesCollection->isNotEmpty() ? $salesCollection->max('created_at') : null;
                                        @endphp
                                        @if($lastPurchaseDate)
                                            {{ \Carbon\Carbon::parse($lastPurchaseDate)->format('M d, Y') }}
                                            @if(\Carbon\Carbon::parse($lastPurchaseDate) >= now()->subDays(30))
                                                <span class="badge badge-success">Active</span>
                                            @endif
                                        @else
                                            <span class="text-muted">No purchases</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('customers.show', $customer) }}" class="btn btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('customers.destroy', $customer) }}" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure? This will also delete all sales records for this customer.')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">No customers found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $customers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Bulk Delete Form -->
<form id="bulk-delete-form" method="POST" action="{{ route('customers.bulk-delete') }}" style="display: none;">
    @csrf
    @method('DELETE')
    <input type="hidden" name="customer_ids" id="bulk-delete-ids">
</form>

<!-- Export Form -->
<form id="export-form" method="GET" action="{{ route('customers.export') }}" style="display: none;">
    <input type="hidden" name="customer_ids" id="export-ids">
    <input type="hidden" name="search" value="{{ request('search') }}">
    <input type="hidden" name="points_filter" value="{{ request('points_filter') }}">
    <input type="hidden" name="activity_filter" value="{{ request('activity_filter') }}">
</form>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Select all checkbox functionality
    $('#select-all').change(function() {
        $('.customer-checkbox').prop('checked', this.checked);
        updateBulkActions();
    });

    // Individual checkbox functionality
    $('.customer-checkbox').change(function() {
        updateBulkActions();
        
        // Update select all checkbox
        var totalCheckboxes = $('.customer-checkbox').length;
        var checkedCheckboxes = $('.customer-checkbox:checked').length;
        $('#select-all').prop('checked', totalCheckboxes === checkedCheckboxes);
    });

    function updateBulkActions() {
        var checkedBoxes = $('.customer-checkbox:checked');
        if (checkedBoxes.length > 0) {
            $('.bulk-actions').show();
            $('.selected-count').text(checkedBoxes.length + ' selected');
        } else {
            $('.bulk-actions').hide();
        }
    }
});

function bulkDelete() {
    var selectedIds = [];
    $('.customer-checkbox:checked').each(function() {
        selectedIds.push($(this).val());
    });

    if (selectedIds.length === 0) {
        alert('Please select customers to delete');
        return;
    }

    if (confirm('Are you sure you want to delete ' + selectedIds.length + ' customer(s)? This will also delete all their sales records.')) {
        $('#bulk-delete-ids').val(JSON.stringify(selectedIds));
        $('#bulk-delete-form').submit();
    }
}

function bulkExport() {
    var selectedIds = [];
    $('.customer-checkbox:checked').each(function() {
        selectedIds.push($(this).val());
    });

    if (selectedIds.length === 0) {
        alert('Please select customers to export');
        return;
    }

    $('#export-ids').val(JSON.stringify(selectedIds));
    $('#export-form').submit();
}

function exportCustomers() {
    $('#export-form').submit();
}
</script>
@endpush