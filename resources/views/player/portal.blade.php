@extends('layouts.app')
@section('title', 'My Portal — Soccer Dads')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<style>
    #transactions-table thead th {
        background: #f8f8f8;
        color: #262c39;
        font-size: 13px;
        font-weight: 600;
        padding: 12px 16px;
        border-bottom: 1px solid #e8e8e8;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    #transactions-table tbody td {
        padding: 12px 16px;
        font-size: 14px;
        color: #262c39;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: middle;
    }
    #transactions-table tbody tr:last-child td { border-bottom: none; }
    #transactions-table tbody tr:hover td { background: #f8f8f8; }
</style>
@endpush
@section('content')

<div style="background:#262c39; padding:3rem 2rem;">
    <div class="container">
        <p style="font-size:13px; color:rgba(255,255,255,0.5); margin-bottom:4px;">Welcome back</p>
        <h1 style="font-family:'GetShow'; font-weight:normal; font-size:56px; color:#fff;">{{ $player->memberNameFirst }}!</h1>
    </div>
</div>

<div style="padding:3rem 2rem;">
    <div class="container">

        {{-- Next game & registration --}}
        @if($nextGame)
        <div style="background:#fff; border:1px solid #e8e8e8; border-radius:16px; padding:1.5rem; margin-bottom:2rem;">
            <h2 style="font-size:16px; font-weight:600; color:#262c39; margin-bottom:1rem;">Next game</h2>
            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
                <div>
                    <div style="font-size:20px; font-weight:600; color:#262c39;">
                        {{ \Carbon\Carbon::parse($nextGame->gameDate)->format('l j F Y') }}
                    </div>
                    <div style="font-size:13px; color:#888; margin-top:2px;">Round {{ $nextGame->gameRound }} · {{ $nextGame->seasonName }}</div>
                </div>
                <div style="display:flex; gap:8px;">
                    <form method="POST" action="/reg/{{ $player->memberCode }}">
                        @csrf
                        <button type="submit" name="status" value="1" style="padding:10px 20px; border-radius:8px; border:2px solid {{ ($registration?->registrationStatus == 1) ? '#7bba56' : '#e8e8e8' }}; background:{{ ($registration?->registrationStatus == 1) ? '#f0fdf4' : '#fff' }}; cursor:pointer; font-size:14px; font-weight:600; color:#262c39;">
                            <i class="fa-solid fa-circle-check" style="color:#7bba56;"></i> I'm in
                        </button>
                    </form>
                    <form method="POST" action="/reg/{{ $player->memberCode }}">
                        @csrf
                        <button type="submit" name="status" value="2" style="padding:10px 20px; border-radius:8px; border:2px solid {{ ($registration?->registrationStatus == 2) ? '#e24b4a' : '#e8e8e8' }}; background:{{ ($registration?->registrationStatus == 2) ? '#fff3f3' : '#fff' }}; cursor:pointer; font-size:14px; font-weight:600; color:#262c39;">
                            <i class="fa-solid fa-circle-xmark" style="color:#e24b4a;"></i> Can't make it
                        </button>
                    </form>
                </div>
            </div>

            {{-- Child registration --}}
            @if($child && $registration?->registrationStatus == 1)
            <div style="margin-top:1rem; padding-top:1rem; border-top:1px solid #f0f0f0;">
                <p style="font-size:14px; font-weight:600; color:#262c39; margin-bottom:0.75rem;">
                    Is {{ $child->memberNameFirst }} coming?
                </p>
                <div style="display:flex; gap:8px;">
                    <form method="POST" action="/reg/{{ $player->memberCode }}">
                        @csrf
                        <input type="hidden" name="childID" value="{{ $child->memberID }}">
                        <button type="submit" name="childStatus" value="1"
                            style="padding:10px 20px; border-radius:8px; border:2px solid {{ ($childRegistration?->registrationStatus == 1) ? '#7bba56' : '#e8e8e8' }}; background:{{ ($childRegistration?->registrationStatus == 1) ? '#f0fdf4' : '#fff' }}; cursor:pointer; font-size:14px; font-weight:600; color:#262c39;">
                            <i class="fa-solid fa-circle-check" style="color:#7bba56;"></i> Yes!
                        </button>
                    </form>
                    <form method="POST" action="/reg/{{ $player->memberCode }}">
                        @csrf
                        <input type="hidden" name="childID" value="{{ $child->memberID }}">
                        <button type="submit" name="childStatus" value="2"
                            style="padding:10px 20px; border-radius:8px; border:2px solid {{ ($childRegistration?->registrationStatus == 2) ? '#e24b4a' : '#e8e8e8' }}; background:{{ ($childRegistration?->registrationStatus == 2) ? '#fff3f3' : '#fff' }}; cursor:pointer; font-size:14px; font-weight:600; color:#262c39;">
                            <i class="fa-solid fa-circle-xmark" style="color:#e24b4a;"></i> Not this time
                        </button>
                    </form>
                </div>
            </div>
            @endif

        </div>
        @endif

        {{-- Account balance --}}
        <div style="background:#fff; border:1px solid #e8e8e8; border-radius:16px; padding:1.5rem; margin-bottom:2rem; display:flex; align-items:center; justify-content:space-between;">
            <div>
                <div style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:4px;">Account balance</div>
                <div style="font-size:32px; font-weight:700; color:{{ $balance < 0 ? '#e24b4a' : '#262c39' }};">
                    ${{ number_format(abs($balance), 2) }}{{ $balance < 0 ? ' owing' : '' }}
                </div>
            </div>
            <a href="/portal/account" style="background:#262c39; color:#fff; padding:12px 24px; border-radius:8px; text-decoration:none; font-size:14px; font-weight:600;">
                <i class="fa-solid fa-credit-card"></i> Top up
            </a>
        </div>

        {{-- Quick links --}}
        <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:12px;">
            <a href="/portal/profile" style="text-decoration:none;">
                <div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; padding:1.25rem; display:flex; align-items:center; gap:1rem;" onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,0.06)'" onmouseout="this.style.boxShadow='none'">
                    <i class="fa-solid fa-user" style="color:#7bba56; font-size:20px;"></i>
                    <div>
                        <div style="font-size:15px; font-weight:600; color:#262c39;">My profile</div>
                        <div style="font-size:12px; color:#888;">Update your details</div>
                    </div>
                </div>
            </a>
            <a href="/portal/account" style="text-decoration:none;">
                <div style="background:#fff; border:1px solid #e8e8e8; border-radius:12px; padding:1.25rem; display:flex; align-items:center; gap:1rem;" onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,0.06)'" onmouseout="this.style.boxShadow='none'">
                    <i class="fa-solid fa-wallet" style="color:#e68a46; font-size:20px;"></i>
                    <div>
                        <div style="font-size:15px; font-weight:600; color:#262c39;">Account & payments</div>
                        <div style="font-size:12px; color:#888;">Balance and top up</div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Transactions --}}
        <div style="margin-top:2rem;">
            <h2 style="font-size:18px; font-weight:600; color:#262c39; margin-bottom:1rem;">Account transactions</h2>
            <div style="background:#fff; border:1px solid #e8e8e8; border-radius:16px; overflow:hidden; padding:1.5rem;">
                <table id="transactions-table" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $t)
                        <tr>
                            <td data-order="{{ strtotime($t->accountCreated) }}">{{ \Carbon\Carbon::parse($t->accountCreated)->format('j M Y') }}</td>
                            <td>
                                @if($t->seasonName && $t->accountValue < 0)
                                    Game fee —
                                    <a href="/seasons/{{ $t->seasonLink }}/{{ $t->gameRound }}" style="color:#262c39;">
                                        {{ $t->seasonName }} Round {{ $t->gameRound }}
                                    </a>
                                @elseif($t->accountComment)
                                    {{ $t->accountComment }}
                                @else
                                    Top up
                                @endif
                            </td>
                            <td style="font-weight:600; color:{{ $t->accountValue < 0 ? '#e24b4a' : '#7bba56' }}; text-align:right;">
                                {{ $t->accountValue < 0 ? '-' : '+' }}${{ number_format(abs($t->accountValue), 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#transactions-table').DataTable({
            pageLength: 10,
            order: [[0, 'desc']],
            stateSave: true,
            columnDefs: [
                { type: 'num', targets: [0] }
            ],
            language: {
                search: 'Search:',
                lengthMenu: 'Show _MENU_ transactions',
                info: 'Showing _START_ to _END_ of _TOTAL_ transactions',
            }
        });
    });
</script>
@endpush
