<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PointsWallet extends Model
{
     protected $fillable = [
        'userid',
        'balance',
    ];
}
