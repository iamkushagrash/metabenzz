<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LevelDetailsController extends Controller
{
    public function checkRank(){
        $getAllUsers=\App\UserDetails::where('userstatus','>',0)->orderBy('created_at','desc')->get();
        $getAllRank=\App\RankDetails::where('status',1)->get();
        foreach($getAllUsers as $user){
            $filterRank=$getAllRank->filter(function($q)use($user){
                return 1;
            });
        }
    }
}
