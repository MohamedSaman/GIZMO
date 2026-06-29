<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductSupplier extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'businessname', 'contact', 'address', 'email', 'phone', 'status', 'notes', 'overpayment', 'opening_balance'];

    protected $casts = [
        'overpayment' => 'decimal:2',
        'opening_balance' => 'decimal:2',
    ];

    public function detail()
    {
        return $this->hasOne(ProductDetail::class, 'code');
    }
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'supplier_id');
    }
    public function orders()
    {
        return $this->hasMany(\App\Models\PurchaseOrder::class, 'supplier_id');
    }

    /**
     * Add overpayment credit to supplier
     */
    public function addOverpayment($amount)
    {
        $this->overpayment += $amount;
        $this->save();
    }

    /**
     * Use overpayment credit from supplier
     */
    public function useOverpayment($amount)
    {
        $usedAmount = min($this->overpayment, $amount);
        $this->overpayment -= $usedAmount;
        $this->save();
        return $usedAmount;
    }

    /**
     * Get available overpayment credit
     */
    public function getAvailableOverpayment()
    {
        return $this->overpayment ?? 0;
    }

    /**
     * Get due amount from unpaid orders
     */
    public function getDueAmountAttribute()
    {
        return $this->orders()->where('due_amount', '>', 0)->sum('due_amount');
    }

    /**
     * Get total due including opening balance and order due amount
     */
    public function getTotalDueAttribute()
    {
        return ($this->opening_balance ?? 0) + $this->due_amount;
    }
}
