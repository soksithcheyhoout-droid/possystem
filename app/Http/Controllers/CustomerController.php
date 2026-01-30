<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Customer::withCount('sales')
                ->with(['sales' => function($query) {
                    $query->select('id', 'customer_id', 'total_amount', 'created_at')
                          ->orderBy('created_at', 'desc');
                }]);

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('id', 'like', "%{$search}%");
                });
            }

            // Filter by loyalty points
            if ($request->filled('points_filter')) {
                switch ($request->points_filter) {
                    case 'high':
                        $query->where('loyalty_points', '>=', 50);
                        break;
                    case 'medium':
                        $query->whereBetween('loyalty_points', [10, 49]);
                        break;
                    case 'low':
                        $query->whereBetween('loyalty_points', [1, 9]);
                        break;
                    case 'none':
                        $query->where('loyalty_points', 0);
                        break;
                }
            }

            // Filter by sales activity
            if ($request->filled('activity_filter')) {
                switch ($request->activity_filter) {
                    case 'active':
                        $query->whereHas('sales', function($q) {
                            $q->where('created_at', '>=', now()->subDays(30));
                        });
                        break;
                    case 'inactive':
                        $query->whereDoesntHave('sales', function($q) {
                            $q->where('created_at', '>=', now()->subDays(30));
                        });
                        break;
                    case 'new':
                        $query->where('created_at', '>=', now()->subDays(7));
                        break;
                }
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            switch ($sortBy) {
                case 'name':
                    $query->orderBy('name', $sortOrder);
                    break;
                case 'loyalty_points':
                    $query->orderBy('loyalty_points', $sortOrder);
                    break;
                case 'sales_count':
                    $query->orderBy('sales_count', $sortOrder);
                    break;
                default:
                    $query->orderBy('created_at', $sortOrder);
            }

            $customers = $query->paginate(20)->withQueryString();

            // Calculate summary statistics
            $totalCustomers = Customer::count();
            $activeCustomers = Customer::whereHas('sales', function($q) {
                $q->where('created_at', '>=', now()->subDays(30));
            })->count();
            $totalLoyaltyPoints = Customer::sum('loyalty_points');
            $averageLoyaltyPoints = $totalCustomers > 0 ? Customer::avg('loyalty_points') : 0;

            return view('admin.customers.index', compact(
                'customers', 
                'totalCustomers', 
                'activeCustomers', 
                'totalLoyaltyPoints', 
                'averageLoyaltyPoints'
            ));
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Customer index error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function export(Request $request)
    {
        $query = Customer::withCount('sales')
            ->with(['sales' => function($query) {
                $query->select('id', 'customer_id', 'total_amount', 'created_at')
                      ->orderBy('created_at', 'desc');
            }]);

        // If specific customer IDs are provided, export only those
        if ($request->filled('customer_ids')) {
            $query->whereIn('id', $request->customer_ids);
        } else {
            // Apply same filters as index
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('id', 'like', "%{$search}%");
                });
            }

            if ($request->filled('points_filter')) {
                switch ($request->points_filter) {
                    case 'high':
                        $query->where('loyalty_points', '>=', 50);
                        break;
                    case 'medium':
                        $query->whereBetween('loyalty_points', [10, 49]);
                        break;
                    case 'low':
                        $query->whereBetween('loyalty_points', [1, 9]);
                        break;
                    case 'none':
                        $query->where('loyalty_points', 0);
                        break;
                }
            }

            if ($request->filled('activity_filter')) {
                switch ($request->activity_filter) {
                    case 'active':
                        $query->whereHas('sales', function($q) {
                            $q->where('created_at', '>=', now()->subDays(30));
                        });
                        break;
                    case 'inactive':
                        $query->whereDoesntHave('sales', function($q) {
                            $q->where('created_at', '>=', now()->subDays(30));
                        });
                        break;
                    case 'new':
                        $query->where('created_at', '>=', now()->subDays(7));
                        break;
                }
            }
        }

        $customers = $query->orderBy('created_at', 'desc')->get();

        $filename = 'customers_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($customers) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'ID',
                'Name',
                'Phone',
                'Email',
                'Address',
                'Loyalty Points',
                'Points Earned',
                'Points Redeemed',
                'Total Sales',
                'Total Spent',
                'Last Purchase',
                'Date Added'
            ]);

            foreach ($customers as $customer) {
                $salesCollection = $customer->sales ?? collect();
                $totalSpent = $salesCollection->sum('total_amount') ?? 0;
                $lastPurchase = $salesCollection->isNotEmpty() ? $salesCollection->max('created_at') : null;
                
                fputcsv($file, [
                    $customer->id,
                    $customer->name ?: 'Customer #' . $customer->id,
                    $customer->phone ?: '',
                    $customer->email ?: '',
                    $customer->full_address ?: '',
                    number_format($customer->loyalty_points, 2),
                    number_format($customer->points_earned, 2),
                    number_format($customer->points_redeemed, 2),
                    $customer->sales_count,
                    number_format($totalSpent, 2),
                    $lastPurchase ? \Carbon\Carbon::parse($lastPurchase)->format('Y-m-d H:i:s') : '',
                    $customer->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'customer_ids' => 'required|array',
            'customer_ids.*' => 'exists:customers,id'
        ]);

        $customers = Customer::whereIn('id', $request->customer_ids)->get();
        $deletedCount = 0;
        $errors = [];

        foreach ($customers as $customer) {
            try {
                // Delete related sales and their items first
                foreach ($customer->sales as $sale) {
                    $sale->saleItems()->delete(); // Delete sale items first
                    $sale->delete(); // Then delete the sale
                }
                
                // Now delete the customer
                $customer->delete();
                $deletedCount++;
            } catch (\Exception $e) {
                $errors[] = "Customer #{$customer->id} could not be deleted: " . $e->getMessage();
            }
        }

        if ($deletedCount > 0) {
            $message = "Successfully deleted {$deletedCount} customer(s) and their related sales";
            if (!empty($errors)) {
                $message .= ". Some customers could not be deleted: " . implode(', ', $errors);
            }
            return redirect()->route('customers.index')->with('success', $message);
        } else {
            return redirect()->route('customers.index')->with('error', 'No customers could be deleted: ' . implode(', ', $errors));
        }
    }

    public function create()
    {
        return view('admin.customers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:customers,email',
            'phone' => 'required|string|max:20|unique:customers,phone',
            'house_number' => 'nullable|string|max:50',
            'street' => 'nullable|string|max:255',
            'address' => 'nullable|string'
        ]);

        $customer = Customer::create($request->all());

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer created successfully! All customer information has been saved.');
    }

    public function show(Customer $customer)
    {
        try {
            $customer->load(['sales.saleItems.product']);
            
            // Calculate additional customer statistics
            $totalItemsPurchased = $customer->sales->sum(function($sale) {
                return $sale->saleItems->sum('quantity');
            });
            
            $averageOrderValue = $customer->sales->count() > 0 
                ? $customer->sales->avg('total_amount') 
                : 0;
                
            $lastPurchaseDate = $customer->sales->sortByDesc('created_at')->first()?->created_at;
            
            $mostFrequentPaymentMethod = $customer->sales
                ->groupBy('payment_method')
                ->sortByDesc(function($sales) { return $sales->count(); })
                ->keys()
                ->first();
                
            return view('admin.customers.show', compact(
                'customer', 
                'totalItemsPurchased', 
                'averageOrderValue', 
                'lastPurchaseDate',
                'mostFrequentPaymentMethod'
            ));
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Customer show error: ' . $e->getMessage(),
                'customer_id' => $customer->id ?? 'unknown',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function showWithSuccess(Customer $customer)
    {
        $customer->load(['sales.saleItems.product']);
        
        // Calculate additional customer statistics
        $totalItemsPurchased = $customer->sales->sum(function($sale) {
            return $sale->saleItems->sum('quantity');
        });
        
        $averageOrderValue = $customer->sales->count() > 0 
            ? $customer->sales->avg('total_amount') 
            : 0;
            
        $lastPurchaseDate = $customer->sales->sortByDesc('created_at')->first()?->created_at;
        
        $mostFrequentPaymentMethod = $customer->sales
            ->groupBy('payment_method')
            ->sortByDesc(function($sales) { return $sales->count(); })
            ->keys()
            ->first();
            
        return view('admin.customers.show', compact(
            'customer', 
            'totalItemsPurchased', 
            'averageOrderValue', 
            'lastPurchaseDate',
            'mostFrequentPaymentMethod'
        ))->with('success', 'Payment processed successfully! Customer information and purchase history updated.');
    }

    public function edit(Customer $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:customers,email,' . $customer->id,
            'phone' => 'required|string|max:20',
            'house_number' => 'nullable|string|max:50',
            'street' => 'nullable|string|max:255',
            'address' => 'nullable|string'
        ]);

        $customer->update($request->all());

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer information updated successfully! All changes have been saved.');
    }

    public function destroy(Customer $customer)
    {
        try {
            // Delete related sales and their items first
            foreach ($customer->sales as $sale) {
                $sale->saleItems()->delete(); // Delete sale items first
                $sale->delete(); // Then delete the sale
            }
            
            // Now delete the customer
            $customer->delete();

            return redirect()->route('customers.index')
                ->with('success', 'Customer and all related sales deleted successfully');
        } catch (\Exception $e) {
            return redirect()->route('customers.index')
                ->with('error', 'Error deleting customer: ' . $e->getMessage());
        }
    }
}