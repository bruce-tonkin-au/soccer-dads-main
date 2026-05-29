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

        {{-- Emergency contacts --}}
        <div style="margin-top:2rem;">
            <h2 style="font-size:18px; font-weight:600; color:#262c39; margin-bottom:1rem;">Emergency contacts</h2>

            @if(session('contact_success'))
            <div style="background:#f0fdf4; border:1px solid #7bba56; border-radius:8px; padding:12px 16px; margin-bottom:1rem; font-size:14px; color:#262c39;">
                <i class="fa-solid fa-circle-check" style="color:#7bba56;"></i> {{ session('contact_success') }}
            </div>
            @endif
            @if(session('contact_error'))
            <div style="background:#fff3f3; border:1px solid #e24b4a; border-radius:8px; padding:12px 16px; margin-bottom:1rem; font-size:14px; color:#262c39;">
                <i class="fa-solid fa-circle-exclamation" style="color:#e24b4a;"></i> {{ session('contact_error') }}
            </div>
            @endif

            <div style="background:#fff; border:1px solid #e8e8e8; border-radius:16px; padding:1.5rem;">

                @if($emergencyContacts->isEmpty())
                <p style="font-size:14px; color:#888; margin-bottom:1rem;">You haven't added any emergency contacts yet. Add at least two so we can reach someone on your behalf.</p>
                @else
                    @php $canDelete = $emergencyContacts->count() > 2; @endphp
                    @foreach($emergencyContacts as $c)
                    <div id="contact-{{ $c->contactID }}" style="border:1px solid #e8e8e8; border-radius:12px; padding:1rem; margin-bottom:12px; background:{{ $c->contactPrimary ? '#f0fdf4' : '#fafafa' }};">
                        {{-- View mode --}}
                        <div id="contact-view-{{ $c->contactID }}">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:1rem;">
                                <div>
                                    <div style="font-size:15px; font-weight:600; color:#262c39;">
                                        {{ $c->contactName }}
                                        @if($c->contactPrimary)
                                        <span style="background:#7bba56; color:#fff; padding:2px 8px; border-radius:20px; font-size:11px; font-weight:600; margin-left:6px; vertical-align:middle;">
                                            <i class="fa-solid fa-star" style="font-size:10px;"></i> Primary
                                        </span>
                                        @endif
                                    </div>
                                    @if($c->contactRelationship)
                                    <div style="font-size:13px; color:#888; margin-top:2px;">{{ $c->contactRelationship }}</div>
                                    @endif
                                    <div style="font-size:14px; color:#262c39; margin-top:8px;">
                                        <i class="fa-solid fa-phone" style="color:#888; font-size:12px; margin-right:4px;"></i> {{ $c->contactPhone }}
                                    </div>
                                    @if($c->contactEmail)
                                    <div style="font-size:14px; color:#262c39; margin-top:4px;">
                                        <i class="fa-solid fa-envelope" style="color:#888; font-size:12px; margin-right:4px;"></i> {{ $c->contactEmail }}
                                    </div>
                                    @endif
                                </div>
                                <div style="display:flex; flex-direction:column; gap:6px; align-items:flex-end;">
                                    <button type="button" onclick="toggleContact({{ $c->contactID }})" style="background:none; border:1px solid #e8e8e8; color:#262c39; border-radius:6px; padding:6px 10px; font-size:12px; font-weight:600; cursor:pointer;">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </button>
                                    @if(!$c->contactPrimary)
                                    <form method="POST" action="/portal/contacts/{{ $c->contactID }}/primary" style="margin:0;">
                                        @csrf
                                        <button type="submit" style="background:none; border:1px solid #e8e8e8; color:#262c39; border-radius:6px; padding:6px 10px; font-size:12px; font-weight:600; cursor:pointer;">
                                            <i class="fa-solid fa-star"></i> Make primary
                                        </button>
                                    </form>
                                    @endif
                                    @if($canDelete)
                                    <form method="POST" action="/portal/contacts/{{ $c->contactID }}/delete" style="margin:0;" onsubmit="return confirm('Delete this emergency contact?');">
                                        @csrf
                                        <button type="submit" style="background:none; border:1px solid #e8e8e8; color:#e24b4a; border-radius:6px; padding:6px 10px; font-size:12px; font-weight:600; cursor:pointer;">
                                            <i class="fa-solid fa-trash"></i> Delete
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                        {{-- Edit mode (hidden by default) --}}
                        <form id="contact-edit-{{ $c->contactID }}" method="POST" action="/portal/contacts/{{ $c->contactID }}" style="display:none;">
                            @csrf
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
                                <input type="text" name="contactName" value="{{ $c->contactName }}" placeholder="Name" required style="border:1px solid #e8e8e8; border-radius:8px; padding:10px 12px; font-size:14px; color:#262c39; outline:none;">
                                <input type="text" name="contactRelationship" value="{{ $c->contactRelationship }}" placeholder="Relationship" style="border:1px solid #e8e8e8; border-radius:8px; padding:10px 12px; font-size:14px; color:#262c39; outline:none;">
                            </div>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
                                <input type="tel" name="contactPhone" value="{{ $c->contactPhone }}" placeholder="Phone" required style="border:1px solid #e8e8e8; border-radius:8px; padding:10px 12px; font-size:14px; color:#262c39; outline:none;">
                                <input type="email" name="contactEmail" value="{{ $c->contactEmail }}" placeholder="Email (optional)" style="border:1px solid #e8e8e8; border-radius:8px; padding:10px 12px; font-size:14px; color:#262c39; outline:none;">
                            </div>
                            <div style="display:flex; gap:8px;">
                                <button type="submit" style="background:#262c39; color:#fff; border:none; border-radius:8px; padding:10px 16px; font-size:13px; font-weight:600; cursor:pointer;">Save changes</button>
                                <button type="button" onclick="toggleContact({{ $c->contactID }})" style="background:none; border:1px solid #e8e8e8; color:#262c39; border-radius:8px; padding:10px 16px; font-size:13px; font-weight:600; cursor:pointer;">Cancel</button>
                            </div>
                        </form>
                    </div>
                    @endforeach

                    @if($emergencyContacts->count() <= 2)
                    <p style="font-size:12px; color:#aaa; margin-bottom:12px;">
                        <i class="fa-solid fa-circle-info"></i> You must keep at least two emergency contacts on file.
                    </p>
                    @endif
                @endif

                {{-- Add new contact --}}
                <div style="border-top:1px solid #f0f0f0; padding-top:1rem;">
                    <button type="button" onclick="toggleAddContact()" id="addContactBtn" style="background:none; border:1px dashed #ccc; color:#666; border-radius:8px; padding:10px 14px; font-size:14px; cursor:pointer; width:100%;">
                        <i class="fa-solid fa-plus"></i> Add another contact
                    </button>
                    <form id="addContactForm" method="POST" action="/portal/contacts" style="display:none; margin-top:8px;">
                        @csrf
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
                            <input type="text" name="contactName" placeholder="Name" required style="border:1px solid #e8e8e8; border-radius:8px; padding:10px 12px; font-size:14px; color:#262c39; outline:none;">
                            <input type="text" name="contactRelationship" placeholder="Relationship (e.g. partner)" style="border:1px solid #e8e8e8; border-radius:8px; padding:10px 12px; font-size:14px; color:#262c39; outline:none;">
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
                            <input type="tel" name="contactPhone" placeholder="Phone" required style="border:1px solid #e8e8e8; border-radius:8px; padding:10px 12px; font-size:14px; color:#262c39; outline:none;">
                            <input type="email" name="contactEmail" placeholder="Email (optional)" style="border:1px solid #e8e8e8; border-radius:8px; padding:10px 12px; font-size:14px; color:#262c39; outline:none;">
                        </div>
                        <label style="display:inline-flex; align-items:center; gap:6px; font-size:13px; color:#262c39; margin-bottom:10px; cursor:pointer;">
                            <input type="checkbox" name="contactPrimary" value="1"> Make this my primary contact
                        </label>
                        <div style="display:flex; gap:8px;">
                            <button type="submit" style="background:#262c39; color:#fff; border:none; border-radius:8px; padding:10px 16px; font-size:13px; font-weight:600; cursor:pointer;">Add contact</button>
                            <button type="button" onclick="toggleAddContact()" style="background:none; border:1px solid #e8e8e8; color:#262c39; border-radius:8px; padding:10px 16px; font-size:13px; font-weight:600; cursor:pointer;">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
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
    function toggleContact(id) {
        const view = document.getElementById('contact-view-' + id);
        const edit = document.getElementById('contact-edit-' + id);
        if (!view || !edit) return;
        const showEdit = edit.style.display === 'none';
        view.style.display = showEdit ? 'none' : '';
        edit.style.display = showEdit ? '' : 'none';
    }
    function toggleAddContact() {
        const form = document.getElementById('addContactForm');
        const btn  = document.getElementById('addContactBtn');
        const showForm = form.style.display === 'none';
        form.style.display = showForm ? '' : 'none';
        btn.style.display  = showForm ? 'none' : '';
    }

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
