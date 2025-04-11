<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransactionHistroy extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'payment_id', 'amount', 'status', 'response','user_balance','type'
    ];
}
