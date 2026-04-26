<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Night Sheet — {{ $game->seasonName }} Round {{ $game->gameRound }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
            padding: 15mm;
        }

        .page {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        h1 { font-size: 18px; font-weight: 700; margin-bottom: 12px; }
        h2 { font-size: 18px; font-weight: 700; margin-bottom: 12px; }

        .details-box {
            border: 1px solid #000;
            margin-bottom: 16px;
        }
        .details-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .details-box td {
            padding: 5px 8px;
            border-bottom: 1px solid #ccc;
            font-size: 12px;
        }
        .details-box td:first-child {
            font-weight: 700;
            width: 80px;
            background: #f4f4f4;
        }
        .details-box tr:last-child td { border-bottom: none; }

        .players-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .players-table th {
            background: #262c39;
            color: #fff;
            padding: 5px 6px;
            text-align: left;
            font-size: 11px;
        }
        .players-table th.center,
        .players-table td.center { text-align: center; }
        .players-table td {
            padding: 5px 6px;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
        }
        .players-table tr:nth-child(even) td { background: #f9f9f9; }
        .players-table .blank td { background: #fff; border-bottom: 1px solid #ddd; }
        .players-table td.owing { color: #cc0000; font-weight: 700; }
        .players-table td.credit { color: #007700; }

        .print-btn {
            position: fixed;
            top: 10px;
            right: 10px;
            background: #262c39;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
        }

        @media print {
            .print-btn { display: none; }
            body { padding: 10mm; }
        }
    </style>
</head>
<body>

<button class="print-btn" onclick="window.print()">
    <i class="fa-solid fa-print"></i> Print
</button>

<div class="page">

    {{-- LEFT: Players --}}
    <div>
        <h1>Players</h1>
        <div style="border:1px solid #000; margin-bottom:8px;">
        <table class="players-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th class="center">Games</th>
                    <th class="center">Bibs %</th>
                    <th class="center">Acct</th>
                    <th class="center">Reg</th>
                </tr>
            </thead>
            <tbody>
                @foreach($players as $player)
                <tr>
                    <td>{{ strtoupper($player->memberNameLast) }}, {{ $player->memberNameFirst }}</td>
                    <td class="center">{{ $player->games }}</td>
                    <td class="center">{{ $player->bibPercent }}</td>
                    <td class="center {{ $player->balance < 0 ? 'owing' : ($player->balance > 0 ? 'credit' : '') }}">
                        {{ $player->balance != 0 ? ($player->balance > 0 ? '+' : '') . number_format($player->balance, 0) : '0' }}
                    </td>
                    <td class="center">✓</td>
                </tr>
                @endforeach

                @for($i = 0; $i < 6; $i++)
                <tr class="blank">
                    <td>&nbsp;</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                @endfor
            </tbody>
        </table>
        </div>
    </div>

    {{-- RIGHT: Details --}}
    <div>
        <h2>Details</h2>
        <div class="details-box" style="margin-bottom:16px;">
            <table>
                <tr>
                    <td>Code</td>
                    <td>{{ $game->gameCode }}</td>
                </tr>
                <tr>
                    <td>Date</td>
                    <td>{{ \Carbon\Carbon::parse($game->gameDate)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td>Season</td>
                    <td>{{ $game->seasonName }}</td>
                </tr>
                <tr>
                    <td>Round</td>
                    <td>{{ $game->gameRound }}</td>
                </tr>
                <tr>
                    <td>Bibs</td>
                    <td>&nbsp;</td>
                </tr>
            </table>
        </div>

        <h2>Notes</h2>
        <div style="border:1px solid #000; height:200px; padding:8px; font-size:11px; color:#aaa;">
            &nbsp;
        </div>
    </div>

</div>

</body>
</html>
