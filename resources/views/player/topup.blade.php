@extends('layouts.app')
@section('title', 'Top Up — Soccer Dads')
@section('content')

<div style="background:#262c39; padding:3rem 2rem;">
    <div class="container">
        <a href="/portal/account" style="font-size:13px; color:rgba(255,255,255,0.5); text-decoration:none; display:inline-flex; align-items:center; gap:6px; margin-bottom:1.5rem;">
            <i class="fa-solid fa-chevron-left"></i> Account
        </a>
        <h1 style="font-family:'GetShow'; font-weight:normal; font-size:56px; color:#fff;">Top Up</h1>
    </div>
</div>

<div style="padding:3rem 2rem;">
    <div class="container" style="max-width:480px;">

        @if(session('info'))
        <div style="background:#fff8f0; border:1px solid #e68a46; border-radius:8px; padding:12px 16px; margin-bottom:1.5rem; font-size:14px; color:#262c39;">
            <i class="fa-solid fa-circle-info" style="color:#e68a46;"></i> {{ session('info') }}
        </div>
        @endif

        {{-- Current balance --}}
        <div style="background:#fff; border:1px solid #e8e8e8; border-radius:16px; padding:1.5rem; margin-bottom:1.5rem; display:flex; align-items:center; justify-content:space-between;">
            <div>
                <div style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:4px;">Current balance</div>
                <div style="font-size:28px; font-weight:700; color:{{ $balance < 0 ? '#e24b4a' : '#262c39' }};">
                    ${{ number_format(abs($balance), 2) }}{{ $balance < 0 ? ' owing' : '' }}
                </div>
            </div>
            @if($balance < 0)
            <div style="background:#fff3f3; border:1px solid #e24b4a; border-radius:8px; padding:8px 14px; font-size:13px; font-weight:600; color:#e24b4a;">
                Outstanding
            </div>
            @elseif($balance > 0)
            <div style="background:#f0fdf4; border:1px solid #7bba56; border-radius:8px; padding:8px 14px; font-size:13px; font-weight:600; color:#7bba56;">
                In credit
            </div>
            @endif
        </div>

        {{-- Amount selection --}}
        <div style="background:#fff; border:1px solid #e8e8e8; border-radius:16px; padding:2rem;">
            <h2 style="font-size:16px; font-weight:600; color:#262c39; margin-bottom:1.25rem;">Choose an amount</h2>

            <form method="POST" action="/portal/topup/create" id="topup-form">
                @csrf
                <input type="hidden" name="amount" id="amount-input" value="">

                {{-- Quick select buttons --}}
                <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:8px; margin-bottom:1.25rem;">
                    @foreach([20, 40, 60, 80, 100, 150] as $preset)
                    <button type="button" onclick="selectAmount({{ $preset }})"
                        id="btn-{{ $preset }}"
                        style="padding:14px 8px; border-radius:10px; border:2px solid #e8e8e8; background:#fff; cursor:pointer; font-size:16px; font-weight:600; color:#262c39; transition:all 0.15s;">
                        ${{ $preset }}
                    </button>
                    @endforeach
                </div>

                {{-- Custom amount --}}
                <div style="margin-bottom:1.5rem;">
                    <label style="display:block; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; color:#888; margin-bottom:8px;">Or enter a custom amount</label>
                    <div style="position:relative;">
                        <span style="position:absolute; left:14px; top:50%; transform:translateY(-50%); font-size:16px; font-weight:600; color:#aaa;">$</span>
                        <input type="number" id="custom-amount" min="5" max="500" step="1" placeholder="0"
                            style="width:100%; border:2px solid #e8e8e8; border-radius:10px; padding:12px 14px 12px 30px; font-size:16px; font-weight:600; color:#262c39; outline:none;"
                            oninput="onCustomInput(this.value)">
                    </div>
                    <div style="font-size:12px; color:#aaa; margin-top:4px;">Minimum $5, maximum $500</div>
                </div>

                <button type="submit" id="pay-btn" disabled
                    style="width:100%; background:#262c39; color:#fff; border:none; border-radius:10px; padding:14px; font-size:15px; font-weight:600; cursor:not-allowed; opacity:0.4; transition:opacity 0.15s;">
                    <i class="fa-brands fa-stripe"></i> Pay with card
                </button>
                <div style="text-align:center; margin-top:12px;">
                    <img src="https://stripe.com/img/v3/home/social.png" alt="" style="display:none;">
                    <div style="font-size:12px; color:#aaa;">Secure payment powered by Stripe</div>
                    <div style="font-size:11px; color:#bbb; margin-top:2px;">
                        <i class="fa-solid fa-lock"></i> Your card details are never stored on our servers
                    </div>
                </div>
            </form>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
    const presets = [20, 40, 60, 80, 100, 150];
    let selected = null;

    function selectAmount(val) {
        selected = val;
        document.getElementById('amount-input').value = val;
        document.getElementById('custom-amount').value = '';
        presets.forEach(p => {
            const btn = document.getElementById('btn-' + p);
            btn.style.border = p === val ? '2px solid #262c39' : '2px solid #e8e8e8';
            btn.style.background = p === val ? '#262c39' : '#fff';
            btn.style.color = p === val ? '#fff' : '#262c39';
        });
        updatePayBtn(val);
    }

    function onCustomInput(val) {
        selected = null;
        presets.forEach(p => {
            const btn = document.getElementById('btn-' + p);
            btn.style.border = '2px solid #e8e8e8';
            btn.style.background = '#fff';
            btn.style.color = '#262c39';
        });
        const num = parseFloat(val);
        document.getElementById('amount-input').value = (num >= 5 && num <= 500) ? num : '';
        updatePayBtn(num >= 5 && num <= 500 ? num : null);
    }

    function updatePayBtn(val) {
        const btn = document.getElementById('pay-btn');
        if (val && val >= 5) {
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.style.cursor = 'pointer';
            btn.innerHTML = '<i class="fa-brands fa-stripe"></i> Pay $' + parseFloat(val).toFixed(2) + ' with card';
        } else {
            btn.disabled = true;
            btn.style.opacity = '0.4';
            btn.style.cursor = 'not-allowed';
            btn.innerHTML = '<i class="fa-brands fa-stripe"></i> Pay with card';
        }
    }
</script>
@endpush
