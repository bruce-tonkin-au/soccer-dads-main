<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $stats = [
            'seasons' => DB::table('seasons')->where('seasonVisible', 1)->count(),
            'sessions' => DB::table('games')->where('gameVisible', 1)->count(),
            'games'   => DB::table('scoring')->where('scoringActive', 1)->count(),
            'goals'   => DB::table('scoring-actions')->where('actionGoal', 1)->where('actionActive', 1)->count(),
            'players' => DB::table('members')->count(),
        ];

        $nextGame = DB::table('games as g')
            ->join('seasons as s', 'g.gameSeasonID', '=', 's.seasonID')
            ->whereRaw('g."gameDate" >= CURRENT_DATE')
            ->orderByRaw('g."gameDate" ASC')
            ->select('g.*', 's.seasonName', 's.seasonLink')
            ->first();

        return view('home', compact('stats', 'nextGame'));
    }
}