<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class StackingDetailController extends Controller
{
    public function cappingCalculation($userid,$amount){
        $getAllDeposite=\App\StackingDeposite::where([['userid',$userid],['status','>',0]])->get();
        $totalAmount=0;
        foreach($getAllDeposite as $deposit){
            if($amount>0){
                if(Crypt::decrypt($deposit->capamount)<=$amount){
                    $remainingAmount=Crypt::decrypt($deposit->capamount)-$amount;
                    $totalAmount+=Crypt::decrypt($deposit->capamount);
                    $amount=$amount-Crypt::decrypt($deposit->capamount);
                    $updateUserDetailUserstate=\App\UserDetails::where('id',$userid);
                    $updateUserDetailUserstate->decrement('userstate');
                    /*$updateUserDetailUserstate->save();*/
                    $updateCapping=\App\StackingDeposite::where('id',$deposit->id)->update([
                        'capamount'  =>   Crypt::encrypt(0),
                        'status'  =>   0,
                    ]);
                }else{
                    $remainingAmount=Crypt::decrypt($deposit->capamount)-$amount;
                    $updateCapping=\App\StackingDeposite::where('id',$deposit->id)->update([
                        'capamount'  =>   Crypt::encrypt($remainingAmount),
                    ]);
                    $totalAmount+=$amount;
                    $amount=0;
                }
                \Log::info('userid '. $deposit->userid.' remaining Capping Amount is '.Crypt::decrypt($deposit->capamount));
            }
        }
        return $totalAmount;
    }

    public function cappingUpdate($userid){
        $getAllPlan=\App\StackingDeposite::where([['userid',$userid],['istatus',0]])->get();
        foreach($getAllPlan as $plan){
            $updateCapping=\App\StackingDeposite::where('id',$plan->id)->update([
                'capamount'  =>  Crypt::encrypt(Crypt::decrypt($plan->capamount)+(3*$plan->usdt)),
                'istatus'  =>  1,
            ]);
        }
    }

    // public function boosterCheckForUser($id){
    //     $userDetail=\App\UserDetails::where('id',$id)->first();
    //     \Log::info('created_at check created_at='.$userDetail->created_at.' check date='.date('Y-m-d',strtotime('- 7 days',strtotime(now()))).' Answer is '.$userDetail->created_at >= date('Y-m-d',strtotime('- 7 days',strtotime(now()))));
    //     \Log::info('is null loan status '.is_null($userDetail->userLoanStatus()));
    //     if($userDetail->created_at >= date('Y-m-d',strtotime('- 7 days',strtotime(now()))) && (is_null($userDetail->userLoanStatus()) || $userDetail->userLoanStatus()->status==0)){
    //     \Log::info('inside if');
    //         $m=0;
    //         foreach($userDetail->totalDirect()->get() as $direct){
    //             \Log::info('self '.$userDetail->stackingDeposite()->max('usdt'));
    //             \Log::info('direct '.$direct->stackingDeposite()->max('usdt'));
    //             \Log::info('loan Status '.is_null($direct->userLoanStatus()));
    //             if(($direct->stackingDeposite()->max('usdt')>=$userDetail->stackingDeposite()->max('usdt')) && (is_null($direct->userLoanStatus()) || $direct->userLoanStatus()->status==0)){
    //                 $m++;
    //             }
    //         }\Log::info('m '.$m);
    //         if($m>=5){
    //             $userUpdate=\App\UserDetails::where('id',$id)->update([
    //                 'booster'  =>  2,
    //             ]);
    //         }
    //     }
    // }
}
