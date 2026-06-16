@props(['title'])

@once
@push('styles')
<style>
    .sd-page-header {
        background: linear-gradient(to right, #3b82c4, #4aaa6e, #c8a83c);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        padding: 40px 0;
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
    @media (max-width: 600px) {
        .sd-page-header h1 { font-size: 48px; }
    }
</style>
@endpush
@endonce

<div class="sd-page-header">
    <div class="container">
        <h1>{{ $title }}</h1>
    </div>
</div>
