@props(['title', 'back' => null, 'backLabel' => 'Back'])

@once
@push('styles')
<style>
    .sd-page-header {
        background: linear-gradient(to right, #458bc8, #7bba56, #e68a46);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        padding: 48px 0;
    }
    .sd-page-header h1 {
        margin: 0;
        font-family: 'GetShow', sans-serif;
        font-weight: normal;
        font-size: 72px;
        line-height: 1.05;
        color: #fff;
        text-align: left;
    }
    .sd-page-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
    }
    .sd-page-header-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: rgba(255, 255, 255, 0.7);
        text-decoration: none;
        margin-bottom: 1rem;
    }
    @media (max-width: 600px) {
        .sd-page-header h1 { font-size: 48px; }
    }
</style>
@endpush
@endonce

<div class="sd-page-header">
    <div class="container">
        @if($back)
        <a href="{{ $back }}" class="sd-page-header-back">
            <i class="fa-solid fa-chevron-left"></i> {{ $backLabel }}
        </a>
        @endif
        <div class="sd-page-header-row">
            <h1>{{ $title }}</h1>
            {{ $slot }}
        </div>
    </div>
</div>
