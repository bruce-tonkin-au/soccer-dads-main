@extends('layouts.app')
@section('title', 'Playing History — Soccer Dads')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<style>
    #history-table thead th {
        background: #f8f8f8;
        color: #262c39;
        font-size: 13px;
        font-weight: 600;
        padding: 12px 16px;
        border-bottom: 1px solid #e8e8e8;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    #history-table tbody td {
        padding: 12px 16px;
        font-size: 14px;
        color: #262c39;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: middle;
    }
    #history-table tbody tr:last-child td { border-bottom: none; }
    #history-table tbody tr:hover td { background: #f8f8f8; }
</style>
@endpush

@section('content')

<div style="background:#262c39; padding:3rem 2rem;">
    <div class="container">
        <a href="/portal" style="font-size:13px; color:rgba(255,255,255,0.5); text-decoration:none; display:inline-flex; align-items:center; gap:6px; margin-bottom:1.5rem;">
            <i class="fa-solid fa-chevron-left"></i> Portal
        </a>
        <h1 style="font-family:'GetShow'; font-weight:normal; font-size:56px; color:#fff;">Playing History</h1>
    </div>
</div>

<div style="padding:3rem 2rem;">
    <div class="container">
        <div style="background:#fff; border:1px solid #e8e8e8; border-radius:16px; overflow:hidden; padding:1.5rem;">
            <table id="history-table" style="width:100%;">
                <thead>
                    <tr>
                        <th>Season</th>
                        <th>Round</th>
                        <th>Date</th>
                        <th>Assisted by</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($actions as $action)
                    <tr>
                        <td>
                            <a href="/seasons/{{ $action->seasonLink }}/{{ $action->gameRound }}" style="color:#262c39; text-decoration:none;">
                                {{ $action->seasonName }}
                            </a>
                        </td>
                        <td style="color:#888;">{{ $action->gameRound }}</td>
                        <td data-order="{{ strtotime($action->gameDate) }}">{{ \Carbon\Carbon::parse($action->gameDate)->format('j M Y') }}</td>
                        <td style="color:#888;">{{ $action->assisterFirst ? $action->assisterFirst . ' ' . $action->assisterLast : '—' }}</td>
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
        $('#history-table').DataTable({
            pageLength: 25,
            order: [[2, 'desc']],
            stateSave: true,
            language: {
                search: 'Search:',
                lengthMenu: 'Show _MENU_ goals',
                info: 'Showing _START_ to _END_ of _TOTAL_ goals',
            }
        });
    });
</script>
@endpush
