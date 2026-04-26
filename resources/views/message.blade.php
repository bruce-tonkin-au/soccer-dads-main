<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $message->messageSubject }} — Soccer Dads</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        @font-face {
            font-family: 'GetShow';
            src: url('/fonts/get_show.woff2') format('woff2'),
                 url('/fonts/get_show.woff') format('woff');
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f4f4f4;
            min-height: 100vh;
        }
        .header {
            background: #262c39;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .header img { width: 80px; margin-bottom: 0.75rem; }
        .header-title {
            font-family: 'GetShow';
            font-size: 36px;
            color: #fff;
            font-weight: normal;
        }
        .container { max-width: 640px; margin: 0 auto; padding: 2rem; }

        .greeting {
            font-size: 28px;
            font-weight: 700;
            color: #262c39;
            margin-bottom: 0.5rem;
        }
        .message-body {
            font-size: 16px;
            color: #444;
            line-height: 1.8;
        }
        .message-body p { margin-bottom: 1rem; }
        .message-body ul, .message-body ol { padding-left: 1.5rem; margin-bottom: 1rem; }
        .message-body li { margin-bottom: 0.25rem; }
        .message-body a { color: #458bc8; }
        .message-body strong { font-weight: 700; }

        .card {
            background: #fff;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 1px solid #e8e8e8;
        }
        .card-title {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #888;
            margin-bottom: 0.75rem;
        }
        .next-game-date {
            font-size: 22px;
            font-weight: 700;
            color: #262c39;
            margin-bottom: 4px;
        }
        .next-game-sub {
            font-size: 14px;
            color: #888;
            margin-bottom: 1rem;
        }
        .reg-buttons {
            display: flex;
            gap: 8px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: 2px solid #e8e8e8;
            background: #fff;
            color: #262c39;
        }
        .btn-active-yes {
            border-color: #7bba56;
            background: #f0fdf4;
        }
        .btn-active-no {
            border-color: #e24b4a;
            background: #fff3f3;
        }

        .peer-review-card {
            background: linear-gradient(135deg, #458bc8, #7bba56, #e68a46);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            color: #fff;
        }
        .peer-review-card h3 {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .peer-review-card p {
            font-size: 14px;
            opacity: 0.85;
            line-height: 1.6;
            margin-bottom: 1rem;
        }
        .btn-white {
            background: #fff;
            color: #262c39;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

    </style>
</head>
<body>

<div class="header">
    <img src="/images/Soccer-Dads-Logo.png" alt="Soccer Dads">
    <span class="header-title">Soccer Dads</span>
</div>

<div style="background:linear-gradient(135deg, #458bc8, #7bba56, #e68a46); padding:1.25rem 2rem; text-align:center;">
    <div style="font-size:33px; font-weight:700; color:#fff; text-shadow:0 1px 4px rgba(0,0,0,0.2);">{{ $message->messageSubject }}</div>
</div>

<div class="container">

    <div class="card" style="margin-bottom:1rem;">
        <div class="message-body" style="margin-bottom:0;">{!! $message->messageBody !!}</div>
    </div>

    {{-- Next game --}}
    @if($nextGame)
    <div class="card">
        <div class="card-title"><i class="fa-solid fa-calendar"></i> Next game</div>
        <div class="next-game-date">{{ \Carbon\Carbon::parse($nextGame->gameDate)->format('l j F Y') }}</div>
        <div class="next-game-sub">{{ $nextGame->seasonName }} · Round {{ $nextGame->gameRound }}</div>

        <div class="reg-buttons">
            <form method="POST" action="/reg/{{ $member->memberCode }}">
                @csrf
                <button type="submit" name="status" value="1"
                    class="btn {{ ($registration?->registrationStatus == 1) ? 'btn-active-yes' : '' }}">
                    <i class="fa-solid fa-circle-check" style="color:#7bba56;"></i> I'm in
                </button>
            </form>
            <form method="POST" action="/reg/{{ $member->memberCode }}">
                @csrf
                <button type="submit" name="status" value="2"
                    class="btn {{ ($registration?->registrationStatus == 2) ? 'btn-active-no' : '' }}">
                    <i class="fa-solid fa-circle-xmark" style="color:#e24b4a;"></i> Can't make it
                </button>
            </form>
        </div>
    </div>
    @endif

    {{-- Peer review nudge --}}
    @if($needsPeerReview)
    <div class="peer-review-card">
        <h3><i class="fa-solid fa-star"></i> Rate your teammates</h3>
        <p>
            Your player ratings help us build more balanced teams each week.
            It only takes a couple of minutes — tap through and rate the players you know!
        </p>
        <a href="/rate/{{ $member->memberCode }}" class="btn-white">
            Start rating
        </a>
    </div>
    @endif

    <div style="text-align:center; margin-top:1.5rem;">
        <a href="/" style="display:inline-flex; align-items:center; gap:8px; background:transparent; color:#aaa; border:1px solid #ddd; padding:10px 22px; border-radius:10px; text-decoration:none; font-size:14px; font-weight:500;">
            <i class="fa-solid fa-link"></i> soccerdads.com.au
        </a>
    </div>

</div>

</body>
</html>
