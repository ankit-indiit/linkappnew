<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'payments';
    const UPDATED_AT = null;
    protected $fillable = [
        'user_id',
        'payment_type',
        'package_id',
        'package_title',
        'package_month',
        'transaction_id',
        'amount',
        'subscription_id',
        'order_id',
        'plan_id',
        'product_id',
        'subscriber_account_email',
        'subscriber_payer_id',
        'start_date',
        'expiry_date',
        'status',
        'created_at',
    ];
}
