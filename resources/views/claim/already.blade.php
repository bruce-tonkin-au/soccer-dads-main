@extends('layouts.app')

@section('title', 'Account already claimed — Soccer Dads')

@section('content')

<div style="max-width:560px; margin:5rem auto; padding:0 1.5rem;">
    <div style="text-align:center; margin-bottom:2rem;">
        <h1 style="font-family:'GetShow'; font-weight:normal; font-size:56px; color:#262c39; margin-bottom:0.25rem;">
            Already claimed
        </h1>
        <p style="font-size:14px; color:#888;">This Soccer Dads account is set up.</p>
    </div>

    <div style="background:#fff; border:1px solid #e8e8e8; border-radius:16px; padding:2rem; text-align:center;">
        <i class="fa-solid fa-circle-check" style="color:#7bba56; font-size:48px; margin-bottom:1rem;"></i>
        <p style="font-size:16px; color:#262c39; margin-bottom:0.5rem;">
            Welcome back, {{ $member->memberNameFirst }}!
        </p>
        <p style="font-size:14px; color:#888; margin-bottom:1.5rem;">
            Your account has already been claimed. You can log in to access your profile, account balance and game history.
        </p>
        <a href="/login" style="display:inline-block; background:#262c39; color:#fff; padding:14px 28px; border-radius:10px; text-decoration:none; font-size:15px; font-weight:600;">
            Log in
        </a>
    </div>
</div>

@endsection
