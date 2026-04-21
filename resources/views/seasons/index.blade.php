@extends('layouts.app')

@section('title', 'Seasons — Soccer Dads')

@section('content')

<div style="padding:4rem 2rem;">
    <div class="container">
        <h1 style="font-family:'GetShow'; font-weight:normal; font-size:72px; color:#262c39; margin-bottom:0.5rem;">Seasons</h1>
        <p style="font-size:15px; color:#888; margin-bottom:3rem;">Every season of Soccer Dads, from the beginning.</p>

        <div style="display:flex; flex-direction:column; gap:12px;">
            @foreach($seasons as $season)
            <a href="/seasons/{{ $season->seasonKey }}" style="text-decoration:none;">
                <div style="background:#fff; border:1px solid #e8e8e8; border-radius:16px; padding:1.5rem 2rem; display:flex; align-items:center; justify-content:space-between; transition:box-shadow 0.2s;" onmouseover="this.style.boxShadow='0 4px 20px rgba(0,0,0,0.08)'" onmouseout="this.style.boxShadow='none'">
                    <div>
                        <div style="font-size:20px; font-weight:600; color:#262c39; margin-bottom:4px;">{{ $season->seasonName }}</div>
                        <div style="font-size:13px; color:#888;">{{ $season->nights }} nights · {{ $season->goals }} goals</div>
                    </div>
                    <i class="fa-solid fa-chevron-right" style="color:#aaa;"></i>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</div>

@endsection