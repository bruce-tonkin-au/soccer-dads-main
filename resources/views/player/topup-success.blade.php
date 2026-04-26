@extends('layouts.app')
@section('title', 'Payment Successful — Soccer Dads')
@section('content')

<div style="background:#262c39; padding:3rem 2rem;">
    <div class="container">
        <h1 style="font-family:'GetShow'; font-weight:normal; font-size:56px; color:#fff;">All good!</h1>
    </div>
</div>

<div style="padding:3rem 2rem;">
    <div class="container" style="max-width:480px; text-align:center;">

        <div style="background:#fff; border:1px solid #e8e8e8; border-radius:16px; padding:3rem 2rem;">
            <div style="width:72px; height:72px; background:#f0fdf4; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1.5rem;">
                <i class="fa-solid fa-circle-check" style="font-size:36px; color:#7bba56;"></i>
            </div>
            <h2 style="font-size:22px; font-weight:700; color:#262c39; margin-bottom:0.5rem;">Payment received</h2>
            @if($session && $session->amount_total)
            <div style="font-size:36px; font-weight:700; color:#7bba56; margin:1rem 0;">
                +${{ number_format($session->amount_total / 100, 2) }}
            </div>
            @endif
            <p style="font-size:14px; color:#888; margin-bottom:2rem;">
                Your account has been topped up. The credit will show in your transaction history.
            </p>
            <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
                <a href="/portal" style="background:#262c39; color:#fff; padding:12px 24px; border-radius:8px; text-decoration:none; font-size:14px; font-weight:600;">
                    <i class="fa-solid fa-house"></i> Back to portal
                </a>
                <a href="/portal/account" style="background:#fff; color:#262c39; padding:12px 24px; border-radius:8px; text-decoration:none; font-size:14px; font-weight:600; border:1px solid #e8e8e8;">
                    <i class="fa-solid fa-wallet"></i> View account
                </a>
            </div>
        </div>

    </div>
</div>

@endsection
