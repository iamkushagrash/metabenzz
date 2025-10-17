<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use DB;
use Session;
use App\StackingDeposite;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\StackingDetailController;
use App\Http\Controllers\SupportQueryController;

class WalletTransferController extends Controller
{
        //User
        public function dragpage(){
            //$capping=\App\StackingDeposite::where('userid',\Session::get('user.id'))->first();dd(' capamount ',Crypt::decrypt($capping->capamount));
            $userDetail=\App\UserDetails::where('id',\Session::get('user.id'))->first();
        

            $availbleStfc=\App\AccountDeposit::where('userid',\Session::get('user.id'))->first();
            
            $user=null;
            $price=\App\ProfileStore::where('id',1)->first();
            /*$user=\App\User::where('uuid',\Session::get('user.uuid'))
            ->join('user_details','users.id','=','user_details.userid')
            ->select(\DB::raw('('.\Session::get('logtime').'+ user_details.id) as id'),'uuid as uuid','email as email','usersname as name')->first();*/

            return view('user.dragmbz')->with('balance',$availbleStfc)/*->with('stacking',$stackingPeriod)*/->with('user',$user)->with('price',$price);
        }


        public function getUserDetail(Request $request){
            $dataregex=$this->findUserName($request->email);
            $regex=['required','exists:users,uuid'];
            Validator::make($request->all(),[
                'userid' =>$regex])->validate();
            $availbleStfc=\App\AccountDeposit::where('userid',\Session::get('user.id'))->first();
            
            $price=\App\ProfileStore::where('id',1)->first();
            $user=\App\User::where('uuid',$request->userid)
            ->join('user_details','users.id','=','user_details.userid')
            ->select(\DB::raw('('.\Session::get('logtime').'+ user_details.id) as id'),'uuid as uuid','email as email','usersname as name','user_details.id as uid')->first();
            /*$stackingDeposite=\App\StackingDeposite::where('userid',$user->uid)->get()->last();
            if(!is_null($stackingDeposite)){
                $stackingPeriod=\App\StackingDetail::where([['status',1],['id','>=',$stackingDeposite->planid]])->selectRaw('id +'.(pow(51, 3)).' as id,planname,cps,max_amount as amount')->get();
            }else{
                $stackingPeriod=\App\StackingDetail::where([['status',1]])->selectRaw('id +'.(pow(51, 3)).' as id,planname,cps,max_amount as amount')->get();
            } */  

            $userDetail=\App\UserDetails::where('id',$user->id-\Session::get('logtime'))->first();
        
            
            return view('user.dragmbz')->with('balance',$availbleStfc)/*->with('stacking',$stackingPeriod)*/->with('user',$user)->with('price',$price);
    }

     public function getUserDetailforlock(Request $request){
            $dataregex=$this->findUserName($request->email);
            $regex=['required','exists:users,uuid'];
            Validator::make($request->all(),[
                'userid' =>$regex])->validate();
            $availbleStfc=\App\AccountDeposit::where('userid',\Session::get('user.id'))->first();
            
            $price=\App\ProfileStore::where('id',1)->first();
            $user=\App\User::where('uuid',$request->userid)
            ->join('user_details','users.id','=','user_details.userid')
            ->select(\DB::raw('('.\Session::get('logtime').'+ user_details.id) as id'),'uuid as uuid','email as email','usersname as name','user_details.id as uid')->first();
            /*$stackingDeposite=\App\StackingDeposite::where('userid',$user->uid)->get()->last();
            if(!is_null($stackingDeposite)){
                $stackingPeriod=\App\StackingDetail::where([['status',1],['id','>=',$stackingDeposite->planid]])->selectRaw('id +'.(pow(51, 3)).' as id,planname,cps,max_amount as amount')->get();
            }else{
                $stackingPeriod=\App\StackingDetail::where([['status',1]])->selectRaw('id +'.(pow(51, 3)).' as id,planname,cps,max_amount as amount')->get();
            } */  

            $userDetail=\App\UserDetails::where('id',$user->id-\Session::get('logtime'))->first();
        
            
            return view('user.lockmbz')->with('balance',$availbleStfc)/*->with('stacking',$stackingPeriod)*/->with('user',$user)->with('price',$price);
    }






            public function dragmbz(Request $request)
            {
                $validator = Validator::make($request->all(), [
                    'honeypotu' => ['required', 'gt:' . \Session::get('logtime')],
                    'amount' => ['required', 'numeric'],
                    'password' => ['required', 'string'],
                ]);

                if ($validator->fails()) {
                    return redirect('/User/Drag')->with('errors', $validator->errors());
                }

                // if (fmod($request->amount, 100) != 0) {
                //     return redirect('/User/Drag')->with('warning', 'Amount should be multiple of 100');
                // }

                $user = \App\User::where('uuid', \Session::get('user.userid'))->first();
                if (!\Hash::check($request->password, $user->password)) {
                    return redirect('/User/Drag')->with('warning', 'Your entered password is wrong.');
                }

                $walletAmount = \App\AccountDeposit::where('userid', \Session::get('user.id'))->first();
                $getIncomingFund = \App\WalletTransfer::where([
                    ['userid', \Session::get('user.id')], 
                    ['toWallet', 'wallet']
                ])->get();
                $getOutgoingFund = \App\WalletTransfer::where([
                    ['fromUser', \Session::get('user.id')], 
                    ['txnid', 0], 
                    ['fromWallet', 'wallet']
                ])->get();

                $totalAmount = $getIncomingFund->sum('amount') - $getOutgoingFund->sum('amount');

                if ($totalAmount < $request->amount) {
                    return redirect('/User/Drag')->with('warning', 'Insufficient balance.');
                }

                $price = \App\ProfileStore::where('id', 1)->first();
                $plan = \App\StackingDetail::where([
                    ['min_amount', '<=', $request->amount],
                    ['max_amount', '>=', $request->amount],
                    ['status', 1]
                ])->first();

                if (!$plan) {
                    return redirect('/User/Drag')->with('warning', 'No suitable stacking plan found.');
                }

                \DB::beginTransaction();
                try {
                    // ✅ Get current user detail
                    $userUpdate = \App\UserDetails::where('id', $request->honeypotu - \Session::get('logtime'));
                    $userId = $userUpdate->first()->id;

                    // ✅ 1. Insert Wallet Transfer
                    $insWalletEntry = \App\WalletTransfer::insertGetId([
                        'userid' => $userId,
                        'txnid' => 0,
                        'fromWallet' => 'wallet',
                        'toWallet' => 'basic',
                        'amount' => $request->amount,
                        'fromUser' => \Session::get('user.id'),
                        'created_at' => now(),
                        'release_date' => now(),
                    ]);

                    // ✅ 2. Deduct Main Wallet
                    \App\AccountDeposit::where('userid', \Session::get('user.id'))->update([
                        'amount' => Crypt::encrypt(Crypt::decrypt($walletAmount->amount) - $request->amount),
                    ]);

                    // ✅ 3. Insert Stacking Deposit
                    $capAmount = ($userUpdate->first()->active_direct)
                        ? Crypt::encrypt($request->amount * 5)
                        : Crypt::encrypt($request->amount * $plan->capping);

                    $insertWallet = \App\StackingDeposite::insertGetId([
                        'userid' => $userId,
                        'txnid' => $insWalletEntry,
                        'amount' => ($request->amount / $price->price),
                        'usdt' => $request->amount,
                        'capamount' => $capAmount,
                        'planid' => $plan->id,
                        'status' => 1,
                        'roidouble' => 1,
                        'created_at' => now(),
                        'istatus' => ($userUpdate->first()->active_direct) ? 1 : 0,
                        'staketype' => 1,
                        'invest_type' => 0
                    ]);

                    // 🟢 4. Add 2X MetaBenz Bonus (1X already main, 1X to Points Wallet)
                    $bonusAmount = $request->amount;

                    // Check if user already has a points wallet
                    $pointsWallet = \App\PointsWallet::firstOrCreate(
                        ['userid' => \Session::get('user.id')],
                        ['balance' => 0]
                    );

                    // Update wallet balance
                    $pointsWallet->balance += $bonusAmount;
                    $pointsWallet->save();

                    // Insert transaction entry
                    \App\PointsTransaction::create([
                        'userid' => \Session::get('user.id'),
                        'amount' => $bonusAmount,
                        'type' => 'credit',
                        'source' => 'Drag Investment Bonus',
                        'reference_id' => $insertWallet,
                        'created_at' => now(),
                    ]);

                    \Log::info('Points Bonus Added: ' . $bonusAmount . ' for user ' . \Session::get('user.id'));

                    // ✅ 5. Update user investment info
                    $userStatus = $userUpdate->first()->userstate;
                    $userUpdate->increment('userstate');
                    $userUpdate->increment('current_self_investment', $request->amount);
                    $userUpdate->increment('total_self_investment', $request->amount);
                    $userUpdate->increment('current_investment', $request->amount);
                    $userUpdate->increment('total_investment', $request->amount);
                    $userUpdate->update(['userstatus' => 1, 'capping' => 0, 'roi_status' => 1]);

                    // ✅ 6. Guider/Sponsor Update
                    $cappingFunction = new \App\Http\Controllers\StackingDetailController();
                    $guiderUpdate = \App\UserDetails::where('userid', $userUpdate->first()->sponsorid);

                    if ($guiderUpdate->exists()) {
                        $guiderDetail = $guiderUpdate->first();
                        $guiderUpdate->increment('current_direct_investment', $request->amount);
                        $guiderUpdate->increment('total_direct_investment', $request->amount);
                        $guiderUpdate->increment('current_investment', $request->amount);
                        $guiderUpdate->increment('total_investment', $request->amount);

                        // Referral Income
                        if ($guiderDetail->userstate) {
                            $directAmount = $request->amount * 5 / 100;
                            $directAmount = $cappingFunction->cappingCalculation($guiderDetail->id, $directAmount);
                            \App\BonusReward::create([
                                'userid' => $guiderDetail->id,
                                'fromuser' => $userId,
                                'amount' => $directAmount / $price->price,
                                'remaining' => $directAmount / $price->price,
                                'amt_usdt' => $directAmount,
                                'txnid' => $insertWallet,
                                'description' => 'referral',
                                'status' => 0,
                                'created_at' => now(),
                            ]);
                        }

                        // Level update
                        $guiderId = $guiderDetail->sponsorid;
                        while ($guiderId > 0) {
                            $levelUpdate = \App\UserDetails::where('userid', $guiderId);
                            $levelUpdate->increment('current_level_investment', $request->amount);
                            $levelUpdate->increment('total_level_investment', $request->amount);
                            $levelUpdate->increment('current_investment', $request->amount);
                            $levelUpdate->increment('total_investment', $request->amount);
                            if ($userStatus == 0) $levelUpdate->increment('active_downline');
                            $guiderId = $levelUpdate->first()->sponsorid;
                        }
                    }

                    \DB::commit();
                    return redirect('/User/Drag')->with('success', 'Your investment is successful. Bonus added to Points Wallet.');

                } catch (\Exception $e) {
                    \DB::rollback();
                    \Log::error('Error for User ' . \Session::get('user.id') . ' Message: ' . $e->getMessage());
                    return redirect('/User/Drag')->with('warning', 'Error Code 1021, There is some error in stacking.');
                }
            }













   //Locking
   //User - Lock Page
public function lockPage(){
    $userDetail = \App\UserDetails::where('id', \Session::get('user.id'))->first();
    $availableStfc = \App\AccountDeposit::where('userid', \Session::get('user.id'))->first();
    $user = null;
    $price = \App\ProfileStore::where('id',1)->first();

    return view('user.lockmbz')
        ->with('balance', $availableStfc)
        ->with('user', $user)
        ->with('price', $price);
}

//Get User Detail for Lock
public function getUserLockDetail(Request $request){
    Validator::make($request->all(),[
        'userid' => ['required','exists:users,uuid']
    ])->validate();

    $availableStfc = \App\AccountDeposit::where('userid',\Session::get('user.id'))->first();
    $price = \App\ProfileStore::where('id',1)->first();

    $user = \App\User::where('uuid',$request->userid)
        ->join('user_details','users.id','=','user_details.userid')
        ->select(\DB::raw('('.\Session::get('logtime').'+ user_details.id) as id'),'uuid as uuid','email as email','usersname as name','user_details.id as uid')
        ->first();

    return view('user.lockmbz')
        ->with('balance', $availableStfc)
        ->with('user', $user)
        ->with('price', $price);
}

//Lock Deposit Function
public function lockmbz(Request $request)
{
    $validator = Validator::make($request->all(), [
        'honeypotu' => ['required','gt:'.\Session::get('logtime')],
        'amount' => ['required','numeric'],
        'password' => ['required','string'],
    ]);

    if ($validator->fails()) {
        return redirect('/User/Lock')->with('errors', $validator->errors());
    }

    if (fmod($request->amount, 100) != 0) {
        return redirect('/User/Lock')->with('warning','Amount should be multiple of 100');
    }

    $user = \App\User::where('uuid', \Session::get('user.userid'))->first();
    if (!\Hash::check($request->password, $user->password)) {
        return redirect('/User/Lock')->with('warning','Your entered password is wrong.');
    }

    $walletAmount = \App\AccountDeposit::where('userid', \Session::get('user.id'))->first();
    if (Crypt::decrypt($walletAmount->amount) < $request->amount) {
        return redirect('/User/Lock')->with('warning','Insufficient balance.');
    }

    $price = \App\ProfileStore::where('id',1)->first();

    \DB::beginTransaction();
    try {
        $userUpdate = \App\UserDetails::where('id', $request->honeypotu - \Session::get('logtime'))->first();
        $userId = $userUpdate->id;

        // 1️⃣ Wallet Transfer Entry
        $insWalletEntry = \App\WalletTransfer::insertGetId([
            'userid' => $userId,
            'txnid' => 0,
            'fromWallet' => 'wallet',
            'toWallet' => 'basic',
            'amount' => $request->amount,
            'fromUser' => \Session::get('user.id'),
            'created_at' => now(),
            'release_date' => now(),
        ]);

        // 2️⃣ Deduct Wallet
        \App\AccountDeposit::where('userid', \Session::get('user.id'))->update([
            'amount' => Crypt::encrypt(Crypt::decrypt($walletAmount->amount) - $request->amount)
        ]);

        // 3️⃣ Insert Locking Stacking Deposit
        $insertWallet = \App\StackingDeposite::insertGetId([
            'userid' => $userId,
            'txnid' => $insWalletEntry,
            'amount' => $request->amount / $price->price,
            'usdt' => $request->amount,
            'planid' => 0,
            'status' => 1,
            'istatus' => 1,
            'staketype' => 2, // locking type
            'invest_type' => 1, // locking deposit
            'maturity_date' => now()->addYear(),
            'created_at' => now(),
        ]);

        // 4️⃣ Add 2X Points Wallet Bonus
        $bonusAmount = $request->amount;
        $pointsWallet = \App\PointsWallet::firstOrCreate(
            ['userid' => \Session::get('user.id')],
            ['balance' => 0]
        );
        $pointsWallet->balance += $bonusAmount;
        $pointsWallet->save();

        \App\PointsTransaction::create([
            'userid' => \Session::get('user.id'),
            'amount' => $bonusAmount,
            'type' => 'credit',
            'source' => 'Locking Deposit Bonus',
            'reference_id' => $insertWallet,
            'created_at' => now(),
        ]);

        // 5️⃣ Update user investment info
        $userUpdate->increment('current_self_investment', $request->amount);
        $userUpdate->increment('total_self_investment', $request->amount);
        $userUpdate->increment('current_investment', $request->amount);
        $userUpdate->increment('total_investment', $request->amount);
        $userUpdate->increment('userstate'); // keep existing
        $userUpdate->update(['userstatus' => 1, 'roi_status' => 1]);

        // 6️⃣ Guider/Sponsor Update
        $cappingFunction = new \App\Http\Controllers\StackingDetailController();
        $guiderUpdate = \App\UserDetails::where('userid', $userUpdate->sponsorid);

        if ($guiderUpdate->exists()) {
            $guiderDetail = $guiderUpdate->first();
            $guiderUpdate->increment('current_direct_investment', $request->amount);
            $guiderUpdate->increment('total_direct_investment', $request->amount);
            $guiderUpdate->increment('current_investment', $request->amount);
            $guiderUpdate->increment('total_investment', $request->amount);

            // 5% Referral Bonus
            if ($guiderDetail->userstate) {
                $directAmount = $request->amount * 5 / 100;
                $directAmount = $cappingFunction->cappingCalculation($guiderDetail->id, $directAmount);
                \App\BonusReward::create([
                    'userid' => $guiderDetail->id,
                    'fromuser' => $userId,
                    'amount' => $directAmount / $price->price,
                    'remaining' => $directAmount / $price->price,
                    'amt_usdt' => $directAmount,
                    'txnid' => $insertWallet,
                    'description' => 'referral',
                    'status' => 0,
                    'created_at' => now(),
                ]);
            }

            // Level Updates
            $guiderId = $guiderDetail->sponsorid;
            while ($guiderId > 0) {
                $levelUpdate = \App\UserDetails::where('userid', $guiderId);
                $levelUpdate->increment('current_level_investment', $request->amount);
                $levelUpdate->increment('total_level_investment', $request->amount);
                $levelUpdate->increment('current_investment', $request->amount);
                $levelUpdate->increment('total_investment', $request->amount);
                if ($userUpdate->userstate == 0) $levelUpdate->increment('active_downline');
                $guiderId = $levelUpdate->first()->sponsorid;
            }
        }

        \DB::commit();
        return redirect('/User/Lock')->with('success','Your locking deposit is submitted successfully. Bonus added to Points Wallet.');

    } catch (\Exception $e) {
        \DB::rollback();
        \Log::error('Error for User '.\Session::get('user.id').' Error: '.$e->getMessage());
        return redirect('/User/Lock')->with('warning','There is some error in locking deposit.');
    }
}



}
