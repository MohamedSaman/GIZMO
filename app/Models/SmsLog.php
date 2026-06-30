<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SmsLog extends Model
{
    use HasFactory;

    protected $table = 'sms_logs';

    protected $fillable = [
        'customer_id',
        'user_id',
        'phone',
        'message',
        'sms_parts',
        'cost_per_sms',
        'total_cost',
        'double_charged',
        'balance_before',
        'balance_after',
        'type',
        'status',
        'api_response',
        'sent_at',
    ];

    protected $casts = [
        'cost_per_sms'    => 'decimal:4',
        'total_cost'      => 'decimal:4',
        'balance_before'  => 'decimal:4',
        'balance_after'   => 'decimal:4',
        'double_charged'  => 'boolean',
        'sent_at'         => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get logs for a specific month (Y-m format e.g. "2026-06")
     */
    public function scopeForMonth($query, string $month)
    {
        return $query->where('created_at', 'like', $month . '%');
    }

    /**
     * Get logs for a specific user in a month
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
