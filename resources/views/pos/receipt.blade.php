<div class="receipt" style="font-family: monospace; max-width: 300px;">
    <div class="text-center mb-3">
        @if(isset($storeInfo['logo']) && $storeInfo['logo'])
            <img src="{{ asset('storage/' . $storeInfo['logo']) }}" 
                 alt="{{ $storeInfo['name'] ?? 'Store' }} Logo" 
                 style="max-height: 60px; max-width: 150px; object-fit: contain; margin-bottom: 10px;">
        @endif
        <h4>{{ strtoupper($storeInfo['name'] ?? 'MINI MART') }}</h4>
        @if(isset($storeInfo['tagline']) && $storeInfo['tagline'])
            <p class="mb-1"><em>{{ $storeInfo['tagline'] }}</em></p>
        @endif
        @if(isset($storeInfo['address']) && $storeInfo['address'])
            <p class="mb-1">{{ $storeInfo['address'] }}</p>
        @endif
        @if(isset($storeInfo['phone']) && $storeInfo['phone'])
            <p class="mb-1">Phone: {{ $storeInfo['phone'] }}</p>
        @endif
        @if(isset($storeInfo['email']) && $storeInfo['email'])
            <p class="mb-1">Email: {{ $storeInfo['email'] }}</p>
        @endif
        <hr>
    </div>
    
    <div class="mb-3">
        <p class="mb-1"><strong>Receipt #:</strong> {{ $sale->receipt_number }}</p>
        <p class="mb-1"><strong>Date:</strong> {{ $sale->sale_date->format('M d, Y H:i:s') }}</p>
        <p class="mb-1"><strong>Cashier:</strong> Admin</p>
        @if($sale->customer)
            <p class="mb-1"><strong>Customer:</strong> {{ $sale->customer->name ?: 'Guest' }}</p>
            <p class="mb-1"><strong>Phone:</strong> {{ $sale->customer->phone }}</p>
            @if($sale->customer->full_address)
                <p class="mb-1"><strong>Address:</strong> {{ $sale->customer->full_address }}</p>
            @endif
        @endif
        <hr>
    </div>
    
    <div class="mb-3">
        <table class="table table-sm" style="font-size: 12px;">
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Price</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->saleItems as $item)
                <tr>
                    <td>
                        {{ Str::limit($item->product->name, 15) }}
                        @if($item->product->hasDiscount())
                            <br><small class="text-success">{{ $item->product->discount_percentage }}% OFF</small>
                        @endif
                    </td>
                    <td class="text-end">{{ $item->quantity }}</td>
                    <td class="text-end">
                        @if($item->product->hasDiscount())
                            <span class="text-decoration-line-through text-muted" style="font-size: 10px;">
                                {{ $storeInfo['currency_symbol'] ?? '$' }}{{ number_format($item->product->price, 2) }}
                            </span><br>
                            <span class="text-success">{{ $storeInfo['currency_symbol'] ?? '$' }}{{ number_format($item->unit_price, 2) }}</span>
                        @else
                            {{ $storeInfo['currency_symbol'] ?? '$' }}{{ number_format($item->unit_price, 2) }}
                        @endif
                    </td>
                    <td class="text-end">{{ $storeInfo['currency_symbol'] ?? '$' }}{{ number_format($item->total_price, 2) }}</td>
                </tr>
                @if($item->product->hasDiscount())
                <tr>
                    <td colspan="3" class="text-end text-success" style="font-size: 10px;">
                        Saved: {{ $storeInfo['currency_symbol'] ?? '$' }}{{ number_format(($item->product->price - $item->product->getFinalPrice()) * $item->quantity, 2) }}
                    </td>
                    <td></td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
        <hr>
    </div>
    
    <div class="mb-3">
        <div class="d-flex justify-content-between">
            <span>Subtotal:</span>
            <span>{{ $storeInfo['currency_symbol'] ?? '$' }}{{ number_format($sale->subtotal, 2) }}</span>
        </div>
        
        @php
            $totalProductDiscounts = 0;
            foreach($sale->saleItems as $item) {
                if($item->product->hasDiscount()) {
                    $totalProductDiscounts += ($item->product->price - $item->product->getFinalPrice()) * $item->quantity;
                }
            }
        @endphp
        
        @if($totalProductDiscounts > 0)
        <div class="d-flex justify-content-between text-success">
            <span>Product Discounts:</span>
            <span>-{{ $storeInfo['currency_symbol'] ?? '$' }}{{ number_format($totalProductDiscounts, 2) }}</span>
        </div>
        @endif
        
        <div class="d-flex justify-content-between">
            <span>Tax:</span>
            <span>{{ $storeInfo['currency_symbol'] ?? '$' }}{{ number_format($sale->tax_amount, 2) }}</span>
        </div>
        @if($sale->discount_amount > 0)
        <div class="d-flex justify-content-between text-success">
            <span>Loyalty Discount:</span>
            <span>-{{ $storeInfo['currency_symbol'] ?? '$' }}{{ number_format($sale->discount_amount, 2) }}</span>
        </div>
        @endif
        <hr>
        <div class="d-flex justify-content-between">
            <strong>Total:</strong>
            <strong>{{ $storeInfo['currency_symbol'] ?? '$' }}{{ number_format($sale->total_amount, 2) }}</strong>
        </div>
        
        @if($totalProductDiscounts > 0)
        <div class="d-flex justify-content-between text-success">
            <strong>💰 You Saved:</strong>
            <strong>{{ $storeInfo['currency_symbol'] ?? '$' }}{{ number_format($totalProductDiscounts, 2) }}</strong>
        </div>
        <hr>
        @endif
        
        <div class="d-flex justify-content-between">
            <span>Paid ({{ ucfirst($sale->payment_method) }}):</span>
            <span>{{ $storeInfo['currency_symbol'] ?? '$' }}{{ number_format($sale->paid_amount, 2) }}</span>
        </div>
        <div class="d-flex justify-content-between">
            <span>Change:</span>
            <span>{{ $storeInfo['currency_symbol'] ?? '$' }}{{ number_format($sale->change_amount, 2) }}</span>
        </div>
    </div>
    
    @if($sale->customer)
    <div class="mb-3">
        <hr>
        <div class="text-center">
            <p class="mb-1"><strong>🌟 LOYALTY PROGRAM 🌟</strong></p>
            @if($sale->points_redeemed > 0)
                <p class="mb-1 text-success">Points Used: <strong>{{ number_format($sale->points_redeemed, 0) }}</strong></p>
                <p class="mb-1 text-success">Discount Applied: <strong>${{ number_format($sale->points_redeemed, 2) }}</strong></p>
            @endif
            <p class="mb-1">Points Earned: <strong>{{ number_format($sale->points_earned, 0) }}</strong></p>
            <p class="mb-1">Total Points: <strong>{{ number_format($sale->customer->loyalty_points, 0) }}</strong></p>
            @if($sale->customer->loyalty_points > 0)
                <p class="mb-1 text-success">Available Discount: <strong>${{ number_format($sale->customer->loyalty_points, 2) }}</strong></p>
            @endif
            <p class="mb-1" style="font-size: 10px;">1 point per $10 spent • 1 point = $1 discount</p>
        </div>
    </div>
    @endif
    
    <div class="text-center">
        <hr>
        <p class="mb-1">{{ $storeInfo['receipt_footer'] ?? 'Thank you for shopping with us!' }}</p>
        <p class="mb-1">Please come again</p>
        <hr>
    </div>
</div>