@extends('admin.layout')
@section('title', 'Message Links')

@push('styles')
<style>
    .link-block {
        padding: 16px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .link-block:last-child { border-bottom: none; }
    .link-label { font-weight: 600; font-size: 14px; color: #262c39; margin-bottom: 6px; }
    .link-hint { font-size: 12px; color: #888; margin-bottom: 10px; }
    .link-controls { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .link-url {
        font-size: 12px;
        color: #888;
        font-family: monospace;
        background: #f4f4f4;
        padding: 8px 10px;
        border-radius: 6px;
        flex: 1;
        min-width: 220px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .copy-btn {
        background: #f4f4f4;
        border: 1px solid #e8e8e8;
        border-radius: 6px;
        padding: 8px 12px;
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
        <p style="font-size:13px; color:#888; margin-top:2px;">Code: <code>{{ $message->messageCode }}</code></p>
    </div>
</div>

@php
    $smsLink = url('/msg/' . $message->messageCode) . '/{memberCode}';
    $newsletterLink = url('/msg/' . $message->messageCode . '/newsletter');
@endphp

<div class="admin-card">
    <div class="link-block">
        <div class="link-label"><i class="fa-solid fa-comment-sms"></i> SMS Link</div>
        <div class="link-hint">Per-player link sent via SMS — replace <code>{memberCode}</code> with the player's code.</div>
        <div class="link-controls">
            <div class="link-url" title="{{ $smsLink }}">{{ $smsLink }}</div>
            <button class="copy-btn" onclick="copyLink(this, '{{ $smsLink }}')">
                <i class="fa-solid fa-copy"></i> Copy
            </button>
        </div>
    </div>

    <div class="link-block">
        <div class="link-label"><i class="fa-solid fa-envelope"></i> Newsletter Link</div>
        <div class="link-hint">Email-friendly HTML page to copy into Sendy.</div>
        <div class="link-controls">
            <div class="link-url" title="{{ $newsletterLink }}">{{ $newsletterLink }}</div>
            <button class="copy-btn" onclick="copyLink(this, '{{ $newsletterLink }}')">
                <i class="fa-solid fa-copy"></i> Copy
            </button>
            <a href="{{ $newsletterLink }}" target="_blank" class="copy-btn">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Preview
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function copyLink(btn, url) {
        navigator.clipboard.writeText(url).then(() => {
            const original = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
            btn.classList.add('copied');
            setTimeout(() => {
                btn.innerHTML = original;
                btn.classList.remove('copied');
            }, 2000);
        });
    }
</script>
@endpush

@endsection
