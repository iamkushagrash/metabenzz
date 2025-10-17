<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PointsTransaction extends Model
{
    protected $fillable = [
        'userid',
        'amount',
        'type',
        'source',
        'reference_id',
        'created_at'
    ];
}