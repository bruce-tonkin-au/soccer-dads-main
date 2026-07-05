{{--
    One night's registration card — header + attendance + child, all in a single
    bordered card. Rendered once per block from registration.blade.php.
    Expects: $block (night, game, registration, activePlayers, benchPosition,
    child, childRegistration), $member, and optionally $multi (bool). The card's
    top-border accent is derived per-night via App\Support\NightColour.

    PRESENTATIONAL ONLY — the hidden gameID/childID fields and POST targets are
    unchanged; the resolver and update() mutation logic are untouched.
--}}
@php
    $night             = $block['night'];
    $game              = $block['game'];
    $registration      = $block['registration'];
    $activePlayers     = $block['activePlayers'];
    $benchPosition     = $block['benchPosition'];
    $child             = $block['child'];
    $childRegistration = $block['childRegistration'];

    $multi  = $multi ?? false;
    // Accent is per-night (Friday = blue, Tuesday = green) and always applied,
    // whether the member has one night or two.
    $accent = \App\Support\NightColour::accent($night->nightName);

    $onBench  = $registration && $registration->registrationStatus == 1 && $registration->registrationBench == 1;
    $isActive = $registration && $registration->registrationStatus == 1 && $registration->registrationBench == 0;

    $childActiveSelected = $childRegistration && $childRegistration->registrationStatus == 1 && !$childRegistration->registrationBench;

    // Confirm dialogs (multi-night only) name the night explicitly.
    $when         = \Carbon\Carbon::parse($game->gameDate)->format('j F');
    $where        = $night->nightVenue ? ' at ' . $night->nightVenue : '';
    $confirmParent = 'Register for ' . strtoupper($night->nightName) . ' ' . $when . $where . '?';
    $confirmChild  = $child ? 'Register ' . $child->memberNameFirst . ' for ' . strtoupper($night->nightName) . ' ' . $when . $where . '?' : '';
@endphp

<div style="background:#fff; border:1px solid #e8e8e8; border-radius:16px; overflow:hidden; border-top:4px solid {{ $accent }};">

    {{-- Header strip --}}
    <div style="background:#262c39; padding:1.5rem; color:#fff; text-align:center;">
        <div style="font-size:12px; color:rgba(255,255,255,0.5); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:4px;">
            {{ $night->nightName }}@if($night->nightVenue) · {{ $night->nightVenue }}@endif
        </div>
        <div style="font-size:22px; font-weight:600; margin-bottom:4px;">
            {{ \Carbon\Carbon::parse($game->gameDate)->format('l j F Y') }}
        </div>
        <div style="font-size:14px; color:rgba(255,255,255,0.6);">
            Round {{ $game->gameRound }} · {{ $game->seasonName }}
        </div>
        <div style="margin-top:1rem; padding-top:1rem; border-top:1px solid rgba(255,255,255,0.1); font-size:13px; color:rgba(255,255,255,0.5);">
            {{ $activePlayers }}/18 players registered
            @if($activePlayers >= 18)
            · <span style="color:#e68a46;">Game is full</span>
            @endif
        </div>
    </div>

    {{-- Body: attendance + child, all inside this one card --}}
    <div style="padding:1.5rem;">

        {{-- Parent attendance --}}
        @if($onBench)
        <p style="font-size:15px; font-weight:600; color:#262c39; margin-bottom:0.5rem;">
            <i class="fa-solid fa-clock" style="color:#e68a46;"></i> You're on the reserves bench
        </p>
        <p style="font-size:14px; color:#666; margin-bottom:1rem;">
            You're #{{ $benchPosition }} in the queue. You'll be automatically moved to the active list if a spot opens up.
        </p>
        <form method="POST" action="/reg/{{ $member->memberCode }}">
            @csrf
            <input type="hidden" name="gameID" value="{{ $game->gameID }}">
            <button type="submit" name="status" value="2"
                style="width:100%; padding:14px; border-radius:12px; border:2px solid #e8e8e8; background:#fff; cursor:pointer; font-size:14px; font-weight:600; color:#888;">
                <i class="fa-solid fa-circle-xmark" style="color:#e24b4a;"></i> Remove me from the bench
            </button>
        </form>
        @else
        <form method="POST" action="/reg/{{ $member->memberCode }}">
            @csrf
            <input type="hidden" name="gameID" value="{{ $game->gameID }}">
            <p style="font-size:15px; font-weight:600; color:#262c39; margin-bottom:1rem;">Are you coming?</p>

            @if($activePlayers >= 18 && !$isActive)
            <div style="background:#fff8ee; border:1px solid #e68a46; border-radius:8px; padding:12px 16px; font-size:14px; color:#262c39; margin-bottom:1rem;">
                <i class="fa-solid fa-clock" style="color:#e68a46;"></i>
                The game is full — registering will add you to the reserves bench.
            </div>
            @endif

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <button type="submit" name="status" value="1"
                    @if($multi) onclick="return confirm(@js($confirmParent))" @endif
                    style="padding:16px; border-radius:12px; border:2px solid {{ $isActive ? '#7bba56' : '#e8e8e8' }}; background:{{ $isActive ? '#f0fdf4' : '#fff' }}; cursor:pointer; font-size:15px; font-weight:600; color:#262c39;">
                    <i class="fa-solid fa-circle-check" style="color:#7bba56; display:block; font-size:24px; margin-bottom:6px;"></i>
                    Yes, I'm in!
                </button>
                <button type="submit" name="status" value="2"
                    style="padding:16px; border-radius:12px; border:2px solid {{ ($registration?->registrationStatus == 2) ? '#e24b4a' : '#e8e8e8' }}; background:{{ ($registration?->registrationStatus == 2) ? '#fff3f3' : '#fff' }}; cursor:pointer; font-size:15px; font-weight:600; color:#262c39;">
                    <i class="fa-solid fa-circle-xmark" style="color:#e24b4a; display:block; font-size:24px; margin-bottom:6px;"></i>
                    Can't make it
                </button>
            </div>
        </form>
        @endif

        {{-- Child attendance — always shown when a child exists; the Yes button
             is gated on the parent being registered-active for THIS night. --}}
        @if($child)
        <form method="POST" action="/reg/{{ $member->memberCode }}" style="margin-top:1.25rem;">
            @csrf
            <input type="hidden" name="gameID" value="{{ $game->gameID }}">
            <input type="hidden" name="childID" value="{{ $child->memberID }}">
            <div style="border-top:1px solid #eee; padding-top:1.25rem;">
                <p style="font-size:15px; font-weight:600; color:#262c39; margin-bottom:4px;">Is {{ $child->memberNameFirst }} coming?</p>
                <p style="font-size:13px; color:#888; margin-bottom:1rem;">Your child can only attend if you're also attending.</p>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <button type="submit" name="childStatus" value="1"
                        {{ $isActive ? '' : 'disabled' }}
                        @if($multi && $isActive) onclick="return confirm(@js($confirmChild))" @endif
                        style="padding:16px; border-radius:12px;
                            border:2px solid {{ ($isActive && $childActiveSelected) ? '#7bba56' : '#e8e8e8' }};
                            background:{{ !$isActive ? '#f7f7f7' : ($childActiveSelected ? '#f0fdf4' : '#fff') }};
                            cursor:{{ $isActive ? 'pointer' : 'not-allowed' }};
                            opacity:{{ $isActive ? '1' : '0.5' }};
                            font-size:15px; font-weight:600; color:#262c39;">
                        <i class="fa-solid fa-circle-check" style="color:#7bba56; display:block; font-size:24px; margin-bottom:6px;"></i>
                        Yes!
                    </button>
                    <button type="submit" name="childStatus" value="2"
                        style="padding:16px; border-radius:12px; border:2px solid {{ ($childRegistration?->registrationStatus == 2) ? '#e24b4a' : '#e8e8e8' }}; background:{{ ($childRegistration?->registrationStatus == 2) ? '#fff3f3' : '#fff' }}; cursor:pointer; font-size:15px; font-weight:600; color:#262c39;">
                        <i class="fa-solid fa-circle-xmark" style="color:#e24b4a; display:block; font-size:24px; margin-bottom:6px;"></i>
                        Not this time
                    </button>
                </div>
            </div>
        </form>
        @endif

    </div>
</div>
