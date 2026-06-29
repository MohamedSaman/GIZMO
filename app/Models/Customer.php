<?php

namespace App\Models;

use App\Models\Sale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;
    protected $table = "customers";
    protected $fillable = [
        'name',
        'phone',
        'email',
        'type',
        'address',
        'notes',
        'business_name',
        'created_by',
        'user_id',
        'opening_balance',
        'due_amount',
        'total_due',
        'overpaid_amount',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'total_due' => 'decimal:2',
        'overpaid_amount' => 'decimal:2',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function cheques()
    {
        return $this->hasMany(Cheque::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get due amount from unpaid sales
     */
    public function getDueAmountAttribute()
    {
        return $this->sales()->where('due_amount', '>', 0)->sum('due_amount');
    }

    /**
     * Get total due including opening balance and sale due amount, deducting overpayments
     */
    public function getTotalDueAttribute()
    {
        $total = ($this->attributes['opening_balance'] ?? 0) + $this->due_amount - ($this->attributes['overpaid_amount'] ?? 0);
        return max(0, $total);
    }
}
