@extends('layouts.app')
@section('title', 'Locations — Soccer Dads')
@section('content')

<x-page-header title="Locations" />

<div style="padding:4rem 2rem;">
    <div class="container">
        <div style="background:#fff; border:1px solid #e8e8e8; border-radius:16px; padding:2rem; margin-bottom:1.5rem;">
            <div style="display:flex; align-items:flex-start; gap:1.5rem;">
                <div style="width:48px; height:48px; background:#458bc8; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <i class="fa-solid fa-location-dot" style="color:#fff; font-size:20px;"></i>
                </div>
                <div>
                    <h2 style="font-size:18px; font-weight:600; color:#262c39; margin-bottom:4px;">Hahndorf</h2>
                    <p style="font-size:14px; color:#888; margin-bottom:8px;">Adelaide Hills, South Australia</p>
                    <p style="font-size:14px; color:#444; line-height:1.6;">Our primary venue in the heart of the Adelaide Hills. Friday nights, weekly during the season.</p>
                </div>
            </div>
        </div>
        <p style="font-size:14px; color:#aaa; text-align:center;">More location details coming soon.</p>
    </div>
</div>

@endsection
