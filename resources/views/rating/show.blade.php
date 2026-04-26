<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate Players — Soccer Dads</title>
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
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .header-title {
            font-family: 'GetShow';
            font-size: 24px;
            color: #fff;
            font-weight: normal;
        }
        .header-progress {
            font-size: 13px;
            color: rgba(255,255,255,0.6);
        }

        .progress-bar { height: 4px; background: rgba(255,255,255,0.1); }
        .progress-fill {
            height: 4px;
            background: linear-gradient(to right, #7bba56, #458bc8);
            transition: width 0.3s;
        }

        .player-card {
            background: #fff;
            margin: 1.5rem;
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }

        .player-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 0 auto 1rem;
            background: #f4f4f4;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 3px solid #e8e8e8;
        }
        .player-photo img { width: 100%; height: 100%; object-fit: cover; }
        .player-name { font-size: 22px; font-weight: 700; color: #262c39; margin-bottom: 4px; }
        .player-sub { font-size: 13px; color: #aaa; }

        .questions { margin: 0 1.5rem 1.5rem; }

        .question-block {
            background: #fff;
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        .question-label { font-size: 17px; font-weight: 600; color: #262c39; margin-bottom: 1rem; text-align: center; }
        .options { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
        .option-btn {
            padding: 10px 4px;
            border-radius: 10px;
            border: 2px solid #e8e8e8;
            background: #fff;
            cursor: pointer;
            font-size: 11px;
            color: #262c39;
            text-align: center;
            transition: all 0.15s;
            line-height: 1.4;
        }
        .option-btn:hover { border-color: #458bc8; background: #f0f7ff; }
        .option-btn.selected { border-color: #458bc8; background: #458bc8; color: #fff; }
        .option-icon { font-size: 22px; display: block; margin-bottom: 6px; font-weight: 900; }
        .option-btn.selected .option-icon { color: #fff; }

        .actions {
            display: grid;
            grid-template-columns: 1fr 3fr;
            gap: 10px;
            margin: 0 1.5rem 2rem;
        }
        .btn-skip {
            padding: 14px;
            border-radius: 12px;
            border: 1px solid #e8e8e8;
            background: #fff;
            color: #aaa;
            font-size: 14px;
            cursor: pointer;
        }
        .btn-next {
            padding: 14px;
            border-radius: 12px;
            border: none;
            background: #262c39;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            opacity: 0.4;
            transition: opacity 0.2s;
        }
        .btn-next.ready { opacity: 1; }
    </style>
</head>
<body>

<div class="header">
    <div class="header-title">Rate Players</div>
    <div class="header-progress">{{ $ratedCount }} of {{ $totalToRate }} rated</div>
</div>
<div class="progress-bar">
    <div class="progress-fill" style="width:{{ $totalToRate > 0 ? round(($ratedCount / $totalToRate) * 100) : 0 }}%"></div>
</div>

<div class="player-card">
    <div class="player-photo">
        @if($nextPlayer->memberPhoto)
            <img src="{{ Storage::url($nextPlayer->memberPhoto) }}" alt="{{ $nextPlayer->memberNameFirst }}">
        @else
            <i class="fa-solid fa-user-large" style="font-size:48px; color:#ccc;"></i>
        @endif
    </div>
    <div class="player-name">{{ $nextPlayer->memberNameFirst }} {{ $nextPlayer->memberNameLast }}</div>
    <div class="player-sub">How well do you know this player?</div>
</div>

<div class="questions-wrapper">
    <div class="questions">

        <div class="question-block">
            <div class="question-label">How dangerous are they in front of goal?</div>
            <div class="options">
                <button type="button" class="option-btn" data-field="ratingGoal" data-value="1" onclick="selectOption(this)">
                    <i class="fa-solid fa-face-meh option-icon"></i>Rarely scores
                </button>
                <button type="button" class="option-btn" data-field="ratingGoal" data-value="2" onclick="selectOption(this)">
                    <i class="fa-solid fa-futbol option-icon"></i>Occasional
                </button>
                <button type="button" class="option-btn" data-field="ratingGoal" data-value="3" onclick="selectOption(this)">
                    <i class="fa-solid fa-fire option-icon"></i>Regular scorer
                </button>
                <button type="button" class="option-btn" data-field="ratingGoal" data-value="4" onclick="selectOption(this)">
                    <i class="fa-solid fa-bullseye option-icon"></i>Clinical
                </button>
            </div>
        </div>

        <div class="question-block">
            <div class="question-label">How do they move the ball?</div>
            <div class="options">
                <button type="button" class="option-btn" data-field="ratingPassing" data-value="1" onclick="selectOption(this)">
                    <i class="fa-solid fa-minus option-icon"></i>Basic
                </button>
                <button type="button" class="option-btn" data-field="ratingPassing" data-value="2" onclick="selectOption(this)">
                    <i class="fa-solid fa-thumbs-up option-icon"></i>Decent
                </button>
                <button type="button" class="option-btn" data-field="ratingPassing" data-value="3" onclick="selectOption(this)">
                    <i class="fa-solid fa-eye option-icon"></i>Great vision
                </button>
                <button type="button" class="option-btn" data-field="ratingPassing" data-value="4" onclick="selectOption(this)">
                    <i class="fa-solid fa-wand-magic-sparkles option-icon"></i>Playmaker
                </button>
            </div>
        </div>

        <div class="question-block">
            <div class="question-label">How hard do they work?</div>
            <div class="options">
                <button type="button" class="option-btn" data-field="ratingWork" data-value="1" onclick="selectOption(this)">
                    <i class="fa-solid fa-bed option-icon"></i>Quiet game
                </button>
                <button type="button" class="option-btn" data-field="ratingWork" data-value="2" onclick="selectOption(this)">
                    <i class="fa-solid fa-person option-icon"></i>Contributes
                </button>
                <button type="button" class="option-btn" data-field="ratingWork" data-value="3" onclick="selectOption(this)">
                    <i class="fa-solid fa-dumbbell option-icon"></i>High energy
                </button>
                <button type="button" class="option-btn" data-field="ratingWork" data-value="4" onclick="selectOption(this)">
                    <i class="fa-solid fa-person-running option-icon"></i>Engine room
                </button>
            </div>
        </div>

        <div class="question-block">
            <div class="question-label">How do they defend?</div>
            <div class="options">
                <button type="button" class="option-btn" data-field="ratingDefending" data-value="1" onclick="selectOption(this)">
                    <i class="fa-solid fa-face-grimace option-icon"></i>Avoids it
                </button>
                <button type="button" class="option-btn" data-field="ratingDefending" data-value="2" onclick="selectOption(this)">
                    <i class="fa-solid fa-shoe-prints option-icon"></i>Gets stuck in
                </button>
                <button type="button" class="option-btn" data-field="ratingDefending" data-value="3" onclick="selectOption(this)">
                    <i class="fa-solid fa-shield option-icon"></i>Solid
                </button>
                <button type="button" class="option-btn" data-field="ratingDefending" data-value="4" onclick="selectOption(this)">
                    <i class="fa-solid fa-shield-halved option-icon"></i>Rock solid
                </button>
            </div>
        </div>

        <div class="question-block">
            <div class="question-label">Overall — what kind of player are they?</div>
            <div class="options">
                <button type="button" class="option-btn" data-field="ratingOverall" data-value="1" onclick="selectOption(this)">
                    <i class="fa-solid fa-bed option-icon"></i>Takes it easy
                </button>
                <button type="button" class="option-btn" data-field="ratingOverall" data-value="2" onclick="selectOption(this)">
                    <i class="fa-solid fa-futbol option-icon"></i>Solid player
                </button>
                <button type="button" class="option-btn" data-field="ratingOverall" data-value="3" onclick="selectOption(this)">
                    <i class="fa-solid fa-star option-icon"></i>Good player
                </button>
                <button type="button" class="option-btn" data-field="ratingOverall" data-value="4" onclick="selectOption(this)">
                    <i class="fa-solid fa-trophy option-icon"></i>Top player
                </button>
            </div>
        </div>

    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 3fr; gap:10px; margin:0 1.5rem 2rem;">

    <form method="POST" action="/rate/{{ $rater->memberCode }}">
        @csrf
        <input type="hidden" name="ratedMemberID" value="{{ $nextPlayer->memberID }}">
        <input type="hidden" name="action" value="skip">
        <button type="submit" class="btn-skip" style="width:100%;">
            <i class="fa-solid fa-forward"></i> Skip
        </button>
    </form>

    <form method="POST" action="/rate/{{ $rater->memberCode }}" id="rating-form">
        @csrf
        <input type="hidden" name="ratedMemberID" value="{{ $nextPlayer->memberID }}">
        <input type="hidden" name="action" value="rate">
        @foreach(['ratingGoal', 'ratingPassing', 'ratingWork', 'ratingDefending', 'ratingOverall'] as $field)
        <input type="hidden" name="{{ $field }}" id="{{ $field }}" value="0">
        @endforeach
        <button type="submit" class="btn-next" id="btn-next" disabled>
            Next player <i class="fa-solid fa-arrow-right"></i>
        </button>
    </form>

</div>

<script>
    const answers = {
        ratingGoal: 0,
        ratingPassing: 0,
        ratingWork: 0,
        ratingDefending: 0,
        ratingOverall: 0
    };

    function selectOption(btn) {
        const field = btn.dataset.field;
        const value = parseInt(btn.dataset.value);

        btn.closest('.options').querySelectorAll('.option-btn').forEach(b => b.classList.remove('selected'));
        btn.classList.add('selected');

        document.getElementById(field).value = value;
        answers[field] = value;

        checkReady();
    }

    function checkReady() {
        const allAnswered = Object.values(answers).every(v => v > 0);
        const btn = document.getElementById('btn-next');
        btn.classList.toggle('ready', allAnswered);
        btn.disabled = !allAnswered;
    }

    checkReady();
</script>

</body>
</html>
