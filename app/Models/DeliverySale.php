<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeliverySale extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'delivery_method',
        'payment_method',
        'delivery_charge',
        'status',
        'delivery_barcode',
        'customer_details',
    ];

    /**
     * Boot the model and auto-generate delivery barcode on creation.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($deliverySale) {
            if (empty($deliverySale->delivery_barcode)) {
                $deliverySale->delivery_barcode = self::generateDeliveryBarcode();
            }
        });
    }

    /**
     * Generate a unique delivery barcode.
     * Format: DLV-YYYYMMDD-XXXXX (e.g., DLV-20260223-00001)
     */
    public static function generateDeliveryBarcode(): string
    {
        $prefix = 'DLV-' . date('Ymd') . '-';

        // Get the latest barcode for today
        $latestBarcode = self::where('delivery_barcode', 'like', $prefix . '%')
            ->orderBy('delivery_barcode', 'desc')
            ->value('delivery_barcode');

        if ($latestBarcode) {
            // Extract the numeric part and increment
            $lastNumber = (int) substr($latestBarcode, -5);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get the sale that this delivery belongs to.
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
