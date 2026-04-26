@extends('layouts.app')
@section('title', 'Account — Soccer Dads')
@section('content')

<div style="background:#262c39; padding:3rem 2rem;">
    <div class="container">
        <a href="/portal" style="font-size:13px; color:rgba(255,255,255,0.5); text-decoration:none; display:inline-flex; align-items:center; gap:6px; margin-bottom:1.5rem;">
            <i class="fa-solid fa-chevron-left"></i> Portal
        </a>
        <h1 style="font-family:'GetShow'; font-weight:normal; font-size:56px; color:#fff;">Account</h1>
    </div>
</div>

<div style="padding:3rem 2rem;">
    <div class="container" style="max-width:600px;">

        <div style="background:#fff; border:1px solid #e8e8e8; border-radius:16px; padding:2rem; margin-bottom:1.5rem; text-align:center;">
            <div style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:8px;">Current balance</div>
            <div style="font-size:48px; font-weight:700; color:{{ $balance < 0 ? '#e24b4a' : '#262c39' }};">
                ${{ number_format(abs($balance), 2) }}
            </div>
            @if($balance < 0)
            <div style="font-size:14px; color:#e24b4a; margin-top:4px;">You have an outstanding balance</div>
            @elseif($balance == 0)
            <div style="font-size:14px; color:#888; margin-top:4px;">Your account is at zero</div>
            @else
            <div style="font-size:14px; color:#7bba56; margin-top:4px;">You're in credit</div>
            @endif
        </div>

        <a href="/portal/topup" style="text-decoration:none; display:block;">
            <div style="background:#262c39; border-radius:16px; padding:2rem; text-align:center;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                <i class="fa-solid fa-credit-card" style="font-size:36px; color:rgba(255,255,255,0.7); margin-bottom:1rem; display:block;"></i>
                <h2 style="font-size:18px; font-weight:600; color:#fff; margin-bottom:0.5rem;">Top up your account</h2>
                <p style="font-size:14px; color:rgba(255,255,255,0.6); margin-bottom:1.25rem;">Add credit online using a debit or credit card.</p>
                <span style="background:#fff; color:#262c39; padding:10px 24px; border-radius:8px; font-size:14px; font-weight:600;">
                    <i class="fa-brands fa-stripe"></i> Pay with card
                </span>
            </div>
        </a>

    </div>
</div>

@endsection
