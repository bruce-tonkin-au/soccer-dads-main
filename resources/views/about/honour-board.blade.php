@extends('layouts.app')
@section('title', 'Honour Board — Soccer Dads')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<style>
    #honour-table_wrapper .dataTables_length select,
    #honour-table_wrapper .dataTables_filter input {
        border: 1px solid #e8e8e8;
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 14px;
        color: #262c39;
        outline: none;
    }
    #honour-table_wrapper .dataTables_filter input:focus {
        border-color: #458bc8;
    }
    #honour-table_wrapper .dataTables_length,
    #honour-table_wrapper .dataTables_filter,
    #honour-table_wrapper .dataTables_info,
    #honour-table_wrapper .dataTables_paginate {
        font-size: 13px;
        color: #888;
        margin-bottom: 1rem;
    }
    #honour-table_wrapper .dataTables_paginate .paginate_button {
        border-radius: 6px !important;
        padding: 4px 10px !important;
        font-size: 13px !important;
    }
    #honour-table_wrapper .dataTables_paginate .paginate_button.current {
        background: #262c39 !important;
        color: #fff !important;
        border-color: #262c39 !important;
    }
    #honour-table_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f4f4f4 !important;
        color: #262c39 !important;
        border-color: #e8e8e8 !important;
    }
    #honour-table thead th {
        background: #f8f8f8;
        color: #262c39;
        font-size: 13px;
        font-weight: 600;
        padding: 12px 16px;
        border-bottom: 1px solid #e8e8e8;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    #honour-table tbody td {
        padding: 14px 16px;
        font-size: 14px;
        color: #262c39;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: middle;
    }
    #honour-table tbody tr:hover td {
        background: #f8f8f8;
    }
    #honour-table tbody tr:last-child td {
        border-bottom: none;
    }
</style>
@endpush

@section('content')

@php
    // Committee positions, hardcoded. Keyed by year; a missing role is left blank.
    $chairs = [
        2011 => 'Tony Goodrich', 2012 => 'Tony Goodrich', 2013 => 'Tony Goodrich',
        2014 => 'Tony Goodrich', 2015 => 'Tony Goodrich', 2016 => 'Tony Goodrich',
        2017 => 'Tony Goodrich', 2018 => 'Neil Williams', 2019 => 'Neil Williams',
        2020 => 'Neil Williams', 2021 => 'Neil Williams', 2022 => 'Neil Williams',
        2023 => 'Neil Williams', 2024 => 'Neil Williams', 2025 => 'Neil Williams',
        2026 => 'Alex Aloisi',
    ];
    $viceChairs = [ 2026 => 'Neil Archer' ];
    $mediaCoordinators = [ 2026 => 'Chris Williams' ];

    $committee = [];
    for ($year = 2026; $year >= 2011; $year--) {
        $committee[] = [
            'year'      => $year,
            'chair'     => $chairs[$year] ?? null,
            'vice'      => $viceChairs[$year] ?? null,
            'treasurer' => 'Bruce Tonkin',
            'media'     => $mediaCoordinators[$year] ?? null,
        ];
    }
@endphp

<div style="background:#262c39; padding:4rem 2rem;">
    <div class="container">
        <a href="/about" style="font-size:13px; color:rgba(255,255,255,0.5); text-decoration:none; display:inline-flex; align-items:center; gap:6px; margin-bottom:1.5rem;">
            <i class="fa-solid fa-chevron-left"></i> About
        </a>
        <h1 style="font-family:'GetShow'; font-weight:normal; font-size:72px; color:#fff; margin-bottom:0.5rem;">Honour Board</h1>
        <p style="font-size:16px; color:rgba(255,255,255,0.6);">Committee members since 2011.</p>
    </div>
</div>

<div style="padding:3rem 2rem 4rem;">
    <div class="container">
        <div style="background:#fff; border:1px solid #e8e8e8; border-radius:16px; overflow:hidden; padding:1.5rem;">
            <table id="honour-table" style="width:100%;">
                <thead>
                    <tr>
                        <th>Year</th>
                        <th>Chair</th>
                        <th>Vice Chair</th>
                        <th>Treasurer</th>
                        <th>Media Coordinator</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($committee as $row)
                    <tr>
                        <td><span style="font-weight:600; color:#262c39;">{{ $row['year'] }}</span></td>
                        <td>@if($row['chair']){{ $row['chair'] }}@else<span style="color:#ccc;">—</span>@endif</td>
                        <td>@if($row['vice']){{ $row['vice'] }}@else<span style="color:#ccc;">—</span>@endif</td>
                        <td>@if($row['treasurer']){{ $row['treasurer'] }}@else<span style="color:#ccc;">—</span>@endif</td>
                        <td>@if($row['media']){{ $row['media'] }}@else<span style="color:#ccc;">—</span>@endif</td>
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
        $('#honour-table').DataTable({
            pageLength: 25,
            order: [],
            language: {
                search: 'Search:',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            }
        });
    });
</script>
@endpush
