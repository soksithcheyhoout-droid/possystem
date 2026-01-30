<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Customer;

class SampleSalesSeeder extends Seeder
{
    public function run(): void
    {
        // Create a sample customer (or get existing one)
        $customer = Customer::firstOrCreate(
            ['email' => 'john@example.com'],
            [
                'name' => 'John Doe',
                'phone' => '+1234567890',
                'address' => '123 Main St, City, State',
                'loyalty_points' => 0
            ]
        );

        // Get some products
        $products = Product::take(5)->get();
        
        if ($products->count() == 0) {
            $this->command->info('No products found. Please run ProductSeeder first.');
            return;
        }

        // Create sample sales for today
        $existingSalesCount = Sale::whereDate('created_at', today())->count();
        
        for ($i = 1; $i <= 3; $i++) {
            $subtotal = 0;
            $receiptNumber = 'RCP-' . date('Ymd') . '-' . str_pad($existingSalesCount + $i, 4, '0', STR_PAD_LEFT);
            
            $sale = Sale::create([
                'receipt_number' => $receiptNumber,
                'customer_id' => $i == 1 ? $customer->id : null, // First sale has customer
                'subtotal' => 0, // Will be calculated
                'tax_amount' => 0, // Will be calculated
                'discount_amount' => 0,
                'total_amount' => 0, // Will be calculated
                'paid_amount' => 0, // Will be calculated
                'change_amount' => 0,
                'payment_method' => ['cash', 'card', 'digital_wallet'][array_rand(['cash', 'card', 'digital_wallet'])],
                'status' => 'completed',
                'sale_date' => now()->subHours(rand(1, 8)) // Random time today
            ]);

            // Add 2-4 random items to each sale
            $itemCount = rand(2, 4);
            $selectedProducts = $products->random($itemCount);
            
            foreach ($selectedProducts as $product) {
                $quantity = rand(1, 3);
                $unitPrice = $product->price;
                $totalPrice = $unitPrice * $quantity;
                $subtotal += $totalPrice;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice
                ]);
            }

            // Update sale totals
            $taxAmount = $subtotal * 0.10; // 10% tax
            $totalAmount = $subtotal + $taxAmount;
            $paidAmount = $totalAmount + rand(0, 500) / 100; // Add some change

            $sale->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'change_amount' => $paidAmount - $totalAmount
            ]);
        }

        $this->command->info('Created 3 sample sales with items.');
    }
}
