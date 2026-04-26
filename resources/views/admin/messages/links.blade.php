@extends('admin.layout')
@section('title', 'Message Links')

@push('styles')
<style>
    .link-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #f0f0f0;
        gap: 1rem;
    }
    .link-row:last-child { border-bottom: none; }
    .player-name { font-weight: 500; font-size: 14px; min-width: 180px; }
    .link-url {
        font-size: 12px;
        color: #888;
        font-family: monospace;
        background: #f4f4f4;
        padding: 4px 8px;
        border-radius: 4px;
        flex: 1;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .copy-btn {
        background: #f4f4f4;
        border: 1px solid #e8e8e8;
        border-radius: 6px;
        padding: 6px 12px;
        font-size: 12px;
        cursor: pointer;
        white-space: nowrap;
        color: #262c39;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .copy-btn:hover { background: #e8e8e8; }
    .copy-btn.copied { background: #f0fdf4; border-color: #7bba56; color: #7bba56; }
</style>
@endpush

@section('content')

<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem;">
    <a href="/admin/messages" class="btn btn-secondary" style="padding:6px 12px;">
        <i class="fa-solid fa-chevron-left"></i>
    </a>
    <div>
        <h1 style="font-size:22px; font-weight:700; color:#262c39;">{{ $message->messageSubject }}</h1>
        <p style="font-size:13px; color:#888; margin-top:2px;">Code: <code>{{ $message->messageCode }}</code> · Copy individual links to send via SMS</p>
    </div>
</div>

<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <h2 style="margin-bottom:0;">Player links ({{ $players->count() }})</h2>
        <button onclick="copyAll(event)" class="btn btn-primary">
            <i class="fa-solid fa-copy"></i> Copy all as list
        </button>
    </div>

    <div style="margin-bottom:1rem;">
        <input type="text" id="search-players" placeholder="Search players..."
               class="form-control" style="max-width:300px;"
               oninput="filterPlayers(this.value)">
    </div>

    <div id="links-list">
        @foreach($players as $player)
        @php $url = url('/msg/' . $message->messageCode . '/' . $player->memberCode); @endphp
        <div class="link-row" data-name="{{ strtolower($player->memberNameFirst . ' ' . $player->memberNameLast) }}">
            <div class="player-name">{{ $player->memberNameFirst }} {{ $player->memberNameLast }}</div>
            <div class="link-url" title="{{ $url }}">{{ $url }}</div>
            <button class="copy-btn" onclick="copyLink(this, '{{ $url }}')">
                <i class="fa-solid fa-copy"></i> Copy
            </button>
            <a href="{{ $url }}" target="_blank" class="copy-btn">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Preview
            </a>
        </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
    function copyLink(btn, url) {
        navigator.clipboard.writeText(url).then(() => {
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
            btn.classList.add('copied');
            setTimeout(() => {
                btn.innerHTML = '<i class="fa-solid fa-copy"></i> Copy';
                btn.classList.remove('copied');
            }, 2000);
        });
    }

    function copyAll(event) {
        const rows = document.querySelectorAll('.link-row');
        let text = '';
        rows.forEach(row => {
            if (row.style.display === 'none') return;
            const name = row.querySelector('.player-name').textContent.trim();
            const url = row.querySelector('.link-url').title;
            text += name + ': ' + url + '\n';
        });
        navigator.clipboard.writeText(text).then(() => {
            const btn = event.target.closest('button');
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
            setTimeout(() => {
                btn.innerHTML = '<i class="fa-solid fa-copy"></i> Copy all as list';
            }, 2000);
        });
    }

    function filterPlayers(query) {
        const rows = document.querySelectorAll('.link-row');
        rows.forEach(row => {
            row.style.display = row.dataset.name.includes(query.toLowerCase()) ? '' : 'none';
        });
    }
</script>
@endpush

@endsection
