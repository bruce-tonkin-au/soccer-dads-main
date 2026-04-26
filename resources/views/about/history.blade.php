@extends('layouts.app')
@section('title', 'History — Soccer Dads')
@section('content')

<div style="background:#262c39; padding:4rem 2rem;">
    <div class="container">
        <a href="/about" style="font-size:13px; color:rgba(255,255,255,0.5); text-decoration:none; display:inline-flex; align-items:center; gap:6px; margin-bottom:1.5rem;">
            <i class="fa-solid fa-chevron-left"></i> About
        </a>
        <h1 style="font-family:'GetShow'; font-weight:normal; font-size:72px; color:#fff;">History</h1>
    </div>
</div>

<div style="padding:4rem 2rem;">
    <div class="container" style="max-width:800px;">
        <p style="font-size:16px; color:#444; line-height:1.8; margin-bottom:1.5rem;">
            Soccer Dads began in 2011 in the Adelaide Hills, South Australia. What started as a casual kickabout between a handful of local dads has grown into a thriving weekly futsal competition with hundreds of players across dozens of seasons.
        </p>
        <p style="font-size:16px; color:#444; line-height:1.8; margin-bottom:1.5rem;">
            Every Friday night, three teams — Orange, Blue and Green — compete across a series of short games in a round-robin format. The scoring system tracks every goal, assist, save and card in real time, with live commentary provided by our AI commentator Nigel Ashcroft.
        </p>
        <p style="font-size:16px; color:#444; line-height:1.8;">
            More content coming soon.
        </p>
    </div>
</div>

@endsection
