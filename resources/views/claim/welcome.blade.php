@extends('layouts.app')

@section('title', 'Welcome to Soccer Dads')

@section('content')

<div style="max-width:560px; margin:0 auto; padding:4rem 1.5rem;">
    <div style="text-align:center; margin-bottom:2rem;">
        <h1 style="font-family:'GetShow'; font-weight:normal; font-size:64px; color:#262c39; margin-bottom:0.25rem;">
            Welcome, {{ $member->memberNameFirst }}!
        </h1>
        <p style="font-size:15px; color:#888;">Your account is all set up.</p>
    </div>

    <div style="background:#fff; border:1px solid #e8e8e8; border-radius:16px; padding:2rem; text-align:center;">
        <p style="font-size:15px; color:#262c39; margin-bottom:1.5rem; line-height:1.6;">
            You're logged in. Head over to your portal to see your account balance, top up, register for the next game and check your match history.
        </p>
        <a href="/portal" style="display:inline-block; background:#262c39; color:#fff; padding:14px 28px; border-radius:10px; text-decoration:none; font-size:15px; font-weight:600;">
            Go to my portal
        </a>
    </div>
</div>

@endsection
