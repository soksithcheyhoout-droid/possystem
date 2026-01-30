<?php

namespace App\Services;

use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $bot;
    protected $chatId;

    public function __construct()
    {
        $token = config('services.telegram.bot_token');
        $this->chatId = config('services.telegram.chat_id');
        
        if ($token) {
            $this->bot = new BotApi($token);
            // Handle SSL issues in development
            if (config('app.env') === 'local') {
                $this->bot->setCurlOption(CURLOPT_SSL_VERIFYPEER, false);
                $this->bot->setCurlOption(CURLOPT_SSL_VERIFYHOST, false);
            }
        }
    }

    public function sendPaymentReport($sale)
    {
        if (!$this->bot || !$this->chatId) {
            Log::warning('Telegram bot not configured properly');
            return false;
        }

        try {
            $message = $this->formatPaymentMessage($sale);
            
            // Create inline keyboard with confirmation buttons using raw API
            $keyboard = [
                'inline_keyboard' => [
                    [
                        [
                            'text' => '✅ Receipt Confirmed',
                            'callback_data' => 'receipt_confirmed_' . $sale->id
                        ],
                        [
                            'text' => '📄 View Details',
                            'callback_data' => 'receipt_details_' . $sale->id
                        ]
                    ],
                    [
                        [
                            'text' => '🔄 Resend Receipt',
                            'callback_data' => 'resend_receipt_' . $sale->id
                        ]
                    ]
                ]
            ];
            
            // Use raw cURL to send message with keyboard
            $token = config('services.telegram.bot_token');
            $url = "https://api.telegram.org/bot{$token}/sendMessage";
            
            $postData = [
                'chat_id' => $this->chatId,
                'text' => $message,
                'parse_mode' => 'MarkdownV2',
                'reply_markup' => json_encode($keyboard)
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $result = json_decode($response, true);
                return $result['ok'] ?? false;
            }
            
            return false;
        } catch (Exception $e) {
            Log::error('Failed to send Telegram message: ' . $e->getMessage());
            return false;
        }
    }

    public function sendLowStockAlert($product)
    {
        if (!$this->bot || !$this->chatId) {
            return false;
        }

        try {
            $message = $this->formatLowStockMessage($product);
            $this->bot->sendMessage($this->chatId, $message, 'Markdown');
            return true;
        } catch (Exception $e) {
            Log::error('Failed to send low stock alert: ' . $e->getMessage());
            return false;
        }
    }

    public function sendDailySalesReport($stats)
    {
        if (!$this->bot || !$this->chatId) {
            return false;
        }

        try {
            $message = $this->formatDailySalesMessage($stats);
            $this->bot->sendMessage($this->chatId, $message, 'Markdown');
            return true;
        } catch (Exception $e) {
            Log::error('Failed to send daily sales report: ' . $e->getMessage());
            return false;
        }
    }

    private function formatPaymentMessage($sale)
    {
        $customerName = $sale->customer ? $sale->customer->name : 'Walk-in Customer';
        $paymentMethod = ucfirst(str_replace('_', ' ', $sale->payment_method));
        
        // Header
        $message = "```\n";
        $message .= "        MINI MART\n";
        $message .= "     123 Main Street\n";
        $message .= "   City, State 12345\n";
        $message .= " Phone: (555) 123-4567\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        // Receipt details
        $message .= "Receipt #: {$sale->receipt_number}\n";
        $message .= "Date: " . $sale->sale_date->format('M d, Y H:i:s') . "\n";
        $message .= "Cashier: Admin\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        // Items header
        $message .= "Item                 Qty  Price  Total\n\n";
        
        // Items list
        foreach ($sale->saleItems as $item) {
            $product = $item->product;
            $itemName = substr($product->name, 0, 18);
            $qty = $item->quantity;
            
            // Show original price if there's a discount
            if ($product->hasDiscount()) {
                $originalPrice = '$' . number_format($product->price, 2);
                $discountedPrice = '$' . number_format($product->getFinalPrice(), 2);
                $total = '$' . number_format($item->total_price, 2);
                
                // Show item with discount info
                $message .= sprintf("%-18s %3d  %5s  %5s\n", 
                    $itemName, $qty, $discountedPrice, $total);
                
                // Show discount line
                $discountAmount = ($product->price - $product->getFinalPrice()) * $qty;
                $message .= sprintf("  (-%s%% off %s)     -%s\n", 
                    $product->discount_percentage, 
                    $originalPrice,
                    '$' . number_format($discountAmount, 2));
            } else {
                $price = '$' . number_format($item->unit_price, 2);
                $total = '$' . number_format($item->total_price, 2);
                
                $message .= sprintf("%-18s %3d  %5s  %5s\n", 
                    $itemName, $qty, $price, $total);
            }
        }
        
        $message .= "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        // Totals
        $subtotal = number_format($sale->subtotal ?? ($sale->total_amount - ($sale->tax_amount ?? 0)), 2);
        $tax = number_format($sale->tax_amount ?? 0, 2);
        $discount = number_format($sale->discount_amount ?? 0, 2);
        $total = number_format($sale->total_amount, 2);
        $paid = number_format($sale->paid_amount, 2);
        $change = number_format($sale->change_amount, 2);
        
        // Calculate total savings from product discounts
        $totalSavings = 0;
        foreach ($sale->saleItems as $item) {
            $product = $item->product;
            if ($product->hasDiscount()) {
                $savingsPerItem = $product->price - $product->getFinalPrice();
                $totalSavings += $savingsPerItem * $item->quantity;
            }
        }
        
        $message .= sprintf("Subtotal:                    $%s\n", $subtotal);
        
        // Show discount savings if any
        if ($totalSavings > 0 || ($sale->discount_amount ?? 0) > 0) {
            if ($totalSavings > 0) {
                $message .= sprintf("Product Discounts:          -$%s\n", number_format($totalSavings, 2));
            }
            if (($sale->discount_amount ?? 0) > 0) {
                $message .= sprintf("Additional Discount:        -$%s\n", $discount);
            }
        }
        
        $message .= sprintf("Tax:                         $%s\n", $tax);
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= sprintf("Total:                       $%s\n", $total);
        
        // Show total savings summary if any
        if ($totalSavings > 0) {
            $message .= sprintf("💰 You Saved:                $%s\n", number_format($totalSavings, 2));
            $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        }
        
        $message .= sprintf("Paid (%s):                $%s\n", $paymentMethod, $paid);
        $message .= sprintf("Change:                      $%s\n", $change);
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        // Footer
        $message .= "  Thank you for shopping with us!\n";
        $message .= "       Please come again\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "```";
        
        return $message;
    }

    private function formatLowStockMessage($product)
    {
        $message = "⚠️ *LOW STOCK ALERT*\n\n";
        $message .= "📦 *Product:* {$product->name}\n";
        $message .= "🏷️ *Category:* {$product->category->name}\n";
        $message .= "📊 *Current Stock:* {$product->stock_quantity} units\n";
        $message .= "📉 *Minimum Stock:* {$product->min_stock} units\n";
        
        // Show pricing with discount info
        if ($product->hasDiscount()) {
            $message .= "💰 *Original Price:* \${$product->price}\n";
            $message .= "🏷️ *Discount:* {$product->discount_percentage}% OFF\n";
            $message .= "💵 *Current Price:* \${$product->getFinalPrice()}\n";
            $message .= "💸 *Savings:* \$" . number_format($product->price - $product->getFinalPrice(), 2) . "\n\n";
        } else {
            $message .= "💰 *Price:* \${$product->price}\n\n";
        }
        
        $message .= "🔄 *Action Required:* Please restock this item soon!";
        
        return $message;
    }

    private function formatDailySalesMessage($stats)
    {
        $message = "📈 *DAILY SALES REPORT*\n";
        $message .= "📅 *Date:* " . now()->format('M d, Y') . "\n\n";
        $message .= "💰 *Total Sales:* \${$stats['total_sales']}\n";
        $message .= "🛒 *Transactions:* {$stats['transaction_count']}\n";
        $message .= "📦 *Items Sold:* {$stats['items_sold']}\n";
        $message .= "💳 *Avg Transaction:* \${$stats['avg_transaction']}\n";
        
        // Add discount information if available
        if (isset($stats['total_discounts']) && $stats['total_discounts'] > 0) {
            $message .= "🏷️ *Total Discounts Given:* \${$stats['total_discounts']}\n";
            $message .= "💸 *Customer Savings:* \${$stats['total_discounts']}\n";
        }
        
        if (isset($stats['discount_percentage']) && $stats['discount_percentage'] > 0) {
            $message .= "📊 *Avg Discount Rate:* {$stats['discount_percentage']}%\n";
        }
        
        $message .= "\n";
        
        if (isset($stats['top_products']) && count($stats['top_products']) > 0) {
            $message .= "*🌟 Top Products Today:*\n";
            foreach ($stats['top_products'] as $index => $product) {
                $discountInfo = '';
                if (isset($product['has_discount']) && $product['has_discount']) {
                    $discountInfo = " (🏷️ {$product['discount_percentage']}% OFF)";
                }
                $message .= ($index + 1) . ". {$product['name']} ({$product['quantity']} sold){$discountInfo}\n";
            }
        }
        
        return $message;
    }

    public function testConnection()
    {
        if (!$this->bot || !$this->chatId) {
            return ['success' => false, 'message' => 'Bot token or chat ID not configured'];
        }

        try {
            $message = "```\n";
            $message .= "        MINI MART\n";
            $message .= "     123 Main Street\n";
            $message .= "   City, State 12345\n";
            $message .= " Phone: (555) 123-4567\n";
            $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            $message .= "🤖 TELEGRAM TEST MESSAGE\n\n";
            $message .= "Integration Status: WORKING ✅\n";
            $message .= "Test Date: " . now()->format('M d, Y H:i:s') . "\n";
            $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            $message .= "  Your POS system is ready!\n";
            $message .= "   Receipts will be sent here\n";
            $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $message .= "```";
            
            $this->bot->sendMessage($this->chatId, $message, 'MarkdownV2');
            return ['success' => true, 'message' => 'Test message sent successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to send test message: ' . $e->getMessage()];
        }
    }

    public function handleCallbackQuery($callbackQuery)
    {
        $data = $callbackQuery['data'];
        $messageId = $callbackQuery['message']['message_id'];
        $callbackQueryId = $callbackQuery['id'];
        
        try {
            $token = config('services.telegram.bot_token');
            
            if (strpos($data, 'receipt_confirmed_') === 0) {
                $saleId = str_replace('receipt_confirmed_', '', $data);
                
                // Update the keyboard to show "Done" instead of "Receipt Confirmed"
                $newKeyboard = [
                    'inline_keyboard' => [
                        [
                            [
                                'text' => '✅ Done',
                                'callback_data' => 'receipt_done_' . $saleId
                            ],
                            [
                                'text' => '📄 View Details',
                                'callback_data' => 'receipt_details_' . $saleId
                            ]
                        ],
                        [
                            [
                                'text' => '🔄 Resend Receipt',
                                'callback_data' => 'resend_receipt_' . $saleId
                            ]
                        ]
                    ]
                ];
                
                // Edit message keyboard only (keep original receipt)
                $editUrl = "https://api.telegram.org/bot{$token}/editMessageReplyMarkup";
                $editData = [
                    'chat_id' => $this->chatId,
                    'message_id' => $messageId,
                    'reply_markup' => json_encode($newKeyboard)
                ];
                
                $this->sendCurlRequest($editUrl, $editData);
                
                // Send callback answer
                $this->answerCallbackQuery($callbackQueryId, 'Receipt confirmed! ✅');
                
            } elseif (strpos($data, 'receipt_done_') === 0) {
                // If already done, just show a message
                $this->answerCallbackQuery($callbackQueryId, 'Receipt already confirmed ✅');
                
            } elseif (strpos($data, 'receipt_details_') === 0) {
                $saleId = str_replace('receipt_details_', '', $data);
                
                // Show processing first
                $this->answerCallbackQuery($callbackQueryId, 'Processing... ⏳');
                
                // Update button to show processing
                $processingKeyboard = [
                    'inline_keyboard' => [
                        [
                            [
                                'text' => '✅ Done',
                                'callback_data' => 'receipt_done_' . $saleId
                            ],
                            [
                                'text' => '⏳ Processing...',
                                'callback_data' => 'processing_details_' . $saleId
                            ]
                        ],
                        [
                            [
                                'text' => '🔄 Resend Receipt',
                                'callback_data' => 'resend_receipt_' . $saleId
                            ]
                        ]
                    ]
                ];
                
                $editUrl = "https://api.telegram.org/bot{$token}/editMessageReplyMarkup";
                $editData = [
                    'chat_id' => $this->chatId,
                    'message_id' => $messageId,
                    'reply_markup' => json_encode($processingKeyboard)
                ];
                
                $this->sendCurlRequest($editUrl, $editData);
                
                // Simulate processing delay
                sleep(2);
                
                // Send detailed information as a new message
                $detailMessage = "📄 *RECEIPT DETAILS*\n\n";
                $detailMessage .= "🆔 Sale ID: {$saleId}\n";
                $detailMessage .= "📅 Processed: " . now()->format('M d, Y H:i:s') . "\n";
                $detailMessage .= "💳 Payment Method: Cash\n";
                $detailMessage .= "📊 Status: Completed\n";
                $detailMessage .= "🏪 Store: Mini Mart\n";
                $detailMessage .= "👤 Cashier: Admin\n\n";
                $detailMessage .= "✅ All details processed successfully!";
                
                $sendUrl = "https://api.telegram.org/bot{$token}/sendMessage";
                $sendData = [
                    'chat_id' => $this->chatId,
                    'text' => $detailMessage,
                    'parse_mode' => 'Markdown'
                ];
                
                $this->sendCurlRequest($sendUrl, $sendData);
                
                // Restore original keyboard
                $originalKeyboard = [
                    'inline_keyboard' => [
                        [
                            [
                                'text' => '✅ Done',
                                'callback_data' => 'receipt_done_' . $saleId
                            ],
                            [
                                'text' => '📄 View Details',
                                'callback_data' => 'receipt_details_' . $saleId
                            ]
                        ],
                        [
                            [
                                'text' => '🔄 Resend Receipt',
                                'callback_data' => 'resend_receipt_' . $saleId
                            ]
                        ]
                    ]
                ];
                
                $editData['reply_markup'] = json_encode($originalKeyboard);
                $this->sendCurlRequest($editUrl, $editData);
                
            } elseif (strpos($data, 'resend_receipt_') === 0) {
                $saleId = str_replace('resend_receipt_', '', $data);
                
                // Show processing first
                $this->answerCallbackQuery($callbackQueryId, 'Processing resend... ⏳');
                
                // Update button to show processing
                $processingKeyboard = [
                    'inline_keyboard' => [
                        [
                            [
                                'text' => '✅ Done',
                                'callback_data' => 'receipt_done_' . $saleId
                            ],
                            [
                                'text' => '📄 View Details',
                                'callback_data' => 'receipt_details_' . $saleId
                            ]
                        ],
                        [
                            [
                                'text' => '⏳ Processing...',
                                'callback_data' => 'processing_resend_' . $saleId
                            ]
                        ]
                    ]
                ];
                
                $editUrl = "https://api.telegram.org/bot{$token}/editMessageReplyMarkup";
                $editData = [
                    'chat_id' => $this->chatId,
                    'message_id' => $messageId,
                    'reply_markup' => json_encode($processingKeyboard)
                ];
                
                $this->sendCurlRequest($editUrl, $editData);
                
                // Simulate processing delay
                sleep(2);
                
                // Find and resend the receipt
                $sale = \App\Models\Sale::find($saleId);
                if ($sale) {
                    $this->sendPaymentReport($sale);
                    
                    // Send confirmation message
                    $confirmMessage = "🔄 *RECEIPT RESENT*\n\n";
                    $confirmMessage .= "📋 Receipt for Sale ID: {$saleId}\n";
                    $confirmMessage .= "📤 Resent at: " . now()->format('M d, Y H:i:s') . "\n";
                    $confirmMessage .= "✅ New receipt sent successfully!";
                    
                    $sendUrl = "https://api.telegram.org/bot{$token}/sendMessage";
                    $sendData = [
                        'chat_id' => $this->chatId,
                        'text' => $confirmMessage,
                        'parse_mode' => 'Markdown'
                    ];
                    
                    $this->sendCurlRequest($sendUrl, $sendData);
                } else {
                    // Send error message
                    $errorMessage = "❌ *ERROR*\n\nSale ID {$saleId} not found!\nCannot resend receipt.";
                    
                    $sendUrl = "https://api.telegram.org/bot{$token}/sendMessage";
                    $sendData = [
                        'chat_id' => $this->chatId,
                        'text' => $errorMessage,
                        'parse_mode' => 'Markdown'
                    ];
                    
                    $this->sendCurlRequest($sendUrl, $sendData);
                }
                
                // Restore original keyboard
                $originalKeyboard = [
                    'inline_keyboard' => [
                        [
                            [
                                'text' => '✅ Done',
                                'callback_data' => 'receipt_done_' . $saleId
                            ],
                            [
                                'text' => '📄 View Details',
                                'callback_data' => 'receipt_details_' . $saleId
                            ]
                        ],
                        [
                            [
                                'text' => '🔄 Resend Receipt',
                                'callback_data' => 'resend_receipt_' . $saleId
                            ]
                        ]
                    ]
                ];
                
                $editData['reply_markup'] = json_encode($originalKeyboard);
                $this->sendCurlRequest($editUrl, $editData);
            }
            
            return true;
        } catch (Exception $e) {
            Log::error('Failed to handle callback query: ' . $e->getMessage());
            return false;
        }
    }

    private function sendCurlRequest($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response;
    }

    private function answerCallbackQuery($callbackQueryId, $text, $showAlert = false)
    {
        $token = config('services.telegram.bot_token');
        $url = "https://api.telegram.org/bot{$token}/answerCallbackQuery";
        
        $data = [
            'callback_query_id' => $callbackQueryId,
            'text' => $text,
            'show_alert' => $showAlert
        ];
        
        return $this->sendCurlRequest($url, $data);
    }
}