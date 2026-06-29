<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoricalCheque extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'party_name',
        'cheque_number',
        'bank_name',
        'cheque_date',
        'cheque_amount',
        'status',
        'note',
    ];
}
