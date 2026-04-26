@extends('layouts.app')
@section('title', 'Honour Board — Soccer Dads')
@section('content')

<div style="background:#262c39; padding:4rem 2rem;">
    <div class="container">
        <a href="/about" style="font-size:13px; color:rgba(255,255,255,0.5); text-decoration:none; display:inline-flex; align-items:center; gap:6px; margin-bottom:1.5rem;">
            <i class="fa-solid fa-chevron-left"></i> About
        </a>
        <h1 style="font-family:'GetShow'; font-weight:normal; font-size:72px; color:#fff; margin-bottom:0.5rem;">Honour Board</h1>
        <p style="font-size:16px; color:rgba(255,255,255,0.6);">Season winners since 2011.</p>
    </div>
</div>

<div style="padding:4rem 2rem;">
    <div class="container" style="max-width:800px; text-align:center;">
        <i class="fa-solid fa-trophy" style="font-size:48px; color:#f0c040; margin-bottom:1.5rem; display:block;"></i>
        <h2 style="font-size:24px; font-weight:600; color:#262c39; margin-bottom:1rem;">Coming Soon</h2>
        <p style="font-size:16px; color:#888; line-height:1.8;">
            The Soccer Dads Honour Board recognises those who have contributed to the development and growth of Soccer Dads over the years. This page is coming soon.
        </p>
    </div>
</div>

@endsection
