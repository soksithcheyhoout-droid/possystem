<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'house_number',
        'street',
        'address',
        'loyalty_points',
        'points_earned',
        'points_redeemed'
    ];

    protected $casts = [
        'loyalty_points' => 'decimal:2',
        'points_earned' => 'decimal:2',
        'points_redeemed' => 'decimal:2'
    ];

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Add loyalty points based on purchase amount
     * 1 point for every $10 spent, 1 point = $1 value
     */
    public function addLoyaltyPoints(float $purchaseAmount): void
    {
        $pointsToAdd = floor($purchaseAmount / 10); // 1 point per $10 spent
        $this->increment('loyalty_points', $pointsToAdd);
        $this->increment('points_earned', $pointsToAdd);
    }

    /**
     * Redeem loyalty points for discount
     * 1 point = $1 discount
     */
    public function redeemPoints(int $pointsToRedeem): float
    {
        if ($pointsToRedeem > $this->loyalty_points) {
            throw new \Exception('Insufficient loyalty points');
        }

        $discountAmount = $pointsToRedeem; // 1 point = $1
        $this->decrement('loyalty_points', $pointsToRedeem);
        $this->increment('points_redeemed', $pointsToRedeem);

        return $discountAmount;
    }

    /**
     * Get available discount amount from loyalty points
     */
    public function getAvailableDiscountAttribute(): float
    {
        return $this->loyalty_points; // 1 point = $1 discount
    }

    /**
     * Get full address
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->house_number,
            $this->street,
            $this->address
        ]);
        
        return implode(', ', $parts);
    }
}
