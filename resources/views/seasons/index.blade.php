@extends('layouts.app')

@section('title', 'Seasons — Soccer Dads')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<style>
    #seasons-table_wrapper .dataTables_length select,
    #seasons-table_wrapper .dataTables_filter input {
        border: 1px solid #e8e8e8;
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 14px;
        color: #262c39;
        outline: none;
    }
    #seasons-table_wrapper .dataTables_filter input:focus {
        border-color: #458bc8;
    }
    #seasons-table_wrapper .dataTables_length,
    #seasons-table_wrapper .dataTables_filter,
    #seasons-table_wrapper .dataTables_info,
    #seasons-table_wrapper .dataTables_paginate {
        font-size: 13px;
        color: #888;
        margin-bottom: 1rem;
    }
    #seasons-table_wrapper .dataTables_paginate .paginate_button {
        border-radius: 6px !important;
        padding: 4px 10px !important;
        font-size: 13px !important;
    }
    #seasons-table_wrapper .dataTables_paginate .paginate_button.current {
        background: #262c39 !important;
        color: #fff !important;
        border-color: #262c39 !important;
    }
    #seasons-table_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f4f4f4 !important;
        color: #262c39 !important;
        border-color: #e8e8e8 !important;
    }
    #seasons-table thead th {
        background: #f8f8f8;
        color: #262c39;
        font-size: 13px;
        font-weight: 600;
        padding: 12px 16px;
        border-bottom: 1px solid #e8e8e8;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    #seasons-table tbody td {
        padding: 14px 16px;
        font-size: 14px;
        color: #262c39;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: middle;
    }
    #seasons-table tbody tr:hover td {
        background: #f8f8f8;
    }
    #seasons-table tbody tr:last-child td {
        border-bottom: none;
    }
</style>
@endpush

@section('content')

<div style="padding:4rem 2rem;">
    <div class="container">
        <h1 style="font-family:'GetShow'; font-weight:normal; font-size:72px; color:#262c39; margin-bottom:0.5rem;">Seasons</h1>

        <div style="background:#fff; border:1px solid #e8e8e8; border-radius:16px; overflow:hidden; padding:1.5rem;">
            <table id="seasons-table" style="width:100%;">
                <thead>
                    <tr>
                        <th>Season</th>
                        <th>Sessions</th>
                        <th>Goals</th>
                        <th>Winner</th>
<th>2nd</th>
<th>3rd</th>
<th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($seasons as $season)
                    <tr style="cursor:pointer;" onclick="window.location='/seasons/{{ $season->seasonLink }}'">
                        <td>
                            <span style="font-weight:600; color:#262c39;">{{ $season->seasonName }}</span>
                        </td>
                        <td style="color:#888;">{{ $season->sessions }}</td>
                        <td style="color:#888;">
                            @if($season->goals > 0)
                            <i class="fa-solid fa-futbol" style="color:#e68a46; margin-right:4px;"></i>{{ $season->goals }}
                            @else
                            —
                            @endif
                        </td>
                        <td>
    @if($season->winner)
    <i class="fa-solid fa-trophy" style="color:#f0c040; margin-right:6px;"></i>{{ $season->winner }}
    @else
    <span style="color:#ccc;">—</span>
    @endif
</td>
<td>
    @if($season->second)
    <i class="fa-solid fa-trophy" style="color:#aaa; margin-right:6px;"></i>{{ $season->second }}
    @else
    <span style="color:#ccc;">—</span>
    @endif
</td>
<td>
    @if($season->third)
    <i class="fa-solid fa-trophy" style="color:#cd7f32; margin-right:6px;"></i>{{ $season->third }}
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
        $('#seasons-table').DataTable({
            pageLength: 25,
            order: [],
            columnDefs: [
                { orderable: false, targets: 6 }
            ],
            language: {
                search: 'Search:',
                lengthMenu: 'Show _MENU_ seasons',
                info: 'Showing _START_ to _END_ of _TOTAL_ seasons',
            }
        });
    });
</script