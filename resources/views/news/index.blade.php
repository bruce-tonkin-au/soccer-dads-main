@extends('layouts.app')

@section('title', 'News — Soccer Dads')

@section('content')

<div style="background:#262c39; padding:4rem 2rem; text-align:center;">
    <h1 style="font-family:'GetShow'; font-weight:normal; font-size:72px; color:#fff; margin:0;">News</h1>
</div>

<div style="padding:4rem 2rem; background:#f6f7f9;">
    <div class="container" style="max-width:860px;">
        @forelse ($news as $item)
            <a href="/news/{{ $item->newsID }}" style="display:block; background:#fff; border:1px solid #eceef1; border-radius:12px; overflow:hidden; text-decoration:none; color:inherit; margin-bottom:2rem;">
                @if ($item->newsImage)
                    <img src="{{ asset('storage/'.$item->newsImage) }}" alt="{{ $item->newsTitle }}" style="width:100%; height:260px; object-fit:cover; display:block;">
                @else
                    <div style="width:100%; height:200px; background:linear-gradient(135deg,#458bc8,#7bba56,#e68a46);"></div>
                @endif
                <div style="padding:2rem;">
                    <div style="font-size:13px; color:#98a2b3; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:0.5rem;">{{ \Carbon\Carbon::parse($item->newsDate)->format('j F Y') }}</div>
                    <h2 style="font-size:26px; font-weight:600; color:#262c39; margin:0;">{{ $item->newsTitle }}</h2>
                </div>
            </a>
        @empty
            <p style="text-align:center; color:#98a2b3;">No news just yet — check back soon.</p>
        @endforelse
    </div>
</div>

@endsection
