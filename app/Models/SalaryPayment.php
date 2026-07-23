<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryPayment extends Model
{
    use HasFactory;

    protected $table = 'salary_payments';

    protected $fillable = [
        'salary_id',
        'amount',
        'payment_date',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    /**
     * Get the salary record associated with this payment.
     */
    public function salary(): BelongsTo
    {
        return $this->belongsTo(Salary::class, 'salary_id', 'salary_id');
    }
}
