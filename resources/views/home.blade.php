@extends('layouts.app')

@section('title', 'Soccer Dads — What could possibly go wrong?')

@section('content')

{{-- Hero --}}
<div style="background:#262c39; padding:6rem 2rem; text-align:center;">
    <div style="max-width:700px; margin:0 auto;">
        <img src="/images/Soccer-Dads-Logo.png" style="width:150px; margin-bottom:2rem;">
        <h1 style="font-family:'GetShow'; font-weight:normal; font-size:110px; color:#fff; line-height:1; margin-bottom:1.5rem; text-shadow:0 4px 20px rgba(0,0,0,0.3);">
            Soccer Dads
        </h1>
        <p style="font-size:20px; color:rgba(255,255,255,0.7); margin-bottom:2.5rem; line-height:1.6;">
            Making more 'old days' together.
        </p>
    </div>
</div>

{{-- Stats bar --}}
<div style="background:linear-gradient(to right, #458bc8, #7bba56, #e68a46); padding:2rem;">
    <div class="container">
        <div class="stats-bar-grid" style="display:grid; grid-template-columns:repeat(5,1fr); gap:2rem; text-align:center;">
            <div>
                <div style="font-size:36px; font-weight:700; color:#fff;">{{ $stats['seasons'] }}</div>
                <div style="font-size:13px; color:rgba(255,255,255,0.8); text-transform:uppercase; letter-spacing:0.08em;">Seasons</div>
            </div>
            <div>
    <div style="font-size:36px; font-weight:700; color:#fff;">{{ $stats['sessions'] }}</div>
    <div style="font-size:13px; color:rgba(255,255,255,0.8); text-transform:uppercase; letter-spacing:0.08em;">Sessions</div>
</div>
            <div>
                <div style="font-size:36px; font-weight:700; color:#fff;">{{ $stats['games'] }}</div>
                <div style="font-size:13px; color:rgba(255,255,255,0.8); text-transform:uppercase; letter-spacing:0.08em;">Games</div>
            </div>
            <div>
                <div style="font-size:36px; font-weight:700; color:#fff;">{{ $stats['goals'] }}</div>
                <div style="font-size:13px; color:rgba(255,255,255,0.8); text-transform:uppercase; letter-spacing:0.08em;">Goals</div>
            </div>
            <div>
                <div style="font-size:36px; font-weight:700; color:#fff;">{{ $stats['players'] }}</div>
                <div style="font-size:13px; color:rgba(255,255,255,0.8); text-transform:uppercase; letter-spacing:0.08em;">Players</div>
            </div>
        </div>
    </div>
</div>


{{-- Latest news --}}
<div style="padding:5rem 2rem; background:#f6f7f9; border-bottom:1px solid #eceef1;">
    <div class="container" style="max-width:820px;">
        <h2 style="font-family:'GetShow'; font-weight:normal; font-size:56px; color:#262c39; margin-bottom:3rem; text-align:center;">Latest news</h2>
        @forelse ($news as $item)
            <article style="background:#fff; border:1px solid #eceef1; border-radius:12px; padding:1.75rem 2rem; margin-bottom:1.5rem;">
                <div style="font-size:13px; color:#98a2b3; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:0.5rem;">{{ \Carbon\Carbon::parse($item->newsDate)->format('j F Y') }}</div>
                <h3 style="font-size:22px; font-weight:600; color:#262c39; margin-bottom:0.75rem;">{{ $item->newsTitle }}</h3>
                <div style="font-size:15px; color:#667085; line-height:1.7;">{!! $item->newsBody !!}</div>
            </article>
        @empty
            <p style="text-align:center; color:#98a2b3;">No news just yet — check back soon.</p>
        @endforelse
    </div>
</div>

@endsection

@push('styles')
<style>
    @media (max-width: 768px) {
        .stats-bar-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }
        .stats-bar-grid > div:nth-child(5) {
            grid-column: 1 / -1;
        }
    }
</style>
@endpush