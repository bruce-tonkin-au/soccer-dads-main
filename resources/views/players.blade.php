@extends('layouts.app')

@section('title', 'Players — Soccer Dads')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<style>
    #players-table_wrapper .dataTables_length select,
    #players-table_wrapper .dataTables_filter input {
        border: 1px solid #e8e8e8;
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 14px;
        color: #262c39;
        outline: none;
    }
    #players-table_wrapper .dataTables_filter input:focus {
        border-color: #458bc8;
    }
    #players-table_wrapper .dataTables_length,
    #players-table_wrapper .dataTables_filter,
    #players-table_wrapper .dataTables_info,
    #players-table_wrapper .dataTables_paginate {
        font-size: 13px;
        color: #888;
        margin-bottom: 1rem;
    }
    #players-table_wrapper .dataTables_paginate .paginate_button {
        border-radius: 6px !important;
        padding: 4px 10px !important;
        font-size: 13px !important;
    }
    #players-table_wrapper .dataTables_paginate .paginate_button.current {
        background: #262c39 !important;
        color: #fff !important;
        border-color: #262c39 !important;
    }
    #players-table_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f4f4f4 !important;
        color: #262c39 !important;
        border-color: #e8e8e8 !important;
    }
    #players-table thead th {
        background: #f8f8f8;
        color: #262c39;
        font-size: 13px;
        font-weight: 600;
        padding: 12px 16px;
        border-bottom: 1px solid #e8e8e8;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    #players-table tbody td {
        padding: 14px 16px;
        font-size: 14px;
        color: #262c39;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: middle;
    }
    #players-table tbody tr:hover td {
        background: #f8f8f8;
    }
    #players-table tbody tr:last-child td {
        border-bottom: none;
    }


</style>
@endpush

@section('content')

<div style="background:#262c39; padding:4rem 2rem 3rem;">
    <div class="container">
        <h1 style="font-family:'GetShow'; font-weight:normal; font-size:72px; color:#fff; line-height:1; margin:0;">Players</h1>
    </div>
</div>

<div style="padding:3rem 2rem 4rem;">
    <div class="container">
        <div style="background:#fff; border:1px solid #e8e8e8; border-radius:16px; overflow:hidden; padding:1.5rem;">
            <table id="players-table" style="width:100%;">
                <thead>
                    <tr>
                        <th>Player</th>
                        <th>Games</th>
                        <th>Goals</th>
                        <th>Assists</th>
                        <th>Awards</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($players as $player)
                    <tr style="cursor:pointer;" onclick="window.location='/players/{{ $player->memberSlug }}'">
                        <td style="font-weight:600;">{{ $player->memberNameFirst }} {{ $player->memberNameLast }}</td>
                        <td style="color:#888;">{{ $player->games }}</td>
                        <td style="color:#888;">
                            @if($player->goals > 0)
                            <i class="fa-solid fa-futbol" style="color:#e68a46; margin-right:4px;"></i>{{ $player->goals }}
                            @else
                            —
                            @endif
                        </td>
                        <td style="color:#888;">
                            @if($player->assists > 0)
                            <i class="fa-solid fa-people-arrows" style="color:#458bc8; margin-right:4px;"></i>{{ $player->assists }}
                            @else
                            —
                            @endif
                        </td>
                        <td>
                            @if(!empty($player->awards))
                                @foreach($player->awards as $award)
                                @php
                                    $emoji    = $award['position'] === 1 ? '🏆' : ($award['position'] === 2 ? '🥈' : '🥉');
                                    $posLabel = $award['position'] === 1 ? '1st' : ($award['position'] === 2 ? '2nd' : '3rd');
                                @endphp
                                <span style="font-size:16px; margin-right:4px; cursor:default;" title="{{ $posLabel }} — {{ $award['season'] }}">{{ $emoji }}</span>
                                @endforeach
                            @else
                            <span style="color:#ccc;">—</span>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            <i class="fa-solid fa-chevron-right" style="color:#aaa; font-size:12px;"></i>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#players-table').DataTable({
            pageLength: 10,
            stateSave: true,
            order: [[0, 'asc']],
            columnDefs: [
                { type: 'num', targets: [1, 2, 3] },
                { orderable: false, targets: [4, 5] }
            ],
            language: {
                search: 'Search:',
                lengthMenu: 'Show _MENU_ players',
                info: 'Showing _START_ to _END_ of _TOTAL_ players',
            }
        });
    });
</script>
@endpush
