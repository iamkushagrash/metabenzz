<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RankDetails extends Model
{
    public $timestamps=false;

    protected $fillable=['topup_amount','firstline','secondline','teamsize','direct_business','direct_count','cps_amount','leg_count','rank','status','created_at','updated_at','rank_id','rank_name'];

}
