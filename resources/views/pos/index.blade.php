@extends('layouts.app')

@section('title', 'POS System')
@section('page-title', 'Point of Sale System')

@push('styles')
<style>
    .pos-container {
        height: calc(100vh - 120px);
    }
    .product-grid {
        max-height: 70vh;
        overflow-y: auto;
    }
    .product-card {
        cursor: pointer;
        transition: all 0.3s;
        border: 2px solid transparent;
    }
    .product-card:hover {
        transform: translateY(-2px);
        border-color: #667eea;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }
    .cart-section {
        background: white;
        border-radius: 15px;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
        max-height: 80vh;
        overflow-y: auto;
    }
    .cart-item {
        border-bottom: 1px solid #eee;
        padding: 10px 0;
    }
    .cart-item:last-child {
        border-bottom: none;
    }
    .category-btn {
        margin: 2px;
        border-radius: 20px;
    }
    .category-btn.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .total-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-top: 20px;
    }
    .customer-section .card {
        border: 1px solid #e3f2fd;
        background: #f8f9ff;
    }
    .customer-section .card-header {
        background: #e3f2fd;
        border-bottom: 1px solid #bbdefb;
    }
</style>
@endpush

@section('content')
<div class="pos-container">
    <div class="row h-100">
        <!-- Products Section -->
        <div class="col-md-8">
            <!-- Search and Categories -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" id="productSearch" class="form-control" placeholder="Search products or scan barcode...">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="category-filters">
                                <button class="btn btn-outline-primary category-btn active" data-category="">All</button>
                                @foreach($categories as $category)
                                    <button class="btn btn-outline-primary category-btn" data-category="{{ $category->id }}">
                                        {{ $category->name }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Products Grid -->
            <div class="product-grid">
                <div class="row" id="productsContainer">
                    @foreach($products as $product)
                    <div class="col-md-3 col-sm-4 col-6 mb-3 product-item" data-category="{{ $product->category_id }}">
                        <div class="card product-card h-100" data-product-id="{{ $product->id }}" 
                             data-product-name="{{ $product->name }}" 
                             data-product-price="{{ $product->getFinalPrice() }}"
                             data-product-original-price="{{ $product->price }}"
                             data-product-discount="{{ $product->discount_percentage }}"
                             data-product-stock="{{ $product->stock_quantity }}">
                            <div class="card-body text-center p-2">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" class="img-fluid mb-2" style="height: 60px; object-fit: cover;">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center mb-2" style="height: 60px;">
                                        <i class="fas fa-box text-muted"></i>
                                    </div>
                                @endif
                                <h6 class="card-title mb-1" style="font-size: 0.9rem;">{{ Str::limit($product->name, 20) }}</h6>
                                <p class="text-muted mb-1" style="font-size: 0.8rem;">{{ $product->category->name }}</p>
                                @if($product->hasDiscount())
                                    <div class="mb-1">
                                        <span class="text-decoration-line-through text-muted" style="font-size: 0.8rem;">${{ number_format($product->price, 2) }}</span>
                                        <span class="badge bg-danger ms-1">{{ $product->discount_percentage }}% OFF</span>
                                    </div>
                                    <p class="text-success fw-bold mb-1">${{ number_format($product->getFinalPrice(), 2) }}</p>
                                @else
                                    <p class="text-primary fw-bold mb-1">${{ number_format($product->price, 2) }}</p>
                                @endif
                                <small class="text-muted">Stock: {{ $product->stock_quantity }}</small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <!-- Cart Section -->
        <div class="col-md-4">
            <div class="cart-section p-3">
                <h5 class="mb-3">
                    <i class="fas fa-shopping-cart me-2"></i>Shopping Cart
                </h5>
                
                <!-- Customer Information -->
                <div class="customer-section mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-user me-2"></i>Customer Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <input type="text" class="form-control form-control-sm" id="customerPhone" placeholder="Phone Number *">
                            </div>
                            <div class="row mb-2">
                                <div class="col-6">
                                    <input type="text" class="form-control form-control-sm" id="customerHouseNumber" placeholder="House #">
                                </div>
                                <div class="col-6">
                                    <input type="text" class="form-control form-control-sm" id="customerStreet" placeholder="Street">
                                </div>
                            </div>
                            <div class="mb-2">
                                <input type="text" class="form-control form-control-sm" id="customerName" placeholder="Customer Name (Optional)">
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-star text-warning me-1"></i>
                                    Points: <span id="customerPoints">0</span>
                                </small>
                                <button class="btn btn-sm btn-outline-primary" id="findCustomer">
                                    <i class="fas fa-search me-1"></i>Find
                                </button>
                            </div>
                            <div class="mt-2" id="loyaltySection" style="display: none;">
                                <div class="alert alert-info py-2 mb-2">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small>
                                            <i class="fas fa-gift me-1"></i>
                                            Available Points: <span id="availablePoints">0</span>
                                        </small>
                                        <small class="text-success">
                                            Max Discount: $<span id="maxDiscount">0.00</span>
                                        </small>
                                    </div>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Use Points:</span>
                                        <input type="number" class="form-control" id="pointsToUse" min="0" max="0" step="1" placeholder="0">
                                        <span class="input-group-text">= $<span id="pointsValue">0.00</span></span>
                                        <button class="btn btn-success" id="applyPoints">Apply</button>
                                    </div>
                                    <div class="mt-1">
                                        <small class="text-muted">
                                            <button type="button" class="btn btn-link btn-sm p-0" id="useAllPoints">Use All Points</button>
                                            | <button type="button" class="btn btn-link btn-sm p-0" id="clearPoints">Clear</button>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-info">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Earn 1 point per $10 spent • 1 point = $1 discount
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Cart Items -->
                <div id="cartItems" style="min-height: 200px;">
                    <p class="text-muted text-center py-5">Cart is empty</p>
                </div>
                
                <!-- Cart Summary -->
                <div class="total-section">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span id="subtotal">$0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax (10%):</span>
                        <span id="tax">$0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Loyalty Discount:</span>
                        <span id="loyaltyDiscountAmount">-$0.00</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total:</strong>
                        <strong id="total">$0.00</strong>
                    </div>
                    
                    <!-- Payment Section -->
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select class="form-select" id="paymentMethod">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="digital_wallet">Digital Wallet</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Amount Paid</label>
                        <input type="number" class="form-control" id="paidAmount" step="0.01" min="0">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Change</label>
                        <input type="text" class="form-control" id="changeAmount" readonly>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button class="btn btn-light btn-lg" id="processPayment" disabled>
                            <i class="fas fa-credit-card me-2"></i>Process Payment
                        </button>
                        <button class="btn btn-outline-light" id="clearCart">
                            <i class="fas fa-trash me-2"></i>Clear Cart
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Receipt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="receiptContent">
                <!-- Receipt content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="window.print()">Print</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let cart = [];
    let currentCustomer = null;
    let loyaltyDiscount = 0;
    const TAX_RATE = 0.10;
    
    // Add product to cart
    $('.product-card').click(function() {
        const productId = $(this).data('product-id');
        const productName = $(this).data('product-name');
        const productPrice = parseFloat($(this).data('product-price'));
        const productStock = parseInt($(this).data('product-stock'));
        const productImage = $(this).find('img').attr('src') || null;
        
        if (productStock <= 0) {
            alert('Product is out of stock!');
            return;
        }
        
        const existingItem = cart.find(item => item.id === productId);
        
        if (existingItem) {
            if (existingItem.quantity >= productStock) {
                alert('Cannot add more items. Stock limit reached!');
                return;
            }
            existingItem.quantity++;
        } else {
            cart.push({
                id: productId,
                name: productName,
                price: productPrice,
                quantity: 1,
                stock: productStock,
                image: productImage
            });
        }
        
        updateCartDisplay();
        updateTotals();
    });
    
    // Update cart display with product images
    function updateCartDisplay() {
        const cartContainer = $('#cartItems');
        
        if (cart.length === 0) {
            cartContainer.html('<p class="text-muted text-center py-5">Cart is empty</p>');
            return;
        }
        
        let cartHtml = '';
        cart.forEach((item, index) => {
            const imageHtml = item.image ? 
                `<img src="${item.image}" class="img-thumbnail me-2" style="width: 50px; height: 50px; object-fit: cover;">` : 
                `<div class="bg-light d-flex align-items-center justify-content-center me-2" style="width: 50px; height: 50px; border-radius: 8px;"><i class="fas fa-box text-muted"></i></div>`;
            
            cartHtml += `
                <div class="cart-item">
                    <div class="d-flex align-items-center mb-2">
                        ${imageHtml}
                        <div class="flex-grow-1">
                            <h6 class="mb-1" style="font-size: 0.9rem;">${item.name}</h6>
                            <small class="text-muted">$${item.price.toFixed(2)} each</small>
                        </div>
                        <button class="btn btn-sm btn-outline-danger" onclick="removeItem(${index})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <button class="btn btn-sm btn-outline-secondary me-2" onclick="updateQuantity(${index}, -1)">-</button>
                            <span class="mx-2 fw-bold">${item.quantity}</span>
                            <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${index}, 1)">+</button>
                        </div>
                        <div class="text-end">
                            <strong class="text-primary">$${(item.price * item.quantity).toFixed(2)}</strong>
                        </div>
                    </div>
                </div>
            `;
        });
        
        cartContainer.html(cartHtml);
    }
    
    // Find customer by phone
    $('#findCustomer').click(function() {
        const phone = $('#customerPhone').val().trim();
        if (!phone) {
            alert('Please enter a phone number');
            return;
        }
        
        $.ajax({
            url: '{{ route("pos.find-customer") }}',
            method: 'GET',
            data: { phone: phone },
            success: function(response) {
                if (response.success && response.customer) {
                    currentCustomer = response.customer;
                    $('#customerName').val(currentCustomer.name || '');
                    $('#customerHouseNumber').val(currentCustomer.house_number || '');
                    $('#customerStreet').val(currentCustomer.street || '');
                    $('#customerPoints').text(currentCustomer.loyalty_points || 0);
                    
                    const availablePoints = Math.floor(currentCustomer.loyalty_points || 0);
                    if (availablePoints > 0) {
                        $('#availablePoints').text(availablePoints);
                        $('#maxDiscount').text(availablePoints.toFixed(2));
                        $('#pointsToUse').attr('max', availablePoints);
                        $('#loyaltySection').show();
                    } else {
                        $('#loyaltySection').hide();
                    }
                } else {
                    // New customer
                    currentCustomer = null;
                    $('#customerPoints').text('0');
                    $('#loyaltySection').hide();
                    alert('Customer not found. Will create new customer record.');
                }
            },
            error: function() {
                alert('Error finding customer');
            }
        });
    });
    
    // Update points value when input changes
    $('#pointsToUse').on('input', function() {
        const pointsToUse = parseInt($(this).val()) || 0;
        const maxPoints = parseInt($(this).attr('max')) || 0;
        
        if (pointsToUse > maxPoints) {
            $(this).val(maxPoints);
            $('#pointsValue').text(maxPoints.toFixed(2));
        } else {
            $('#pointsValue').text(pointsToUse.toFixed(2));
        }
    });
    
    // Apply points as discount
    $('#applyPoints').click(function() {
        if (!currentCustomer) return;
        
        const pointsToUse = parseInt($('#pointsToUse').val()) || 0;
        const maxPoints = Math.floor(currentCustomer.loyalty_points || 0);
        
        if (pointsToUse > maxPoints) {
            alert('Cannot use more points than available!');
            return;
        }
        
        if (pointsToUse > 0) {
            loyaltyDiscount = pointsToUse; // 1 point = $1 discount
            updateTotals();
            $(this).prop('disabled', true).text('Applied');
            $('#pointsToUse').prop('disabled', true);
        }
    });
    
    // Use all available points
    $('#useAllPoints').click(function() {
        if (!currentCustomer) return;
        
        const maxPoints = Math.floor(currentCustomer.loyalty_points || 0);
        if (maxPoints > 0) {
            $('#pointsToUse').val(maxPoints);
            $('#pointsValue').text(maxPoints.toFixed(2));
        }
    });
    
    // Clear points selection
    $('#clearPoints').click(function() {
        $('#pointsToUse').val('').prop('disabled', false);
        $('#pointsValue').text('0.00');
        $('#applyPoints').prop('disabled', false).text('Apply');
        loyaltyDiscount = 0;
        updateTotals();
    });
    
    // Update quantity
    window.updateQuantity = function(index, change) {
        const item = cart[index];
        const newQuantity = item.quantity + change;
        
        if (newQuantity <= 0) {
            cart.splice(index, 1);
        } else if (newQuantity <= item.stock) {
            item.quantity = newQuantity;
        } else {
            alert('Cannot exceed stock limit!');
            return;
        }
        
        updateCartDisplay();
        updateTotals();
    };
    
    // Remove item
    window.removeItem = function(index) {
        cart.splice(index, 1);
        updateCartDisplay();
        updateTotals();
    };
    
    // Update totals
    function updateTotals() {
        const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const tax = subtotal * TAX_RATE;
        const total = subtotal + tax - loyaltyDiscount;
        
        $('#subtotal').text('$' + subtotal.toFixed(2));
        $('#tax').text('$' + tax.toFixed(2));
        $('#loyaltyDiscountAmount').text('-$' + loyaltyDiscount.toFixed(2));
        $('#total').text('$' + total.toFixed(2));
        
        $('#processPayment').prop('disabled', cart.length === 0);
    }
    
    // Calculate change
    $('#paidAmount').on('input', function() {
        const total = parseFloat($('#total').text().replace('$', ''));
        const paid = parseFloat($(this).val()) || 0;
        const change = paid - total;
        
        $('#changeAmount').val(change >= 0 ? '$' + change.toFixed(2) : '$0.00');
        $('#processPayment').prop('disabled', change < 0 || cart.length === 0);
    });
    
    // Process payment
    $('#processPayment').click(function() {
        if (cart.length === 0) {
            alert('Cart is empty!');
            return;
        }
        
        const phone = $('#customerPhone').val().trim();
        if (!phone) {
            alert('Please enter customer phone number for loyalty points!');
            return;
        }
        
        const subtotal = parseFloat($('#subtotal').text().replace('$', ''));
        const tax = parseFloat($('#tax').text().replace('$', ''));
        const total = parseFloat($('#total').text().replace('$', ''));
        const paid = parseFloat($('#paidAmount').val()) || 0;
        
        if (paid < total) {
            alert('Insufficient payment amount!');
            return;
        }
        
        const saleData = {
            items: cart.map(item => ({
                product_id: item.id,
                quantity: item.quantity
            })),
            customer: {
                phone: phone,
                name: $('#customerName').val().trim(),
                house_number: $('#customerHouseNumber').val().trim(),
                street: $('#customerStreet').val().trim()
            },
            subtotal: subtotal,
            tax_amount: tax,
            loyalty_discount: loyaltyDiscount,
            total_amount: total,
            paid_amount: paid,
            payment_method: $('#paymentMethod').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        
        $.ajax({
            url: '{{ route("pos.process-sale") }}',
            method: 'POST',
            data: saleData,
            success: function(response) {
                if (response.success) {
                    let message = `Sale processed successfully!\n`;
                    if (response.points_redeemed > 0) {
                        message += `Points used: ${response.points_redeemed} (saved $${response.points_redeemed.toFixed(2)})\n`;
                    }
                    message += `Points earned: ${response.points_earned}\n`;
                    message += `Total points: ${response.customer_total_points}`;
                    
                    alert(message);
                    
                    // Show receipt first
                    loadReceipt(response.sale_id);
                    
                    // Clear cart and reset
                    cart = [];
                    currentCustomer = null;
                    loyaltyDiscount = 0;
                    updateCartDisplay();
                    updateTotals();
                    $('#paidAmount').val('');
                    $('#changeAmount').val('');
                    $('#customerPhone, #customerName, #customerHouseNumber, #customerStreet').val('');
                    $('#customerPoints').text('0');
                    $('#loyaltySection').hide();
                    $('#pointsToUse').val('').prop('disabled', false);
                    $('#pointsValue').text('0.00');
                    $('#applyPoints').prop('disabled', false).text('Apply');
                    
                    // Add button to view customer details
                    setTimeout(function() {
                        if (confirm('Payment successful! Would you like to view customer details?')) {
                            window.open(response.redirect_url, '_blank');
                        }
                    }, 2000);
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Error processing sale: ' + xhr.responseJSON.message);
            }
        });
    });
    
    // Load receipt
    function loadReceipt(saleId) {
        $.get(`/pos/receipt/${saleId}`, function(data) {
            $('#receiptContent').html(data);
            $('#receiptModal').modal('show');
        });
    }
    
    // Clear cart
    $('#clearCart').click(function() {
        if (confirm('Are you sure you want to clear the cart?')) {
            cart = [];
            loyaltyDiscount = 0;
            updateCartDisplay();
            updateTotals();
            $('#paidAmount').val('');
            $('#changeAmount').val('');
            $('#pointsToUse').val('').prop('disabled', false);
            $('#pointsValue').text('0.00');
            $('#applyPoints').prop('disabled', false).text('Apply');
        }
    });
    
    // Category filter
    $('.category-btn').click(function() {
        $('.category-btn').removeClass('active');
        $(this).addClass('active');
        
        const categoryId = $(this).data('category');
        
        if (categoryId === '') {
            $('.product-item').show();
        } else {
            $('.product-item').hide();
            $(`.product-item[data-category="${categoryId}"]`).show();
        }
    });
    
    // Product search
    $('#productSearch').on('input', function() {
        const query = $(this).val().toLowerCase();
        
        $('.product-item').each(function() {
            const productName = $(this).find('.card-title').text().toLowerCase();
            if (productName.includes(query)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});
</script>
@endpush