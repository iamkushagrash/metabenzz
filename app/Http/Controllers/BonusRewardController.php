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
    //Admin
    public function userDepositHis(Request $request){
        if($request->method()==="GET"){
            $fromDate=date('Y-m-d');
            $toDate=date('Y-m-d').' 23:59:59';
        }else{
            $fromDate=$request->fromdate;
            $toDate=$request->todate.' 23:59:59';
        }
        $topupuser=DB::table('transaction_details')->where([['transaction_details.txntype','0'],['transaction_details.txndesc','User Deposit'],['transaction_details.paymentstatus',2]])
        ->whereBetween('transaction_details.created_at',[$fromDate,$toDate])
        ->join('user_details','transaction_details.userid','=','user_details.id')
        ->join('users','user_details.userid','=','users.id')
        /*->join('user_details as pdb','transaction_details.paidby','=','pdb.id')
        ->join('users as pdby','pdb.userid','=','pdby.id')*/
        ->select('transaction_details.id as id', 'users.uuid as userid', 'users.email', 'users.usersname', 'transaction_details.amountsftc', 'transaction_details.amountusdt',/* 'pdby.usersname as paidbyname', 'pdby.email as paidbyid',*/ 'transaction_details.comments', 'transaction_details.created_at')
        ->selectRaw('case when transaction_details.paymentstatus=0 then "Pending" when transaction_details.paymentstatus=1 then "Pending" when transaction_details.paymentstatus=2 then "Confirmed" when transaction_details.paymentstatus=3 then "Failed" when transaction_details.paymentstatus=4 then "Failed" when transaction_details.paymentstatus=5 then "Failed" end as status')
        ->selectRaw('case when transaction_details.paymentstatus=0 then "status-pending" when transaction_details.paymentstatus=1 then "status-pending" when transaction_details.paymentstatus=2 then "status-complete" when transaction_details.paymentstatus=3 then "status-cancelled" when transaction_details.paymentstatus=4 then "status-cancelled" when transaction_details.paymentstatus=5 then "status-cancelled" end as statusclass')
        ->orderByRaw('transaction_details.id DESC')
        ->get();


        $sumamount=DB::table('transaction_details')->where([['transaction_details.txntype','0'],['transaction_details.txndesc','User Deposit'],['transaction_details.paymentstatus',2]])
        ->whereBetween('transaction_details.created_at',[$fromDate,$toDate])
        ->select(DB::raw('sum(amountsftc) as amountcoin,sum(amountusdt) as amountusdt'))
        ->get()->first();

        /*dd($topupuser);*/
        return view('control.deposituser')->with('topup',$topupuser)->with('sumamount',$sumamount);
    }

    public function userPeriodStaking(Request $request){
        if($request->method()==="GET"){
            $fromDate=date('Y-m-d');
            $toDate=date('Y-m-d').' 23:59:59';
        }else{
            $fromDate=$request->fromdate;
            $toDate=$request->todate.' 23:59:59';
        }
        $basichistory=DB::table('stacking_deposites')/*->where('stacking_deposites.userid',Session::get('user.id'))*/
        ->whereBetween('stacking_deposites.created_at',[$fromDate,$toDate])
        ->join('user_details','stacking_deposites.userid','=','user_details.id')
        ->join('users','user_details.userid','=','users.id')
        ->join('stacking_details','stacking_deposites.planid','=','stacking_details.id')
        ->join('wallet_transfers','stacking_deposites.txnid','=','wallet_transfers.id')
        ->join('user_details as ud','wallet_transfers.fromUser','=','ud.id')
        ->join('users as u','ud.userid','=','u.id')
        ->select('stacking_deposites.amount','stacking_deposites.usdt','stacking_details.cps', 'users.email', 'users.uuid', 'users.usersname', 'u.usersname as fromname', 'u.uuid as fromid', 'stacking_details.duration', 'wallet_transfers.fromWallet')
        ->selectRaw('DATE_FORMAT(stacking_deposites.created_at,"%d-%m-%Y") as created_at')
        ->selectRaw('case when stacking_deposites.status=0 then "Closed" when stacking_deposites.status=1 then "Active" when stacking_deposites.status=3 then "Active" when stacking_deposites.status=4 then "Active" end as status')
        ->selectRaw('case when stacking_deposites.status=0 then "status-cancelled" when stacking_deposites.status=1 then "status-complete" when stacking_deposites.status=3 then "status-complete" when stacking_deposites.status=4 then "status-complete" end as statusclass')
        ->selectRaw('case when stacking_deposites.staketype=1 then "Drag" when stacking_deposites.staketype=2 then "Lock" end as staketype')
        ->orderByRaw('stacking_deposites.id DESC')
        ->get();
        $sumamount=DB::table('stacking_deposites')
        ->whereBetween('stacking_deposites.created_at',[$fromDate,$toDate])
        ->select(DB::raw('sum(amount) as amountstake,sum(usdt) as amountusdt'))
        ->get()->first();
        
        return view('control.stakinghistorybasic')->with('basichistory',$basichistory)->with('sumamount',$sumamount);
    }

    public function reportDragging(Request $request){
        $fromdate=date("Y-m-d").' 00:00:00';
        $todate=date("Y-m-d").' 23:59:59';
        if(!is_null($request->fromdate) && !is_null($request->todate)){
            $fromdate=$request->fromdate;
            $todate=$request->todate.' 23:59:59';
        }
        $reportbasic=DB::table('cps_incomes')/*->where('cps_details.desc','CPS')*/
        ->whereBetween('cps_incomes.created_at',[$fromdate,$todate])
        ->join('user_details','user_details.id','=','cps_incomes.userid')
        ->join('users','users.id','=','user_details.userid')
        ->join('stacking_deposites','stacking_deposites.id','=','cps_incomes.txnid')
        ->select('users.usersname as name','users.uuid as userid','users.email as email','cps_incomes.amount as amount','cps_incomes.amt_usdt as amountusdt', 'stacking_deposites.amount as principal', 'stacking_deposites.usdt as principalusdt', 'cps_incomes.created_at as created_at')
        ->orderBy('cps_incomes.id', 'desc')
        ->get();
        /*dd($reportbasic);*/
        $sumamount=DB::table('cps_incomes')/*->where('cps_details.desc','CPS')*/
        ->whereBetween('cps_incomes.created_at',[$fromdate,$todate])
        ->select(DB::raw('sum(amount) as amountcoin,sum(amt_usdt) as amountusdt'))
        ->get()->first();

        return view('control.reportdragging')->with('reportbasic',$reportbasic)->with('sumamount',$sumamount);
    }

    public function reportDirect(Request $request){
        $fromdate=date("Y-m-d").' 00:00:00';
        $todate=date("Y-m-d").' 23:59:59';
        if(!is_null($request->fromdate) && !is_null($request->todate)){
            $fromdate=$request->fromdate;
            $todate=$request->todate.' 23:59:59';
        }
        $reportdirect=DB::table('bonus_rewards')/*->where('bonus_rewards.description','referral')*/
        ->whereBetween('bonus_rewards.created_at',[$fromdate,$todate])
        ->join('user_details','user_details.id','=','bonus_rewards.userid')
        ->join('users','users.id','=','user_details.userid')
        ->join('user_details as ud','ud.id','=','bonus_rewards.fromuser')
        ->join('users as u','u.id','=','ud.userid')
        ->select('users.usersname as name','users.uuid as userid','users.email as email','u.usersname as fromname','u.uuid as fromid','bonus_rewards.amount as amount', 'bonus_rewards.amt_usdt as amountusdt', 'bonus_rewards.created_at as created_at')
        ->selectRaw('case when bonus_rewards.status=0 then "Wallet" when bonus_rewards.status=1 then "Wallet" when bonus_rewards.status=3 then "Locked" end as status')
        ->selectRaw('case when bonus_rewards.status=0 then "ffffff" when bonus_rewards.status=1 then "ffffff" when bonus_rewards.status=3 then "FF0000" end as statusclass')
        ->orderBy('bonus_rewards.id', 'desc')
        ->get();
        /*dd($reportbasic);*/
        $sumamount=DB::table('bonus_rewards')/*->where('bonus_rewards.description','referral')*/
        ->whereBetween('bonus_rewards.created_at',[$fromdate,$todate])
        ->select(DB::raw('sum(amount) as amountcoin,sum(amt_usdt) as amountusdt'))
        ->get()->first();

        return view('control.reportdirect')->with('reportdirect',$reportdirect)->with('sumamount',$sumamount);
    }






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
