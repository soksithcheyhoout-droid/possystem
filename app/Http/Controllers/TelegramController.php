<?php

namespace App\Http\Controllers;

use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class TelegramController extends Controller
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function settings()
    {
        $botToken = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');
        
        return view('admin.telegram.settings', compact('botToken', 'chatId'));
    }

    public function testConnection()
    {
        $result = $this->telegramService->testConnection();
        
        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }

    public function sendDailyReport()
    {
        // Calculate daily stats
        $today = now()->startOfDay();
        $sales = \App\Models\Sale::whereDate('sale_date', $today)->get();
        
        $stats = [
            'total_sales' => $sales->sum('total_amount'),
            'transaction_count' => $sales->count(),
            'items_sold' => $sales->sum(function($sale) {
                return $sale->saleItems->sum('quantity');
            }),
            'avg_transaction' => $sales->count() > 0 ? $sales->avg('total_amount') : 0,
            'top_products' => $this->getTopProductsToday()
        ];

        $result = $this->telegramService->sendDailySalesReport($stats);
        
        if ($result) {
            return redirect()->back()->with('success', 'Daily report sent successfully!');
        } else {
            return redirect()->back()->with('error', 'Failed to send daily report.');
        }
    }

    private function getTopProductsToday()
    {
        $today = now()->startOfDay();
        
        return \App\Models\SaleItem::select('product_id', DB::raw('SUM(quantity) as total_quantity'))
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

    public function getChatId()
    {
        $token = config('services.telegram.bot_token');
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Bot token not configured'
            ]);
        }

        try {
            $bot = new \TelegramBot\Api\BotApi($token);
            // Handle SSL issues in development
            if (config('app.env') === 'local') {
                $bot->setCurlOption(CURLOPT_SSL_VERIFYPEER, false);
                $bot->setCurlOption(CURLOPT_SSL_VERIFYHOST, false);
            }
            $updates = $bot->getUpdates();
            
            if (empty($updates)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No recent messages found. Please send a message to your bot first.',
                    'instructions' => [
                        'Send any message to your bot on Telegram',
                        'Then click this button again'
                    ]
                ]);
            }

            $chats = [];
            foreach ($updates as $update) {
                $chat = $update->getMessage()->getChat();
                $chatId = $chat->getId();
                $chatType = $chat->getType();
                $chatTitle = $chat->getTitle() ?: ($chat->getFirstName() . ' ' . $chat->getLastName());
                
                $chats[$chatId] = [
                    'id' => $chatId,
                    'type' => $chatType,
                    'title' => trim($chatTitle)
                ];
            }
            
            return response()->json([
                'success' => true,
                'chats' => array_values($chats)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function updateChatId(Request $request)
    {
        $request->validate([
            'chat_id' => 'required|string'
        ]);

        $chatId = $request->chat_id;
        
        // Update .env file
        $envFile = base_path('.env');
        $envContent = file_get_contents($envFile);
        
        $envContent = preg_replace(
            '/TELEGRAM_CHAT_ID=.*/',
            "TELEGRAM_CHAT_ID={$chatId}",
            $envContent
        );
        
        file_put_contents($envFile, $envContent);
        
        return redirect()->back()->with('success', 'Chat ID updated successfully! You can now test the connection.');
    }

    public function webhook(Request $request)
    {
        $update = $request->all();
        
        // Handle callback queries (button presses)
        if (isset($update['callback_query'])) {
            $this->telegramService->handleCallbackQuery($update['callback_query']);
        }
        
        // Handle regular messages if needed
        if (isset($update['message'])) {
            // You can add message handling logic here if needed
        }
        
        return response()->json(['ok' => true]);
    }
}
