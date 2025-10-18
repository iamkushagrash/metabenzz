<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RankDetailsController extends Controller
{
    public function checkRank(){
        $getAllUsers=\App\UserDetails::where('userstatus','>',0)->orderBy('created_at','desc')->get();
        $getAllRankNormal=\App\RankDetails::where('status',1)->get();
        $getAllRank=\App\RankDetails::where('status',2)->get();
        $qualified=array(4=>0,5=>0,6=>0);
        $rankQualified=0;
        foreach($getAllUsers as $user){
            $filterRank=$getAllRankNormal->filter(function($q)use($user){
                return $q->topup_amount<=$user->stackingDeposite()->max('usdt') && $q->firstline<= $user->clubBusiness()['first'] && $q->secondline<= $user->clubBusiness()['rest'] && $q->teamsize<= $user->active_downline && $q->direct_business<=$user->total_direct_investment && $q->direct_count<= $user->active_direct;
            });
            \Log::info('topup '.$user->stackingDeposite()->max('usdt').' first line '.$user->clubBusiness()['first'].' rest '.$user->clubBusiness()['rest'].'\n team '.$user->active_downline.' dir busns '.$user->total_direct_investment.' direct_count '.$user->active_direct);
            if(count($filterRank)){
                $userNormalRankUpdate=\App\UserDetails::where('id',$user->id)->update([
                    'user_rank'  =>  $filterRank->last()->rank_id,
                ]);
                $rankQualified=$filterRank->last()->rank_id;
                $directDetail=$user->totalDirect()->get();
                
                foreach($getAllRank as $rank){
                    $legsRank=array();
                    if(count($directDetail)>=$rank->leg_count){
                        $i=0;
                        foreach($directDetail as $direct){
                            $star4=0;
                            $star5=0;
                            $star6=0;
                            $star4+=($direct->user_rank==4)?1:0;
                            $star5+=($direct->user_rank==5)?1:0;
                            $star6+=($direct->user_rank==6)?1:0;
                            $sponsorid=[$direct->userid];
                            while(count($sponsorid)){
                                $allDirect=\App\UserDetails::whereIn('sponsorid',$sponsorid)->get();
                                if(count($allDirect)){
                                    $allDirectFilter=$allDirect->where('user_rank','=',4);
                                    $star4+=count($allDirectFilter);
                                    $allDirectFilter=$allDirect->where('user_rank','=',5);
                                    $star5+=count($allDirectFilter);
                                    $allDirectFilter=$allDirect->where('user_rank','=',6);
                                    $star6+=count($allDirectFilter);
                                }
                                $sponsorid=\App\UserDetails::whereIn('sponsorid',$sponsorid)->pluck('userid');
                            }
                            if($star4)
                                $qualified[4]++;
                            if($star5)
                                $qualified[5]++;
                            if($star6)
                                $qualified[6]++;
                        }
                        if($qualified[6]>=$rank->leg_count){
                            $rank= 7;
                            break;
                        }
                        if($qualified[5]>=$rank->leg_count){
                            $rankQualified= 6;
                            break;
                        }
                        if($qualified[4]>=$rank->leg_count){
                            $rankQualified=5;
                            break;
                        }
                    }else{
                        break;
                    }
                }\Log::info('final Rank '.$rankQualified);
                $userNormalRankUpdate=\App\UserDetails::where('id',$user->id)->update([
                    'user_rank'  =>  $rankQualified,
                ]);
            }
        }
    }
}
