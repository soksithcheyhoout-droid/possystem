<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TelegramService;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

class SendDailyTelegramReport extends Command
{
    protected $signature = 'telegram:daily-report';
    protected $description = 'Send daily sales report to Telegram';

    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        parent::__construct();
        $this->telegramService = $telegramService;
    }

    public function handle()
    {
        $this->info('Generating daily sales report...');

        // Calculate daily stats
        $today = now()->startOfDay();
        $sales = Sale::whereDate('sale_date', $today)->get();
        
        $stats = [
            'total_sales' => $sales->sum('total_amount'),
            'transaction_count' => $sales->count(),
            'items_sold' => $sales->sum(function($sale) {
                return $sale->saleItems->sum('quantity');
            }),
            'avg_transaction' => $sales->count() > 0 ? round($sales->avg('total_amount'), 2) : 0,
            'top_products' => $this->getTopProductsToday()
        ];

        $result = $this->telegramService->sendDailySalesReport($stats);
        
        if ($result) {
            $this->info('Daily report sent successfully to Telegram!');
            $this->info("Total Sales: $" . number_format($stats['total_sales'], 2));
            $this->info("Transactions: " . $stats['transaction_count']);
            $this->info("Items Sold: " . $stats['items_sold']);
        } else {
            $this->error('Failed to send daily report to Telegram.');
            return 1;
        }

        return 0;
    }

    private function getTopProductsToday()
    {
        $today = now()->startOfDay();
        
        return SaleItem::select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->whereHas('sale', function($query) use ($today) {
                $query->whereDate('sale_date', $today);
            })
            ->with('product')
            ->groupBy('product_id')
            ->orderBy('total_quantity', 'desc')
            ->take(5)
            ->get()
            ->map(function($item) {
                return [
                    'name' => $item->product->name,
                    'quantity' => $item->total_quantity
                ];
            })
            ->toArray();
    }
}
