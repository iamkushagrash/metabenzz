<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\UserDetails;
use DB;
use Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;


class BonusRewardController extends Controller
{
    //User
    public function userDepositHistory(){
        $trnshistory=DB::table('transaction_details')->where([['transaction_details.userid',Session::get('user.id')],['transaction_details.txntype','0']/*,['transaction_details.txndesc','User Deposit'],['transaction_details.paymentstatus',2]*/])
        ->join('transaction_infos','transaction_details.id','=','transaction_infos.txnid')
        ->join('user_details','transaction_details.userid','=','user_details.id')
        ->join('users','user_details.userid','=','users.id')
        ->select('transaction_details.amountsftc as coinamount','transaction_details.amountusdt as amountusdt','transaction_details.currency', 'users.email', 'users.uuid as userid', 'users.usersname', 'transaction_infos.transaction_hash')
        ->selectRaw('DATE_FORMAT(transaction_details.created_at,"%d-%m-%Y") as created_at')
        ->selectRaw('case when transaction_details.paymentstatus=0 then "Pending" when transaction_details.paymentstatus=1 then "Pending" when transaction_details.paymentstatus=2 then "Confirmed" when transaction_details.paymentstatus=3 then "Failed" when transaction_details.paymentstatus=4 then "Failed" when transaction_details.paymentstatus=5 then "Failed" end as status')
        ->selectRaw('case when transaction_details.paymentstatus=0 then "status-pending" when transaction_details.paymentstatus=1 then "status-pending" when transaction_details.paymentstatus=2 then "status-complete" when transaction_details.paymentstatus=3 then "status-cancelled" when transaction_details.paymentstatus=4 then "status-cancelled" when transaction_details.paymentstatus=5 then "status-cancelled" end as statusclass')
        ->orderByRaw('transaction_details.id DESC')
        ->get();
        /*dd($trnshistory);*/

        return view('user.deposithistory')->with('history',$trnshistory);
    }


    public function userUpgradeHistory(){
    $basichistory = DB::table('stacking_deposites')
        ->where('stacking_deposites.userid', Session::get('user.id'))
        ->where('stacking_deposites.invest_type', 0) // <--- Dragging deposits only
        ->join('user_details','stacking_deposites.userid','=','user_details.id')
        ->join('users','user_details.userid','=','users.id')
        ->join('stacking_details','stacking_deposites.planid','=','stacking_details.id')
        ->select('stacking_deposites.amount','stacking_deposites.usdt','stacking_details.cps', 'users.email', 'users.uuid as userid', 'users.usersname as usersname')
        ->selectRaw('DATE_FORMAT(stacking_deposites.created_at,"%d-%m-%Y") as created_at')
        ->selectRaw('case when stacking_deposites.status=-1 then "Withdraw" when stacking_deposites.status=0 then "Closed" when stacking_deposites.status=1 then "Active" when stacking_deposites.status=2 then "Active" when stacking_deposites.status=3 then "Active" end as status')
        ->selectRaw('case when stacking_deposites.status=0 then "status-cancelled" when stacking_deposites.status=1 then "status-complete" when stacking_deposites.status=2 then "status-complete" when stacking_deposites.status=3 then "status-complete" end as statusclass')
        ->orderByRaw('stacking_deposites.id DESC')
        ->get();
    
    return view('user.packagehistory')->with('basic', $basichistory);
}


    public function userUpgradeTxnHistory(){

        $txnhistory=DB::table('stacking_deposites')->where([['wallet_transfers.fromUser',Session::get('user.id')],['wallet_transfers.fromWallet','wallet'],['wallet_transfers.toWallet','basic']])
        ->join('wallet_transfers','stacking_deposites.txnid','=','wallet_transfers.id')
        ->join('user_details','stacking_deposites.userid','=','user_details.id')
        ->join('users','user_details.userid','=','users.id')
        ->join('stacking_details','stacking_deposites.planid','=','stacking_details.id')
        ->select('stacking_deposites.amount','stacking_deposites.usdt','stacking_details.cps', 'users.email', 'users.uuid as userid', 'users.usersname as usersname',/* 'wallet_transfers.userid'*/ )
        ->selectRaw('DATE_FORMAT(stacking_deposites.created_at,"%d-%m-%Y") as created_at')
        ->selectRaw('case when stacking_deposites.status=0 then "Closed" when stacking_deposites.status=1 then "Active" end as status')
        ->selectRaw('case when stacking_deposites.status=0 then "status-cancelled" when stacking_deposites.status=1 then "status-complete" end as statusclass')
        ->orderByRaw('stacking_deposites.id DESC')
        ->get();

        $walletreducehistory=DB::table('wallet_transfers')->where([['wallet_transfers.fromUser',Session::get('user.id')],['wallet_transfers.fromWallet','wallet'],['wallet_transfers.toWallet','wallet']])
        ->join('user_details','wallet_transfers.userid','=','user_details.id')
        ->join('users','user_details.userid','=','users.id')
        ->select('users.email', 'users.uuid as userid', 'users.usersname as usersname', 'wallet_transfers.amount')
        ->selectRaw('DATE_FORMAT(wallet_transfers.created_at,"%d-%m-%Y") as created_at')
        ->orderByRaw('wallet_transfers.id DESC')
        ->get();
        /*dd($walletreducehistory);*/

        
        return view('user.packagetxnhistory')->with('team',$txnhistory)->with('wallet',$walletreducehistory);
    }

     public function userUpgradeLockingHistory(){
        $basichistory=DB::table('stacking_deposites')->where('stacking_deposites.userid',Session::get('user.id'))
         ->where('stacking_deposites.invest_type', 1) // <--- Locking deposits only
        ->join('user_details','stacking_deposites.userid','=','user_details.id')
        ->join('users','user_details.userid','=','users.id')
        ->join('stacking_details','stacking_deposites.planid','=','stacking_details.id')
        ->select('stacking_deposites.amount','stacking_deposites.usdt','stacking_deposites.maturity_date','stacking_details.cps', 'users.email', 'users.uuid as userid', 'users.usersname as usersname')
        ->selectRaw('DATE_FORMAT(stacking_deposites.created_at,"%d-%m-%Y") as created_at')
        ->selectRaw('case when stacking_deposites.status=-1 then "Withdraw" when stacking_deposites.status=0 then "Closed" when stacking_deposites.status=1 then "Active" when stacking_deposites.status=2 then "Active" when stacking_deposites.status=3 then "Active" end as status')
        ->selectRaw('case when stacking_deposites.status=0 then "status-cancelled" when stacking_deposites.status=1 then "status-complete" when stacking_deposites.status=2 then "status-complete" when stacking_deposites.status=3 then "status-complete" end as statusclass')
        ->orderByRaw('stacking_deposites.id DESC')
        ->get();
        
        return view('user.packageLockinghistory')->with('basic',$basichistory);
    }


}
