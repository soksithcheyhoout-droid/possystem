<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function index()
    {
        $categories = Category::where('is_active', true)->get();
        $products = Product::with('category')
            ->where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->get();

        return view('pos.index', compact('categories', 'products'));
    }

    public function searchProduct(Request $request)
    {
        $query = $request->get('query');
        
        $products = Product::with('category')
            ->where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->where(function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('barcode', 'LIKE', "%{$query}%");
            })
            ->get();

        return response()->json($products);
    }

    public function getProductsByCategory(Request $request)
    {
        $categoryId = $request->get('category_id');
        
        $products = Product::with('category')
            ->where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->when($categoryId, function($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            })
            ->get();

        return response()->json($products);
    }

    public function findCustomer(Request $request)
    {
        $phone = $request->get('phone');
        
        $customer = Customer::where('phone', $phone)->first();
        
        if ($customer) {
            return response()->json([
                'success' => true,
                'customer' => $customer
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Customer not found'
        ]);
    }

    public function processSale(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'customer.phone' => 'required|string',
            'customer.name' => 'nullable|string',
            'customer.house_number' => 'nullable|string',
            'customer.street' => 'nullable|string',
            'subtotal' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'loyalty_discount' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,digital_wallet'
        ]);

        try {
            DB::beginTransaction();

            // Find or create customer
            $customer = Customer::where('phone', $request->customer['phone'])->first();
            
            if (!$customer) {
                $customer = Customer::create([
                    'phone' => $request->customer['phone'],
                    'name' => $request->customer['name'] ?? null,
                    'house_number' => $request->customer['house_number'] ?? null,
                    'street' => $request->customer['street'] ?? null,
                    'loyalty_points' => 0
                ]);
            } else {
                // Update customer info if provided
                if ($request->customer['name']) {
                    $customer->name = $request->customer['name'];
                }
                if ($request->customer['house_number']) {
                    $customer->house_number = $request->customer['house_number'];
                }
                if ($request->customer['street']) {
                    $customer->street = $request->customer['street'];
                }
                $customer->save();
            }

            // Apply loyalty discount if any
            $pointsRedeemed = 0;
            if ($request->loyalty_discount > 0) {
                $pointsToRedeem = $request->loyalty_discount; // 1 point = $1
                if ($customer->loyalty_points >= $pointsToRedeem) {
                    $customer->redeemPoints($pointsToRedeem);
                    $pointsRedeemed = $pointsToRedeem;
                }
            }

            // Calculate points earned (1 point per $10 spent on final amount after discount)
            $pointsEarned = floor($request->total_amount / 10);

            // Create sale
            $sale = Sale::create([
                'receipt_number' => Sale::generateReceiptNumber(),
                'customer_id' => $customer->id,
                'subtotal' => $request->subtotal,
                'tax_amount' => $request->tax_amount,
                'discount_amount' => $request->loyalty_discount,
                'total_amount' => $request->total_amount,
                'paid_amount' => $request->paid_amount,
                'change_amount' => $request->paid_amount - $request->total_amount,
                'payment_method' => $request->payment_method,
                'sale_date' => now(),
                'points_earned' => $pointsEarned,
                'points_redeemed' => $pointsRedeemed,
            ]);

            // Create sale items and update stock
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                // Check stock availability
                if ($product->stock_quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}");
                }

                // Create sale item
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->getFinalPrice(),
                    'total_price' => $product->getFinalPrice() * $item['quantity'],
                ]);

                // Update stock
                $product->decrement('stock_quantity', $item['quantity']);
            }

            // Add loyalty points (1 point per $10 spent)
            $customer->addLoyaltyPoints($request->total_amount);

            DB::commit();

            // Send Telegram notification
            $sale->load(['customer', 'saleItems.product']);
            $this->telegramService->sendPaymentReport($sale);

            // Check for low stock and send alerts
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                if ($product && $product->isLowStock()) {
                    $this->telegramService->sendLowStockAlert($product);
                }
            }

            return response()->json([
                'success' => true,
                'sale_id' => $sale->id,
                'receipt_number' => $sale->receipt_number,
                'points_earned' => $pointsEarned,
                'points_redeemed' => $pointsRedeemed,
                'customer_total_points' => $customer->fresh()->loyalty_points,
                'customer_id' => $customer->id,
                'message' => 'Sale processed successfully',
                'redirect_url' => route('customers.show.success', $customer->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function printReceipt($saleId)
    {
        $sale = Sale::with(['customer', 'saleItems.product'])->findOrFail($saleId);
        return view('pos.receipt', compact('sale'));
    }
}
