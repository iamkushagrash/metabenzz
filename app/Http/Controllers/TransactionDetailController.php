<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;

class TransactionDetailController extends Controller
{
    //User
    public function showTransactionPage(){
        //dd(Crypt::encrypt('0x7B5691917CAE40174BD636D84B3E36AFbCCD2F01'));
        $detail=\App\ProfileStore::where('id',1)->first();
        $user=\App\UserDetails::where('id',\Session::get('user.id'))->first();
        
        $transaction=\App\TransactionDetail::where([['paymentstatus','>',0],['txntype',0]])->get();
        return view('user.depositusdt')->with('detail',$detail)->with('user',$user)->with('transaction',$transaction);
        
    }

    public function submitTransaction(Request $request){
        if($request->currency=='usdt')
            $regex='regex:/^[a-z0-9]{64}$/u';
        else
            $regex='regex:/^0x[a-fA-F0-9]{64}$/u';
        $vali=Validator::make($request->all(), [
            'honeypot'  =>  ['nullable','numeric'],
            'currency'  =>  ['required','string'],
            'amountusdt'  =>  ['required','numeric'],
            'mwt'  =>   ['nullable','numeric' ],
            'txnhash'  =>  [ 'string' , $regex,'unique:transaction_infos,transaction_hash'],
        ])->validate();
        $selTxn=\App\TransactionInfo::where('id',($request->honeypot-\Session::get('logtime')))->first();
        if($request->amountusdt<10 /*&& $request->currency!='usdt'*/){
            return redirect()->back()->with('warning','Amount of USDT should be greater than 10.');
        }
        if(is_null($request->honeypot) || $request->honeypot< \Session::get('logtime')){
            //$this->userIssueReport('payload','Deposite');
        }elseif($request->honeypot>\Session::get('logtime') && $request->honeypot<\Session::get('logtime')){
            if(is_null($selTxn)){
                //$this->userIssueReport('payload','Remaining Deposite');
                \Log::info('User is tempering with payload user id is '.\Session::get('user.id'));
                $userDetailsUpd=\App\User::where('email',\Session::get('user.email'))->update([
                    'permission'  =>  0,
                ]);
                \Auth::logout();
                return redirect('/login')->with('warning','Your ID is blocked. please contact admin.');
            }else{
                $usrPaydetail=\App\TransactionDetail::where('id',($selTxn->txnid))->first();
                if(!($usrPaydetail->remaining==$request->mwt)){
                    //$this->userIssueReport('remaining amount','Remaining Deposite');
                    \Log::info('User is tempering remaining amount in Remaining Deposite. Txn Id is '.$selTxn->txnid.' user id is '.\Session::get('user.id'));
                    $userDetailsUpd=\App\User::where('email',\Session::get('user.email'))->update([
                        'permission'  =>  0,
                    ]);
                    \Auth::logout();
                    return redirect('/login')->with('warning','Your ID is blocked. please contact admin.');
                }
            }
        }
        if(!is_null($request->honeypot) && $request->amountusdt>=10 && $request->honeypot==\Session::get('logtime')){
            $assetDetail=\App\AssetDetail::where('userid',\Session::get('user.id'))->first();
            $insertWalletRequest=\App\TransactionDetail::insertGetId([
                "userid"  => \Session::get('user.id'),
                "txntype"  => 0,
                "amountsftc"  => $request->mwt,
                "amountusdt"  => $request->amountusdt,
                "remaining"  => ($request->currency=='usdt' || $request->currency=='usdtbep20')?$request->amountusdt:$request->mwt, 
                "paymentstatus"  => 1,
                "txndesc"  => "User Deposit",
                "comments"  => 'wallet',
                "planid"  => 0,
                "currency"  => $request->currency,
                "paidby"  => \Session::get('user.id'),
                "created_at"  => now(),
                "release_date"  =>date('Y-m-d'),
            ]);
            $profileDetails=\App\ProfileStore::all()->first();
            $insTxnInfo=\App\TransactionInfo::create([
                "txnid"  =>  $insertWalletRequest,
                "payment_addr"  =>  ($request->currency=='usdt')?Crypt::decrypt($profileDetails->usdt):(($request->currency=='usdtbep20')?Crypt::decrypt($profileDetails->usdtbep20):Crypt::decrypt($profileDetails->sftc)),//\Illuminate\Support\Facades\Crypt::decrypt($assetDetail->depositaddr)
                "transaction_hash"  =>  $request->txnhash,
                "contract_addr" =>($request->currency=='usdt')?$profileDetails->usdt_contract_addr:(($request->currency=='usdtbep20')?$profileDetails->usdtbep20_contract_addr:$profileDetails->sftc_contract_addr),
                "amount"  =>  ($request->currency=='usdt' || $request->currency=='usdtbep20')?$request->amountusdt:$request->mwt,
                "txn_status"  =>  1,
            ]);
        }else{
            $selTxn=\App\TransactionInfo::where('id',($request->honeypot-\Session::get('logtime')))->first();
            $updTxn=\App\TransactionInfo::where('id',($request->honeypot-\Session::get('logtime')))->update([
                'txn_status'=> 1,'transaction_hash' => $request->txnhash ,
            ]);
            $updTxn=\App\TransactionDetail::where('id',($selTxn->txnid))->update([
                'paymentstatus'=> 1,
            ]);
        }
        
        return back()->with('success','Request submitted successfully.');
    }




    //Admin
    public function userWithdrawreq(){
        $adminwithreq=DB::table('transaction_details')->where([['transaction_details.txntype',1],['b_status',3],['transaction_details.paymentstatus',1]])
        /*->where(function($q) {
             $q->where('transaction_details.paymentstatus', 0)
               ->orWhere('transaction_details.paymentstatus', 1);
         })*/
        ->join('user_details','user_details.id','=','transaction_details.userid')
        ->join('users','users.id','=','user_details.userid')
        ->join('transaction_infos','transaction_infos.txnid','=','transaction_details.id')
        ->join('withdraw_infos','withdraw_infos.txnid','=','transaction_details.id')
        ->join('asset_details','user_details.id','=','asset_details.userid')
        ->select('transaction_details.id as txnid','users.usersname as usersname','users.uuid as uuid','users.email as email','transaction_details.amountsftc as amountsftc','transaction_details.amountusdt as amountusdt', 'transaction_details.net_amount as net_amount', 'transaction_details.deduction as deduction', 'withdraw_infos.stacking as roi', 'payment_addr as address', 'currency as currency', 'transaction_details.created_at as txndate')
        ->selectRaw('case when transaction_details.paymentstatus=0 then "Pending" when transaction_details.paymentstatus=1 then "Pending" end as status')
        ->selectRaw('case when transaction_details.paymentstatus=0 then "status-pending" when transaction_details.paymentstatus=1 then "status-pending" end as statusclass')
        ->orderBy('transaction_details.id', 'asc')
        ->get();
        $sumtotal=DB::table('transaction_details')->where([['txntype',1],['paymentstatus',1],['b_status',3]])
        /*->where(function($q) {
             $q->where('transaction_details.paymentstatus', 0)
               ->orWhere('transaction_details.paymentstatus', 1);
         })*/
        ->select(DB::raw('sum(amountsftc) as amountsftc,sum(amountusdt) as amountusdt'))
        ->get()->first();
        return view('control.withdrawrequests')->with('requests',$adminwithreq)->with('sumtotal',$sumtotal);
    }

    public function WithdrawRequestsExcel(){
        $adminwithreq=DB::table('transaction_details')->where([['txntype',1],['b_status',3],['paymentstatus',1]])
        /*->where(function($q) {
             $q->where('transaction_details.paymentstatus', 0)
               ->orWhere('transaction_details.paymentstatus', 1);
         })*/
        ->join('user_details','user_details.id','=','transaction_details.userid')
        ->join('users','users.id','=','user_details.userid')
        ->join('transaction_infos','transaction_infos.txnid','=','transaction_details.id')
        ->join('asset_details','user_details.id','=','asset_details.userid')
        ->select(/*'transaction_details.id as txnid',*/'users.usersname as usersname','users.uuid as uuid','users.email as email', 'payment_addr as address', 'currency as currency', 'transaction_details.created_at as txndate')
        ->selectRaw('case when transaction_details.paymentstatus=0 then "Pending" when transaction_details.paymentstatus=1 then "Pending" end as status')
        ->selectRaw('case when transaction_details.paymentstatus=0 then "status-pending" when transaction_details.paymentstatus=1 then "status-pending" end as statusclass,sum(transaction_details.amountsftc) as amountsftc,sum(transaction_details.amountusdt) as amountusdt,sum(transaction_details.deduction) as deduction,sum(transaction_details.net_amount) as net_amount')
        ->orderBy('transaction_details.id', 'asc')
        ->groupBy('payment_addr')
        ->get();
        $sumtotal=DB::table('transaction_details')->where([['txntype',1],['paymentstatus',1],['b_status',3]])
        /*->where(function($q) {
             $q->where('transaction_details.paymentstatus', 0)
               ->orWhere('transaction_details.paymentstatus', 1);
         })*/
        ->select(DB::raw('sum(amountsftc) as amountsftc,sum(amountusdt) as amountusdt'))
        ->get()->first();
        return view('control.withdrawexcel')->with('requests',$adminwithreq)->with('sumtotal',$sumtotal);
    }

    public function AdminwithdrawEdit($paymentid){
        $withdrawshow=DB::table('transaction_details')->where([['txntype',1],['transaction_details.id',$paymentid]])
        ->join('user_details','user_details.id','=','transaction_details.userid')
        ->join('users','users.id','=','user_details.userid')
        ->join('transaction_infos','transaction_infos.txnid','=','transaction_details.id')
        ->join('asset_details','user_details.id','=','asset_details.userid')
        ->select('transaction_details.id as txnid','users.usersname as usersname','users.uuid as uuid','users.email as email','transaction_details.amountsftc as amountsftc','transaction_details.amountusdt as amountusdt', 'transaction_details.deduction as deduction', 'transaction_details.net_amount as net_amount', 'payment_addr as address', 'transaction_hash as txnhash', 'currency as currency', DB::raw('DATE_FORMAT(transaction_details.created_at ,"%Y-%m-%d")as txndate'))
        ->selectRaw('case when transaction_details.paymentstatus=0 then "Pending" when transaction_details.paymentstatus=1 then "Pending" when transaction_details.paymentstatus=2 then "Success" when transaction_details.paymentstatus=5 then "Cancel" end as status')
        ->get()->first();
        //dd($withdrawshow);
        return view('control.withdrawupdate')->with('withdata',$withdrawshow);
    }
    public function AdminwithdrawUpdateone(Request $request){
        Validator::make($request->all(), [
            'paymentid'      => ['required', 'numeric'],
            /*'amountsftc'      => ['required', 'numeric'],*/
            'amountusdt'      => ['required', 'numeric'],
            'txnhash'   => ['required', 'string','regex:/^[a-zA-Z0-9\s]|[^<>]+$/u'],
            'withstatus'  => ['required', 'string','regex:/^[a-zA-Z0-9\s]|[^<>]+$/u'],
        ])->validate();
        
        if($request->withstatus=='Pending'){
            $status=1;
        }elseif($request->withstatus=='Success'){
            $status=2;
        }

            $updpayment=DB::table('transaction_details')->where('id',$request->paymentid)->update([
                'paymentstatus' => $status,
                'remaining' => 0,
                'paidby' => Session::get('user.id'),
                ]);
            $updtxninfo=DB::table('transaction_infos')->where('txnid',$request->paymentid)->update([
                'transaction_hash' => $request->txnhash,
                'txn_status' => $status,
                ]);
        
        return redirect()->back()->with(['success'=>'Withdrawal Updated Successfully.']);
    }

    public function userWithdrawHistory(Request $request){
        if($request->method()==="GET"){
            $fromDate=date('Y-m-d');
            $toDate=date('Y-m-d').' 23:59:59';
        $wdreport=DB::table('transaction_details')->where([['txntype',1],['transaction_details.paymentstatus',2]])
        ->whereBetween('transaction_details.created_at',[$fromDate,$toDate])
        ->join('user_details','user_details.id','=','transaction_details.userid')
        ->join('users','users.id','=','user_details.userid')
        ->join('transaction_infos','transaction_infos.txnid','=','transaction_details.id')
        ->join('asset_details','user_details.id','=','asset_details.userid')
        ->select('users.usersname as usersname','users.uuid as uuid','users.email as email','transaction_details.amountsftc as amountsftc','transaction_details.amountusdt as amountusdt', 'transaction_details.net_amount as net_amount', 'transaction_details.deduction as deduction', 'payment_addr as address', 'currency as currency', 'transaction_details.created_at as txndate', 'transaction_details.updated_at as updated')
        ->selectRaw('case when transaction_details.paymentstatus=1 then "Pending" when transaction_details.paymentstatus=2 then "Success" end as status')
        ->selectRaw('case when transaction_details.paymentstatus=1 then "status-pending" when transaction_details.paymentstatus=2 then "status-complete" end as statusclass')
        ->orderBy('transaction_details.id', 'desc')
        ->get();
        $wdtotal=DB::table('transaction_details')->where([['txntype',1],['paymentstatus',2]])
        ->whereBetween('transaction_details.created_at',[$fromDate,$toDate])
        ->select(DB::raw('sum(amountsftc) as amountsftc,sum(amountusdt) as amountusdt'))
        ->get()->first();
        }else{
            $fromDate=$request->fromdate;
            $toDate=$request->todate.' 23:59:59';
        $wdreport=DB::table('transaction_details')->where([['txntype',1],['transaction_details.paymentstatus',2]])
        ->whereBetween('transaction_details.created_at',[$fromDate,$toDate])
        ->join('user_details','user_details.id','=','transaction_details.userid')
        ->join('users','users.id','=','user_details.userid')
        ->join('transaction_infos','transaction_infos.txnid','=','transaction_details.id')
        ->join('asset_details','user_details.id','=','asset_details.userid')
        ->select('users.usersname as usersname','users.uuid as uuid','users.email as email','transaction_details.amountsftc as amountsftc','transaction_details.amountusdt as amountusdt', 'transaction_details.net_amount as net_amount', 'transaction_details.deduction as deduction', 'payment_addr as address', 'currency as currency', 'transaction_details.created_at as txndate', 'transaction_details.updated_at as updated')
        ->selectRaw('case when transaction_details.paymentstatus=1 then "Pending" when transaction_details.paymentstatus=2 then "Success" end as status')
        ->selectRaw('case when transaction_details.paymentstatus=1 then "status-pending" when transaction_details.paymentstatus=2 then "status-complete" end as statusclass')
        ->orderBy('transaction_details.id', 'desc')
        ->get();
        $wdtotal=DB::table('transaction_details')->where([['txntype',1],['paymentstatus',2]])
        ->whereBetween('transaction_details.created_at',[$fromDate,$toDate])
        ->select(DB::raw('sum(amountsftc) as amountsftc,sum(amountusdt) as amountusdt'))
        ->get()->first();
        }
        //dd($wdreport,$wdtotal);
        
        return view('control.withdrawhistory')->with('requests',$wdreport)->with('sumtotal',$wdtotal);
    }
    

    

}
