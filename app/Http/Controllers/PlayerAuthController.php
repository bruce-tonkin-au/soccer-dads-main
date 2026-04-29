<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PlayerAuthController extends Controller
{
    public function showLogin()
    {
        if (session('player_id')) {
            return redirect('/portal');
        }
        return view('player.login');
    }

    public function sendMagicLink(Request $request)
    {
        $code = trim($request->input('code'));

        $member = DB::table('members')
            ->whereRaw('LOWER("memberCode") = LOWER(?)', [$code])
            ->where('memberActive', 1)
            ->first();

        if (!$member) {
            return back()->with('error', 'We couldn\'t find a player with that code. Please check and try again.');
        }

        if (!$member->memberEmail) {
            return back()->with('no_email', true)->with('member_name', $member->memberNameFirst);
        }

        $token = Str::random(64);
        DB::table('member_tokens')->insert([
            'memberID'   => $member->memberID,
            'token'      => $token,
            'expires_at' => Carbon::now()->addMinutes(30),
            'used'       => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $loginUrl = url('/auth/' . $token);
        Mail::send('emails.magic-link', [
            'member'   => $member,
            'loginUrl' => $loginUrl,
        ], function ($message) use ($member) {
            $message->to($member->memberEmail)
                    ->subject('Your Soccer Dads login link');
        });

        return back()->with('success', 'Check your email — we\'ve sent you a login link valid for 30 minutes.');
    }

    public function authenticate($token)
    {
        $tokenRecord = DB::table('member_tokens')
            ->where('token', $token)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$tokenRecord) {
            return redirect('/login')->with('error', 'This login link has expired or already been used. Please request a new one.');
        }

        DB::table('member_tokens')
            ->where('token', $token)
            ->update(['used' => true, 'updated_at' => now()]);

        session(['player_id' => $tokenRecord->memberID]);

        return redirect('/portal');
    }

    public function logout(Request $request)
    {
        session()->forget('player_id');
        return redirect('/login')->with('success', 'You have been logged out.');
    }
}
