@extends('layouts.app')
@section('title', 'About — Soccer Dads')
@section('content')

<div style="background:#262c39; padding:4rem 2rem;">
    <div class="container">
        <h1 style="font-family:'GetShow'; font-weight:normal; font-size:72px; color:#fff; margin-bottom:1rem;">About</h1>
        <p style="font-size:18px; color:rgba(255,255,255,0.7); max-width:600px;">Friday night futsal for dads in the Adelaide Hills. Est. 2011.</p>
    </div>
</div>

<div style="padding:4rem 2rem;">
    <div class="container">
        <div class="about-grid" style="display:grid; grid-template-columns:repeat(3,1fr); gap:1.5rem;">
            <a href="/about/history" style="text-decoration:none;">
                <div style="background:#fff; border:1px solid #e8e8e8; border-radius:16px; padding:2rem; text-align:center; transition:box-shadow 0.2s;" onmouseover="this.style.boxShadow='0 4px 20px rgba(0,0,0,0.08)'" onmouseout="this.style.boxShadow='none'">
                    <i class="fa-solid fa-book-open" style="font-size:36px; color:#458bc8; margin-bottom:1rem; display:block;"></i>
                    <h2 style="font-size:20px; font-weight:600; color:#262c39; margin-bottom:0.5rem;">History</h2>
                    <p style="font-size:14px; color:#888; line-height:1.6;">How Soccer Dads began and where we've come from.</p>
                </div>
            </a>
            <a href="/about/locations" style="text-decoration:none;">
                <div style="background:#fff; border:1px solid #e8e8e8; border-radius:16px; padding:2rem; text-align:center; transition:box-shadow 0.2s;" onmouseover="this.style.boxShadow='0 4px 20px rgba(0,0,0,0.08)'" onmouseout="this.style.boxShadow='none'">
                    <i class="fa-solid fa-location-dot" style="font-size:36px; color:#e68a46; margin-bottom:1rem; display:block;"></i>
                    <h2 style="font-size:20px; font-weight:600; color:#262c39; margin-bottom:0.5rem;">Locations</h2>
                    <p style="font-size:14px; color:#888; line-height:1.6;">Where we play and how to find us.</p>
                </div>
            </a>
            <a href="/about/honour-board" style="text-decoration:none;">
                <div style="background:#fff; border:1px solid #e8e8e8; border-radius:16px; padding:2rem; text-align:center; transition:box-shadow 0.2s;" onmouseover="this.style.boxShadow='0 4px 20px rgba(0,0,0,0.08)'" onmouseout="this.style.boxShadow='none'">
                    <i class="fa-solid fa-trophy" style="font-size:36px; color:#f0c040; margin-bottom:1rem; display:block;"></i>
                    <h2 style="font-size:20px; font-weight:600; color:#262c39; margin-bottom:0.5rem;">Honour Board</h2>
                    <p style="font-size:14px; color:#888; line-height:1.6;">Season winners and top performers throughout the years.</p>
                </div>
            </a>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    @media (max-width: 768px) {
        .about-grid {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endpush
