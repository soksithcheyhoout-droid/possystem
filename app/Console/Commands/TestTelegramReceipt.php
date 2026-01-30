<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TelegramService;
use App\Models\Sale;
use App\Models\Product;
use App\Models\SaleItem;

class TestTelegramReceipt extends Command
{
    protected $signature = 'telegram:test-receipt';
    protected $description = 'Send a test receipt to Telegram with the new format';

    public function handle()
    {
        $telegramService = new TelegramService();
        
        // Create a mock sale object for testing
        $sale = new Sale();
        $sale->id = 999; // Mock ID for testing
        $sale->receipt_number = 'RCP-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $sale->total_amount = 2.85;
        $sale->paid_amount = 5.00;
        $sale->change_amount = 2.15;
        $sale->tax_amount = 0.15;
        $sale->subtotal = 3.00;
        $sale->discount_amount = 0.30; // Additional discount
        $sale->payment_method = 'cash';
        $sale->sale_date = now();
        
        // Create mock sale items with discounts
        $product1 = new Product();
        $product1->name = 'Coca Cola 500ml';
        $product1->price = 2.00;
        $product1->discount_percentage = 25.00;
        $product1->discounted_price = 1.50;
        
        $saleItem1 = new SaleItem();
        $saleItem1->quantity = 1;
        $saleItem1->unit_price = 1.50; // Discounted price
        $saleItem1->total_price = 1.50;
        $saleItem1->product = $product1;
        
        $product2 = new Product();
        $product2->name = 'Chips Regular';
        $product2->price = 1.50;
        $product2->discount_percentage = 0; // No discount
        $product2->discounted_price = null;
        
        $saleItem2 = new SaleItem();
        $saleItem2->quantity = 1;
        $saleItem2->unit_price = 1.50;
        $saleItem2->total_price = 1.50;
        $saleItem2->product = $product2;
        
        $sale->saleItems = collect([$saleItem1, $saleItem2]);
        
        $result = $telegramService->sendPaymentReport($sale);
        
        if ($result) {
            $this->info('✅ Test receipt with discount information sent successfully to Telegram!');
            $this->info('Check your Telegram chat to see the new receipt format with:');
            $this->info('  - Individual product discounts');
            $this->info('  - Total savings calculation');
            $this->info('  - Interactive confirmation buttons');
            $this->info('You can click the buttons to test the confirmation functionality.');
        } else {
            $this->error('❌ Failed to send test receipt to Telegram.');
            $this->error('Please check your Telegram configuration.');
        }
    }
}