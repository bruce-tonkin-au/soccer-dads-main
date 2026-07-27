@extends('layouts.app')

@section('title', $item->newsTitle . ' — Soccer Dads')

@section('content')

<div style="padding:4rem 2rem; background:#f6f7f9;">
    <div class="container" style="max-width:760px;">
        <a href="/news" style="display:inline-block; margin-bottom:1.5rem; color:#458bc8; text-decoration:none; font-size:14px;">← All news</a>
        @if ($item->newsImage)
            <img src="{{ asset('storage/'.$item->newsImage) }}" alt="{{ $item->newsTitle }}" style="width:100%; max-height:420px; object-fit:cover; border-radius:12px; display:block; margin-bottom:2rem;">
        @else
            <div style="width:100%; height:280px; background:linear-gradient(135deg,#458bc8,#7bba56,#e68a46); border-radius:12px; margin-bottom:2rem;"></div>
        @endif
        <div style="font-size:13px; color:#98a2b3; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:0.75rem;">{{ \Carbon\Carbon::parse($item->newsDate)->format('j F Y') }}</div>
        <h1 style="font-family:'GetShow'; font-weight:normal; font-size:56px; color:#262c39; line-height:1.1; margin-bottom:2rem;">{{ $item->newsTitle }}</h1>
        <div style="font-size:17px; color:#475467; line-height:1.8;">{!! $item->newsBody !!}</div>
    </div>
</div>

@endsection
