<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LockMaturityHistory extends Model
{
    protected $table = 'lock_maturity_histories';

    protected $fillable = [
        'deposit_id',
        'userid',
        'planid',
        'invest_type',
        'amount',
        'maturity_amount',
        'price_at_maturity',
        'status',
        'created_at',
    ];

    public $timestamps = true;
}
