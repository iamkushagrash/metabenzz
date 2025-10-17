<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     */
    protected function redirectTo()
    {
        $user = \App\UserDetails::where('users.id', Auth::user()->id)
            ->leftJoin('users', 'users.id', '=', 'user_details.userid')
            ->leftJoin('users as guider', 'guider.id', '=', 'user_details.sponsorid')
            ->select(
                'user_details.id as id',
                'users.usersname as name',
                'users.uuid as userid',
                'users.email as email',
                'users.licence as licence',
                'users.permission as permission',
                'users.uuid as uuid',
                'guider.uuid as sponsorid',
                'users.doj'
            )
            ->first();

        \Session::put('user', $user);
        \Session::put('logtime', strtotime(now()));

        if ($user->licence == '3') return '/Main/Dashboard';
        if ($user->licence == '2') return '/Manage/Dashboard';
        return '/User/Dashboard';
    }

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Override login method to handle:
     * 1. Email + password
     * 2. UUID + password
     * 3. Wallet only
     */
    public function login(Request $request)
    {
        // 1️⃣ Wallet Login
        if ($request->has('wallet')) {
            $wallet = $request->input('wallet');
            $user = \App\User::where('wallet_address', $wallet)->first();

            if ($user) {
                Auth::login($user);
                return response()->json([
                    'success' => true,
                    'redirect' => $this->redirectTo()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Wallet not registered. Please sign up first.'
                ]);
            }
        }

        // 2️⃣ Email or UUID + Password Login
        $loginInput = $request->input('email'); // email or UUID
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'uuid';

        $request->merge([$fieldType => $loginInput]);

        // Validate
        $request->validate([
            $fieldType => 'required|string',
            'password' => 'required|string',
        ]);

        // Attempt login
        if (Auth::attempt([$fieldType => $loginInput, 'password' => $request->password])) {
            $request->session()->regenerate();
            return redirect()->intended($this->redirectTo());
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }
}
