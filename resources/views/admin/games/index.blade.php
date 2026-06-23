@extends('admin.layout')
@section('title', 'Games')
@section('content')

<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <div>
            <a href="/admin/seasons" style="font-size:13px; color:#888; text-decoration:none;">← Seasons</a>
            <h2 style="margin-bottom:0; margin-top:4px;">{{ $season->seasonName }} — Games</h2>
        </div>
        <a href="/admin/seasons/{{ $season->seasonID }}/games/create" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Add game
        </a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Round</th>
                <th>Date</th>
                <th>YouTube</th>
                <th>Visible</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($games as $game)
            @php
                $isChargeable  = $game->gameDate >= '2026-05-01';
                $hasTeams      = $isChargeable && $gamesWithTeams->contains($game->gameID);
                $alreadyCharged = $isChargeable && $chargedGameIDs->contains($game->gameID);
            @endphp
            <tr>
                <td style="font-weight:600;">Round {{ $game->gameRound }}</td>
                <td>{{ $game->gameDate }}</td>
                <td>{{ $game->gameYouTube ? '✓' : '—' }}</td>
                <td>
                    @if($game->gameVisible)
                    <span style="background:#f0fdf4; color:#7bba56; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600;">Yes</span>
                    @else
                    <span style="background:#f4f4f4; color:#aaa; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600;">No</span>
                    @endif
                </td>
                <td style="display:flex; gap:8px; align-items:center;">
                    <a href="/admin/teams/{{ $game->gameID }}" class="btn btn-secondary" style="padding:6px 12px; font-size:13px;">
                        <i class="fa-solid fa-users"></i> Teams
                    </a>
                    <a href="/admin/print/{{ $game->gameID }}" target="_blank" class="btn btn-secondary" style="padding:6px 12px; font-size:13px;">
                        <i class="fa-solid fa-print"></i> Print
                    </a>
                    <a href="/admin/seasons/{{ $season->seasonID }}/games/{{ $game->gameID }}/edit" class="btn btn-secondary" style="padding:6px 12px; font-size:13px;">
                        <i class="fa-solid fa-pen"></i> Edit
                    </a>
                    @if($alreadyCharged)
                    <span style="display:inline-flex; align-items:center; gap:4px; color:#7bba56; font-size:13px; font-weight:600; padding:6px 12px;">
                        <i class="fa-solid fa-circle-check"></i> Charged
                    </span>
                    @elseif($hasTeams)
                    <button
                        class="btn btn-secondary js-charge-btn"
                        style="padding:6px 12px; font-size:13px; color:#e68a46; border-color:#e68a46;"
                        data-game-id="{{ $game->gameID }}"
                        data-season-id="{{ $season->seasonID }}"
                        data-round="{{ $game->gameRound }}"
                        data-preview-url="/admin/seasons/{{ $season->seasonID }}/games/{{ $game->gameID }}/preview-charges"
                    >
                        <i class="fa-solid fa-dollar-sign"></i> Charge players
                    </button>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Season ladder --}}
@if($ladder->isNotEmpty())
<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom:0.5rem;">
        <h2 style="margin-bottom:0;">Season ladder</h2>
        <span style="font-size:12px; color:#888;">Sorted by average points per game</span>
    </div>
    <p style="font-size:13px; color:#888; margin-bottom:1rem;">
        Title contenders must have played {{ $threshold }} {{ \Illuminate\Support\Str::plural('game', $threshold) }}
        (season average {{ number_format($avgGamesPlayed, 1) }} rounded up).
    </p>
    <table>
        <thead>
            <tr>
                <th style="width:48px;">Rank</th>
                <th>Player</th>
                <th style="text-align:center;">Games played</th>
                <th style="text-align:center;">Total points</th>
                <th style="text-align:right;">Average</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ladder as $i => $p)
            <tr style="{{ $p->eligible ? '' : 'color:#bbb;' }}">
                <td style="font-weight:600;{{ $p->eligible ? '' : 'color:#bbb;' }}">{{ $i + 1 }}</td>
                <td style="{{ $p->eligible ? '' : 'color:#bbb;' }}">
                    {{ $p->name }}
                    @unless($p->eligible)
                    <span style="font-size:11px; color:#c0392b; background:#fff3f3; border:1px solid #f3c6c6; border-radius:10px; padding:1px 7px; margin-left:6px; white-space:nowrap;">
                        ineligible — {{ $p->gamesPlayed }} {{ \Illuminate\Support\Str::plural('game', $p->gamesPlayed) }}
                    </span>
                    @endunless
                </td>
                <td style="text-align:center;">{{ $p->gamesPlayed }}</td>
                <td style="text-align:center;">{{ $p->totalPoints }}</td>
                <td style="text-align:right; font-weight:600;">{{ number_format($p->average, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Average progression chart (title-eligible players only) --}}
@if(count($chartSeries) > 0)
<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom:1rem;">
        <h2 style="margin-bottom:0;">Average progression</h2>
        <span style="font-size:12px; color:#888;">Title-eligible players only ({{ count($chartSeries) }} plotted)</span>
    </div>
    <canvas id="ladderChart" height="120"></canvas>
</div>
@endif
@else
<div class="admin-card">
    <h2 style="margin-bottom:0.5rem;">Season ladder</h2>
    <p style="font-size:14px; color:#888;">No results recorded for this season yet.</p>
</div>
@endif

{{-- Charge players modal --}}
<div id="charge-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; padding:2rem; max-width:580px; width:90%; max-height:85vh; display:flex; flex-direction:column; box-shadow:0 8px 32px rgba(0,0,0,0.18);">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem;">
            <h2 style="font-size:18px; font-weight:700; color:#262c39; margin:0;" id="modal-title">Charge players</h2>
            <button id="modal-close" style="background:none; border:none; font-size:20px; color:#aaa; cursor:pointer; padding:4px;">×</button>
        </div>
        <div id="modal-body" style="overflow-y:auto; flex:1;">
            <p style="color:#888; font-size:14px;">Loading…</p>
        </div>
        <div id="modal-error" style="display:none; color:#e24b4a; font-size:13px; margin-top:10px; padding:8px 12px; background:#fff3f3; border-radius:6px; border:1px solid #fcc;"></div>
        <div id="modal-footer" style="margin-top:1.25rem; display:flex; gap:8px; justify-content:flex-end; align-items:center;">
            <button id="modal-cancel" class="btn btn-secondary">Cancel</button>
            <form id="charge-form" method="POST" style="display:none;">
                @csrf
                <button type="submit" class="btn btn-primary" style="background:#e68a46;">
                    <i class="fa-solid fa-dollar-sign"></i> Confirm &amp; charge
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var modal       = document.getElementById('charge-modal');
    var modalTitle  = document.getElementById('modal-title');
    var modalBody   = document.getElementById('modal-body');
    var modalError  = document.getElementById('modal-error');
    var chargeForm  = document.getElementById('charge-form');
    var modalClose  = document.getElementById('modal-close');
    var modalCancel = document.getElementById('modal-cancel');

    function openModal()  { modal.style.display = 'flex'; }
    function closeModal() { modal.style.display = 'none'; chargeForm.style.display = 'none'; hideError(); }
    function showError(msg) { modalError.textContent = msg; modalError.style.display = 'block'; }
    function hideError()    { modalError.style.display = 'none'; }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    modalClose.addEventListener('click', closeModal);
    modalCancel.addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });

    // Intercept form submit: validate inputs, inject hidden fields, then submit
    chargeForm.addEventListener('submit', function (e) {
        e.preventDefault();
        hideError();

        var inputs = modalBody.querySelectorAll('.charge-amount-input');
        var valid  = true;

        inputs.forEach(function (input) {
            var val = parseFloat(input.value);
            if (isNaN(val) || val < 0 || val > 50) {
                valid = false;
                input.style.borderColor = '#e24b4a';
                input.style.boxShadow   = '0 0 0 2px rgba(226,75,74,0.15)';
            }
            // re-apply correct border for valid fields (may have been reset from a prior attempt)
            if (!isNaN(val) && val >= 0 && val <= 50) {
                var isModified = input.value !== input.dataset.default;
                input.style.borderColor = isModified ? '#458bc8' : '#e8e8e8';
                input.style.boxShadow   = isModified ? '0 0 0 2px rgba(69,139,200,0.15)' : 'none';
            }
        });

        if (!valid) {
            showError('All amounts must be between $0.00 and $50.00.');
            return;
        }

        // Remove any previously injected hidden inputs
        chargeForm.querySelectorAll('.js-charge-hidden').forEach(function (el) { el.remove(); });

        // Inject one pair of hidden inputs per player
        inputs.forEach(function (input, i) {
            var mId = document.createElement('input');
            mId.type      = 'hidden';
            mId.name      = 'charges[' + i + '][memberID]';
            mId.value     = input.dataset.memberId;
            mId.className = 'js-charge-hidden';
            chargeForm.appendChild(mId);

            var amt = document.createElement('input');
            amt.type      = 'hidden';
            amt.name      = 'charges[' + i + '][amount]';
            amt.value     = parseFloat(input.value).toFixed(2);
            amt.className = 'js-charge-hidden';
            chargeForm.appendChild(amt);
        });

        chargeForm.submit();
    });

    document.querySelectorAll('.js-charge-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var gameID     = btn.dataset.gameId;
            var seasonID   = btn.dataset.seasonId;
            var round      = btn.dataset.round;
            var previewUrl = btn.dataset.previewUrl;

            modalTitle.textContent = 'Charge players — Round ' + round;
            modalBody.innerHTML    = '<p style="color:#888; font-size:14px;">Loading…</p>';
            chargeForm.style.display = 'none';
            hideError();
            openModal();

            fetch(previewUrl, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.error) {
                    modalBody.innerHTML = '<p style="color:#e24b4a; font-size:14px;">' + escapeHtml(data.error) + '</p>';
                    return;
                }

                var charges = data.charges;
                if (!charges || charges.length === 0) {
                    modalBody.innerHTML = '<p style="color:#888; font-size:14px;">No players found in teams for this game night.</p>';
                    return;
                }

                // Build table with editable amount inputs
                var table = document.createElement('table');
                table.style.cssText = 'width:100%; border-collapse:collapse; font-size:14px;';

                var thStyle = 'text-align:left; padding:6px 8px; background:#f8f8f8; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; color:#262c39; border-bottom:1px solid #e8e8e8;';
                table.innerHTML =
                    '<thead><tr>' +
                    '<th style="' + thStyle + '">Player</th>' +
                    '<th style="' + thStyle + ' text-align:right; width:150px;">Amount</th>' +
                    '<th style="' + thStyle + '">Reason</th>' +
                    '</tr></thead>';

                var tbody = document.createElement('tbody');

                charges.forEach(function (c) {
                    var defaultAmt = Math.abs(c.amount).toFixed(2);
                    var tr = document.createElement('tr');

                    // Player name cell
                    var tdName = document.createElement('td');
                    tdName.style.cssText = 'padding:8px; border-bottom:1px solid #f0f0f0; color:#262c39;';
                    tdName.textContent = c.memberName;

                    // Amount cell — editable input + modified badge
                    var tdAmt = document.createElement('td');
                    tdAmt.style.cssText = 'padding:6px 8px; border-bottom:1px solid #f0f0f0;';

                    var amtWrap = document.createElement('div');
                    amtWrap.style.cssText = 'display:flex; align-items:center; gap:4px; justify-content:flex-end;';

                    var dollar = document.createElement('span');
                    dollar.textContent = '$';
                    dollar.style.cssText = 'color:#aaa; font-size:13px;';

                    var input = document.createElement('input');
                    input.type              = 'number';
                    input.min               = '0';
                    input.max               = '50';
                    input.step              = '0.50';
                    input.value             = defaultAmt;
                    input.dataset.default   = defaultAmt;
                    input.dataset.memberId  = c.memberID;
                    input.className         = 'charge-amount-input';
                    input.style.cssText     = 'width:72px; border:1px solid #e8e8e8; border-radius:6px; padding:5px 8px; font-size:14px; font-weight:600; text-align:right; outline:none; transition:border-color 0.15s, box-shadow 0.15s;';

                    var badge = document.createElement('span');
                    badge.textContent   = 'edited';
                    badge.style.cssText = 'display:none; font-size:11px; font-weight:600; color:#458bc8; white-space:nowrap;';

                    input.addEventListener('input', function () {
                        var val       = parseFloat(this.value);
                        var modified  = this.value !== this.dataset.default;
                        var outOfRange = isNaN(val) || val < 0 || val > 50;

                        if (outOfRange) {
                            this.style.borderColor = '#e24b4a';
                            this.style.boxShadow   = '0 0 0 2px rgba(226,75,74,0.15)';
                        } else if (modified) {
                            this.style.borderColor = '#458bc8';
                            this.style.boxShadow   = '0 0 0 2px rgba(69,139,200,0.15)';
                        } else {
                            this.style.borderColor = '#e8e8e8';
                            this.style.boxShadow   = 'none';
                        }
                        badge.style.display = (modified && !outOfRange) ? 'inline' : 'none';
                        hideError();
                    });

                    amtWrap.appendChild(dollar);
                    amtWrap.appendChild(input);
                    amtWrap.appendChild(badge);
                    tdAmt.appendChild(amtWrap);

                    // Reason cell
                    var tdReason = document.createElement('td');
                    tdReason.style.cssText = 'padding:8px; border-bottom:1px solid #f0f0f0; color:#888; font-size:13px;';
                    tdReason.textContent = c.reason;

                    tr.appendChild(tdName);
                    tr.appendChild(tdAmt);
                    tr.appendChild(tdReason);
                    tbody.appendChild(tr);
                });

                table.appendChild(tbody);

                var intro = document.createElement('p');
                intro.style.cssText = 'font-size:13px; color:#888; margin-bottom:12px;';
                intro.textContent = 'Adjust amounts if needed, then confirm to apply charges.';

                modalBody.innerHTML = '';
                modalBody.appendChild(intro);
                modalBody.appendChild(table);

                chargeForm.action = '/admin/seasons/' + seasonID + '/games/' + gameID + '/charge';
                chargeForm.style.display = 'block';
            })
            .catch(function () {
                modalBody.innerHTML = '<p style="color:#e24b4a; font-size:14px;">Failed to load preview. Please try again.</p>';
            });
        });
    });
}());
</script>
@endpush

{{-- Average progression chart — reuses the Chart.js CDN pattern from seasons/night.blade.php --}}
@if(!empty($chartSeries))
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    var labels = @json($chartLabels);
    var series = @json($chartSeries);
    var canvas = document.getElementById('ladderChart');
    if (!canvas || !window.Chart) return;

    var datasets = series.map(function (s, i) {
        var color = 'hsl(' + Math.round((i * 360) / series.length) + ', 65%, 50%)';
        return {
            label: s.name,
            data: s.data,
            borderColor: color,
            backgroundColor: color,
            borderWidth: 2,
            pointRadius: 2,
            tension: 0.2,
            fill: false,
            spanGaps: false,
        };
    });

    new Chart(canvas.getContext('2d'), {
        type: 'line',
        data: { labels: labels, datasets: datasets },
        options: {
            responsive: true,
            interaction: { mode: 'nearest', intersect: false },
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                tooltip: { mode: 'index', intersect: false },
            },
            scales: {
                y: { title: { display: true, text: 'Average points / game' }, suggestedMin: 1, suggestedMax: 3 },
                x: { title: { display: true, text: 'Round' } },
            },
        },
    });
}());
</script>
@endpush
@endif

@endsection
