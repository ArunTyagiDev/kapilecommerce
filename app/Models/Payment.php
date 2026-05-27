<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'transaction_id',
        'payment_method',
        'amount',
        'status',
        'payment_response',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'payment_response' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function getPaymentMethodNameAttribute()
    {
        $methods = [
            'phonepe' => 'PhonePe',
            'paytm' => 'Paytm',
            'google_pay' => 'Google Pay',
            'cod' => 'Cash on Delivery',
        ];

        return $methods[$this->payment_method] ?? $this->payment_method;
    }
}

