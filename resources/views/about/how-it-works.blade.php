@extends('layouts.app')
@section('title', 'How it works — Soccer Dads')
@section('content')

<x-page-header title="How it works" />

<div style="padding:4rem 2rem;">
    <div class="container" style="max-width:800px;">

        {{-- Intro --}}
        <p style="font-size:19px; color:#262c39; line-height:1.7; margin-bottom:1.25rem; font-weight:500;">
            Soccer Dads is a weekly social futsal competition in the Adelaide Hills, and we've been running since 2011.
        </p>
        <p style="font-size:16px; color:#444; line-height:1.8; margin-bottom:1rem;">
            Every skill level is welcome — genuinely. Whether you last played twenty years ago or you're out most weekends, it all sorts itself out once you're on a team (more on that below). It's friendly, it's social, and there's no pressure. Come and have a run.
        </p>

        {{-- Your registration link --}}
        <h2 style="font-size:24px; font-weight:700; color:#262c39; margin-top:3rem; margin-bottom:0.75rem;">Your registration link</h2>
        <p style="font-size:16px; color:#444; line-height:1.8; margin-bottom:1rem;">
            When you join, you're given a personal link — that link <em>is</em> your account. There's no password to remember; just use your link to pre-register for any night you'd like to play.
        </p>
        <p style="font-size:16px; color:#444; line-height:1.8;">
            Registrations open a week in advance. Each night is capped at 18 players, so it pays to get in early once a night opens up.
        </p>

        {{-- What a night looks like --}}
        <h2 style="font-size:24px; font-weight:700; color:#262c39; margin-top:3rem; margin-bottom:0.75rem;">What a night looks like</h2>
        <p style="font-size:16px; color:#444; line-height:1.8; margin-bottom:1.5rem;">
            Arrive around 6:50pm for a warm-up, we kick off about 7:20pm, and we're usually done around 9:10pm.
        </p>
        <div style="background:#fff; border:1px solid #e8e8e8; border-radius:16px; padding:2rem; margin-bottom:1rem;">
            <p style="font-size:16px; color:#444; line-height:1.8; margin:0;">
                The format keeps everyone moving. Games are five minutes each, and you play two games each round before a five-minute rest. We run seven rounds across the night, which works out to around 70 minutes of game time per player — plenty of touches, and enough breathers to keep it fun.
            </p>
        </div>

        {{-- Teams --}}
        <h2 style="font-size:24px; font-weight:700; color:#262c39; margin-top:3rem; margin-bottom:0.75rem;">Teams</h2>
        <p style="font-size:16px; color:#444; line-height:1.8; margin-bottom:1rem;">
            On the night you'll be assigned to one of three teams — Orange, Green or Blue. We balance the teams automatically each week using playing history and peer reviews, so the games stay even and competitive.
        </p>
        <p style="font-size:16px; color:#444; line-height:1.8;">
            For your first week or two we won't have either of those yet, so you may be unsorted to begin with — that's completely normal. After a few games the system settles you in around your own skill level, wherever that happens to land.
        </p>

        {{-- Cost & payment --}}
        <h2 style="font-size:24px; font-weight:700; color:#262c39; margin-top:3rem; margin-bottom:0.75rem;">Cost &amp; payment</h2>
        <p style="font-size:16px; color:#444; line-height:1.8; margin-bottom:1.5rem;">
            Your first night is on us — free, so you can try it with no commitment. After that it's $10 for each night you attend.
        </p>
        <div style="background:#fff; border:1px solid #e8e8e8; border-radius:16px; padding:2rem; margin-bottom:1rem;">
            <p style="font-size:16px; color:#444; line-height:1.8; margin:0;">
                The first $10 only comes out after your <em>second</em> night, and your account can run down to −$30 before you'll need to top it up — so there's no rush. We don't handle cash: payment is by credit card, whenever you're ready.
            </p>
        </div>

        {{-- What to wear --}}
        <h2 style="font-size:24px; font-weight:700; color:#262c39; margin-top:3rem; margin-bottom:0.75rem;">What to wear</h2>
        <p style="font-size:16px; color:#444; line-height:1.8; margin-bottom:1rem;">
            Comfy clothes and a pair of indoor shoes. Joggers are perfectly fine to start with — most newcomers switch to futsal boots before long, but there's no need on day one.
        </p>
        <p style="font-size:16px; color:#444; line-height:1.8;">
            Shin-pads are optional. Hardly anyone wears them and it's rarely an issue, but if you'd feel more comfortable in them, go for it — no one minds either way.
        </p>

        {{-- Bringing mates & kids --}}
        <h2 style="font-size:24px; font-weight:700; color:#262c39; margin-top:3rem; margin-bottom:0.75rem;">Bringing mates &amp; kids</h2>
        <p style="font-size:16px; color:#444; line-height:1.8; margin-bottom:1rem;">
            Got mates who'd be keen? Send them our way — point them to our <a href="/contact" style="color:#262c39; font-weight:500; border-bottom:1px solid rgba(38,44,57,0.2); text-decoration:none;">Contact</a> page and we'll get them set up.
        </p>
        <p style="font-size:16px; color:#444; line-height:1.8;">
            Kids are welcome too, and it's great fun playing alongside your son. The minimum age is around 14–15.
        </p>

        {{-- Closing --}}
        <div style="margin-top:3rem; padding-top:2rem; border-top:1px solid #eee;">
            <p style="font-size:16px; color:#444; line-height:1.8;">
                Still got questions? We're happy to help — just head to our <a href="/contact" style="color:#262c39; font-weight:500; border-bottom:1px solid rgba(38,44,57,0.2); text-decoration:none;">Contact</a> page and get in touch.
            </p>
        </div>

    </div>
</div>

@endsection
