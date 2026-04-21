<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $stats = [
            'seasons' => DB::table('seasons')->count(),
            'games'   => DB::table('scoring')->where('scoringActive', 1)->count(),
            'goals'   => DB::table('scoring-actions')->where('actionGoal', 1)->where('actionActive', 1)->count(),
            'players' => DB::table('members')->where('memberActive', 1)->count(),
        ];

        $nextGame = DB::table('games as g')
            ->join('seasons as s', 'g.gameSeason', '=', 's.seasonKey')
            ->whereRaw("STR_TO_DATE(g.gameDate, '%d/%m/%Y') >= CURDATE()")
            ->orderByRaw("STR_TO_DATE(g.gameDate, '%d/%m/%Y') ASC")
            ->select('g.*', 's.seasonName')
            ->first();

        return view('home', compact('stats', 'nextGame'));
    }
}