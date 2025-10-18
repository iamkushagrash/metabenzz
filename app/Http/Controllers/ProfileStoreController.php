<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use DB;
use Session;

class ProfileStoreController extends Controller
{
    //Admin
    public function companyProfilepage(){
        if(\Session::get('user.licence')==3){
            $address=DB::table('profile_stores')/*->where('status',1)*/
            ->select('sftc as mgptaddress', 'usdt as usdtaddress', 'usdtbep20 as usdtbep20address', 'price as price')
            ->selectRaw('case when sftc_deposit_status=0 then "Off" when sftc_deposit_status=1 then "On" end as mgptdepstatus')
            ->selectRaw('case when sftc_withdrawal_status=0 then "Off" when sftc_withdrawal_status=1 then "On" end as mgptwithstatus')
            ->selectRaw('case when usdt_deposit_status=0 then "Off" when usdt_deposit_status=1 then "On" end as usdttrcdepstatus')
            ->selectRaw('case when usdt_withdrawal_status=0 then "Off" when usdt_withdrawal_status=1 then "On" end as usdttrcwithdstatus')
            ->selectRaw('case when usdtbep20_deposit_status=0 then "Off" when usdtbep20_deposit_status=1 then "On" end as usdtbep20depstatus')
            ->selectRaw('case when usdtbep20_withdrawal_status=0 then "Off" when usdtbep20_withdrawal_status=1 then "On" end as usdtbep20withstatus')
            ->get()->first();
            //dd(\Illuminate\Support\Facades\Crypt::encrypt('TCHWKvdXeppPzHADJJqjTQx6U2KKyvJCEd'));
            return view('control.companyprofile')->with('address',$address);
        }else{
            return redirect()->back()->with('warning','Page not found.');
        }
    }

    public function editCompanyProfile(Request $request){
        if(\Session::get('user.licence')==3){
            Validator::make($request->all(), [
                'usdtaddress'      => ['required','string','regex:/^T[a-zA-Z0-9]{33}$/u'],
                'usdtbep20address'      => ['required','string','regex:/^0x[a-fA-F0-9]{40}$/u'],
                'mgptaddress'      => ['nullable','string','regex:/^0x[a-fA-F0-9]{40}$/u'],
                'price'      => ['nullable','numeric'],
            ])->validate();

            $edit=\App\ProfileStore::firstOrNew(array('id'=>1));
            if(!is_null($request->usdtaddress))
                $edit->usdt=\Illuminate\Support\Facades\Crypt::encrypt($request->usdtaddress);
            if(!is_null($request->usdtbep20address))
                $edit->usdtbep20=\Illuminate\Support\Facades\Crypt::encrypt($request->usdtbep20address);
            if(!is_null($request->mgptaddress))
                $edit->sftc=\Illuminate\Support\Facades\Crypt::encrypt($request->mgptaddress);
            if(!is_null($request->price))
                $edit->price=$request->price;
            $edit->save();


            
            return redirect()->back()->with('success','Details Saved');
        }else{
            return redirect()->back()->with('warning','Page not found.');
        }
    }


    public function editCompanyDepositStatus(Request $request){
        Validator::make($request->all(), [
            /*'mgptdepstatus'      => ['string','required'],
            'mgptwithstatus'      => ['string','required'],*/
            'usdttrcdepstatus'      => ['string','required'],
            'usdttrcwithdstatus'      => ['string','required'],
            'usdtbep20depstatus'      => ['string','required'],
            'usdtbep20withstatus'      => ['string','required'],
        ])->validate();

        /*if ($request->mgptdepstatus=='Off') {
            $ktodepst=0;
        }elseif ($request->mgptdepstatus=='On') {
            $ktodepst=1;
        }
        if ($request->mgptwithstatus=='Off') {
            $ktowithst=0;
        }elseif ($request->mgptwithstatus=='On') {
            $ktowithst=1;
        }*/
        if ($request->usdttrcdepstatus=='Off') {
            $usdttrcdepst=0;
        }elseif ($request->usdttrcdepstatus=='On') {
            $usdttrcdepst=1;
        }
        if ($request->usdttrcwithdstatus=='Off') {
            $usdttrcwithdst=0;
        }elseif ($request->usdttrcwithdstatus=='On') {
            $usdttrcwithdst=1;
        }
        if ($request->usdtbep20depstatus=='Off') {
            $usdtbep20depst=0;
        }elseif ($request->usdtbep20depstatus=='On') {
            $usdtbep20depst=1;
        }
        if ($request->usdtbep20withstatus=='Off') {
            $usdtbep20withst=0;
        }elseif ($request->usdtbep20withstatus=='On') {
            $usdtbep20withst=1;
        }
        
        
        $updstatus=DB::table('profile_stores')->where('id',1)->update([
            /*'sftc_deposit_status' => $ktodepst,
            'sftc_withdrawal_status' => $ktowithst,*/
            'usdt_deposit_status' => $usdttrcdepst,
            'usdt_withdrawal_status' => $usdttrcwithdst,
            'usdtbep20_deposit_status' => $usdtbep20depst,
            'usdtbep20_withdrawal_status' => $usdtbep20withst,
            ]);
        
       
        return redirect()->back()->with(['success'=>'Permission Updated Successfully.']);
    }

}
