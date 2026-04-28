<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    // AUTH
    public function showLogin()
    {
        if (session('admin_authenticated')) return redirect('/admin');
        return view('admin.login');
    }

    public function login(Request $request)
    {
        if ($request->input('username') === env('ADMIN_USERNAME') &&
            $request->input('password') === env('ADMIN_PASSWORD')) {
            session(['admin_authenticated' => true]);
            return redirect('/admin');
        }
        return back()->with('error', 'Invalid credentials.');
    }

    public function logout()
    {
        session()->forget('admin_authenticated');
        return redirect('/admin/login');
    }

    // DASHBOARD
    public function dashboard()
    {
        $stats = [
            'players' => DB::table('members')->where('memberActive', 1)->count(),
            'seasons' => DB::table('seasons')->where('seasonVisible', 1)->count(),
            'games'   => DB::table('games')->where('gameVisible', 1)->count(),
            'goals'   => DB::table('scoring-actions')->where('actionGoal', 1)->where('actionActive', 1)->count(),
        ];

        $currentSeason = DB::table('seasons')
            ->where('seasonVisible', 1)
            ->orderBy('seasonID', 'desc')
            ->first();

        $nextGame = null;
        if ($currentSeason) {
            $nextGame = DB::table('games as g')
                ->join('seasons as s', 'g.gameSeasonID', '=', 's.seasonID')
                ->where('g.gameVisible', 1)
                ->where('g.gameSeasonID', $currentSeason->seasonID)
                ->whereNotExists(function ($q) {
                    $q->select(DB::raw(1))->from('scoring')
                      ->whereColumn('scoring.gameID', 'g.gameID')
                      ->whereNotNull('scoring.scoringEnded');
                })
                ->orderBy('g.gameID', 'asc')
                ->select('g.*', 's.seasonName')
                ->first();
        }

        $registrations = null;
        if ($nextGame) {
            $registrations = DB::table('game-registrations as r')
                ->join('members as m', 'r.memberID', '=', 'm.memberID')
                ->where('r.gameID', $nextGame->gameID)
                ->where('r.registrationStatus', 1)
                ->orderBy('m.memberNameLast')
                ->select('m.memberID', 'm.memberNameFirst', 'm.memberNameLast', 'm.memberSlug')
                ->get();
        }

        return view('admin.dashboard', compact('stats', 'nextGame', 'registrations'));
    }

    // PLAYERS
    public function players()
    {
        $players = DB::table('members')
            ->orderBy('memberNameLast')
            ->orderBy('memberNameFirst')
            ->get();

        $balances = DB::table('account')
            ->where('accountVisible', 1)
            ->select('memberID', DB::raw('SUM(accountValue) as balance'))
            ->groupBy('memberID')
            ->get()
            ->keyBy('memberID');

        $players = $players->map(function($player) use ($balances) {
            $player->balance = $balances[$player->memberID]->balance ?? 0;
            return $player;
        });

        return view('admin.players.index', compact('players'));
    }

    public function createPlayer()
    {
        return view('admin.players.create');
    }

    public function storePlayer(Request $request)
    {
        $firstName = trim($request->input('firstName'));
        $lastName  = trim($request->input('lastName'));

        do {
            $code = strtoupper(Str::random(3));
        } while (DB::table('members')->where('memberCode', $code)->exists());

        $baseSlug = Str::slug($firstName . ' ' . $lastName);
        $slug = $baseSlug;
        $counter = 2;
        while (DB::table('members')->where('memberSlug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        DB::table('members')->insert([
            'memberNameFirst'   => $firstName,
            'memberNameLast'    => $lastName,
            'memberEmail'       => $request->input('email'),
            'memberPhoneMobile' => $request->input('mobile'),
            'memberCode'        => $code,
            'memberSlug'        => $slug,
            'memberActive'      => 1,
            'memberParent'      => $request->input('parent') ?: null,
        ]);

        return redirect('/admin/players')->with('success', "Player {$firstName} {$lastName} created with code {$code}.");
    }

    public function editPlayer($memberID)
    {
        $player     = DB::table('members')->where('memberID', $memberID)->firstOrFail();
        $allPlayers = DB::table('members')->where('memberActive', 1)->orderBy('memberNameLast')->get();
        return view('admin.players.edit', compact('player', 'allPlayers'));
    }

    public function updatePlayer(Request $request, $memberID)
    {
        DB::table('members')->where('memberID', $memberID)->update([
            'memberNameFirst'   => $request->input('firstName'),
            'memberNameLast'    => $request->input('lastName'),
            'memberEmail'       => $request->input('email'),
            'memberPhoneMobile' => $request->input('mobile'),
            'memberActive'      => $request->input('active', 0),
            'memberParent'      => $request->input('parent') ?: null,
            'memberBirthday'    => $request->input('birthday') ?: null,
        ]);
        return redirect('/admin/players')->with('success', 'Player updated.');
    }

    // SEASONS
    public function seasons()
    {
        $seasons = DB::table('seasons')->orderBy('seasonID', 'desc')->get();
        return view('admin.seasons.index', compact('seasons'));
    }

    public function createSeason()
    {
        return view('admin.seasons.create');
    }

    public function storeSeason(Request $request)
    {
        DB::table('seasons')->insert([
            'seasonLink'    => $request->input('seasonLink'),
            'seasonName'    => $request->input('seasonName'),
            'seasonVisible' => $request->input('seasonVisible', 1),
        ]);
        return redirect('/admin/seasons')->with('success', 'Season created.');
    }

    public function editSeason($seasonID)
    {
        $season = DB::table('seasons')->where('seasonID', $seasonID)->firstOrFail();
        return view('admin.seasons.edit', compact('season'));
    }

    public function updateSeason(Request $request, $seasonID)
    {
        DB::table('seasons')->where('seasonID', $seasonID)->update([
            'seasonLink'    => $request->input('seasonLink'),
            'seasonName'    => $request->input('seasonName'),
            'seasonVisible' => $request->input('seasonVisible', 1),
        ]);
        return redirect('/admin/seasons')->with('success', 'Season updated.');
    }

    // GAMES
    public function games($seasonID)
    {
        $season = DB::table('seasons')->where('seasonID', $seasonID)->firstOrFail();
        $games  = DB::table('games')->where('gameSeasonID', $seasonID)->orderBy('gameRound')->get();
        return view('admin.games.index', compact('season', 'games'));
    }

    public function createGame($seasonID)
    {
        $season = DB::table('seasons')->where('seasonID', $seasonID)->firstOrFail();
        return view('admin.games.create', compact('season'));
    }

    public function storeGame(Request $request, $seasonID)
    {
        DB::table('games')->insert([
            'gameSeasonID' => $seasonID,
            'gameRound'    => $request->input('gameRound'),
            'gameDate'     => $request->input('gameDate'),
            'gameYouTube'  => $request->input('gameYouTube'),
            'gameVisible'  => $request->input('gameVisible', 1),
        ]);
        return redirect("/admin/seasons/{$seasonID}/games")->with('success', 'Game created.');
    }

    public function editGame($seasonID, $gameID)
    {
        $season = DB::table('seasons')->where('seasonID', $seasonID)->firstOrFail();
        $game   = DB::table('games')->where('gameID', $gameID)->firstOrFail();
        return view('admin.games.edit', compact('season', 'game'));
    }

    public function updateGame(Request $request, $seasonID, $gameID)
    {
        DB::table('games')->where('gameID', $gameID)->update([
            'gameRound'        => $request->input('gameRound'),
            'gameDate'         => $request->input('gameDate'),
            'gameYouTube'      => $request->input('gameYouTube'),
            'gameYouTubeStart' => $request->input('gameYouTubeStart') ?: null,
            'gameVisible'      => $request->input('gameVisible', 1),
        ]);
        return redirect("/admin/seasons/{$seasonID}/games")->with('success', 'Game updated.');
    }

    // TEAMS
    public function teams($gameID)
    {
        $game = DB::table('games as g')
            ->join('seasons as s', 'g.gameSeasonID', '=', 's.seasonID')
            ->where('g.gameID', $gameID)
            ->select('g.*', 's.seasonName', 's.seasonID')
            ->firstOrFail();

        $registered = DB::table('game-registrations as r')
            ->join('members as m', 'r.memberID', '=', 'm.memberID')
            ->where('r.gameID', $gameID)
            ->where('r.registrationStatus', 1)
            ->orderBy('m.memberNameLast')
            ->select('m.*', 'r.registrationBench')
            ->get();

        $teamNames  = [1 => 'Orange', 2 => 'Green', 3 => 'Blue'];
        $teamColors = [1 => '#e68a46', 2 => '#7bba56', 3 => '#458bc8'];

        $existingAssignments = DB::table('results')
            ->where('resultGameID', $game->gameID)
            ->where('resultActive', 1)
            ->get()
            ->keyBy('resultMemberID');

        $registered = $registered->map(function ($player) use ($existingAssignments) {
            $goals = DB::table('scoring-actions')
                ->where('memberID', $player->memberID)
                ->where('actionGoal', 1)
                ->where('actionActive', 1)
                ->count();
            $games = DB::table('results')
                ->where('resultMemberID', $player->memberID)
                ->where('resultActive', 1)
                ->distinct('resultGameID')
                ->count('resultGameID');
            $player->rating = $games > 0 ? min(99, round(($goals / $games) * 25) + round($games / 5)) : null;
            $player->bench  = (bool) ($player->registrationBench ?? 0);

            $assignment     = $existingAssignments[$player->memberID] ?? null;
            $player->teamID = $assignment ? $assignment->resultTeamID : null;
            return $player;
        })->sortByDesc('rating')->values();

        $rated = $registered->filter(fn($p) => $p->rating !== null);
        if ($rated->isNotEmpty()) {
            $midRating = (int) round(($rated->min('rating') + $rated->max('rating')) / 2);
        } else {
            $midRating = 50;
        }
        $registered = $registered->map(function ($player) use ($midRating) {
            if ($player->rating === null) {
                $player->rating = $midRating;
            }
            return $player;
        });

        $peerRatings = DB::table('player-ratings')
            ->whereIn('ratedMemberID', $registered->pluck('memberID'))
            ->select(
                'ratedMemberID',
                DB::raw('COUNT(*) as ratingCount'),
                DB::raw('AVG(ratingGoal) as avgGoal'),
                DB::raw('AVG(ratingPassing) as avgPassing'),
                DB::raw('AVG(ratingWork) as avgWork'),
                DB::raw('AVG(ratingDefending) as avgDefending'),
                DB::raw('AVG(ratingOverall) as avgOverall')
            )
            ->groupBy('ratedMemberID')
            ->get()
            ->keyBy('ratedMemberID');

        $roleIconMap = [
            'striker'   => 'fa-fire',
            'playmaker' => 'fa-wand-magic-sparkles',
            'workhorse' => 'fa-bolt',
            'defender'  => 'fa-shield-halved',
        ];

        $registered = $registered->map(function ($player) use ($peerRatings, $roleIconMap) {
            $peer = $peerRatings[$player->memberID] ?? null;
            $player->ratingCount = $peer->ratingCount ?? 0;

            if ($peer && $peer->ratingCount >= 1) {
                $attrs = [
                    'striker'   => (float) $peer->avgGoal,
                    'playmaker' => (float) $peer->avgPassing,
                    'workhorse' => (float) $peer->avgWork,
                    'defender'  => (float) $peer->avgDefending,
                ];
                arsort($attrs);
                $player->role = array_key_first($attrs);
            } else {
                $goals = DB::table('scoring-actions')
                    ->where('memberID', $player->memberID)
                    ->where('actionGoal', 1)
                    ->where('actionActive', 1)
                    ->count();
                $assists = DB::table('scoring-actions')
                    ->where('secondID', $player->memberID)
                    ->where('actionGoal', 1)
                    ->where('actionActive', 1)
                    ->count();
                $saves = DB::table('scoring-actions')
                    ->where('memberID', $player->memberID)
                    ->where('typeID', 3)
                    ->where('actionActive', 1)
                    ->count();
                $games = DB::table('results')
                    ->where('resultMemberID', $player->memberID)
                    ->where('resultActive', 1)
                    ->distinct('resultGameID')
                    ->count('resultGameID');
                $g = $games > 0 ? $goals / $games : 0;
                $a = $games > 0 ? $assists / $games : 0;
                $s = $games > 0 ? $saves / $games : 0;
                if ($s > $g && $s > $a) {
                    $player->role = 'defender';
                } elseif ($a > $g) {
                    $player->role = 'playmaker';
                } else {
                    $player->role = 'striker';
                }
            }

            $performanceRating = $player->rating;
            if ($peer && $peer->ratingCount >= 1) {
                $peerScore = min(99, round(
                    (((float) $peer->avgGoal * 0.35) +
                     ((float) $peer->avgPassing * 0.25) +
                     ((float) $peer->avgWork * 0.20) +
                     ((float) $peer->avgDefending * 0.10) +
                     ((float) $peer->avgOverall * 0.10)) / 4 * 99
                ));
                $player->rating = min(99, round(($peerScore * 0.70) + ($performanceRating * 0.30)));
            } else {
                $player->rating = $performanceRating;
            }

            $player->roleIcon = $roleIconMap[$player->role];
            return $player;
        });

        $playerDataForJs = $registered->map(function ($p) {
            return [
                'id'     => $p->memberID,
                'rating' => $p->rating,
                'role'   => $p->role,
                'bench'  => (bool) $p->bench,
                'age'    => $p->memberBirthday ? \Carbon\Carbon::parse($p->memberBirthday)->age : null,
            ];
        })->values();

        return view('admin.teams', compact('game', 'registered', 'teamNames', 'teamColors', 'playerDataForJs'));
    }

    public function toggleBench($gameID, $memberID)
    {
        $reg = DB::table('game-registrations')
            ->where('gameID', $gameID)
            ->where('memberID', $memberID)
            ->first();

        if (!$reg) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $newValue = $reg->registrationBench ? 0 : 1;

        DB::table('game-registrations')
            ->where('gameID', $gameID)
            ->where('memberID', $memberID)
            ->update(['registrationBench' => $newValue]);

        return response()->json(['bench' => $newValue]);
    }

    public function ratings()
    {
        $ratings = DB::table('player-ratings as r')
            ->join('members as rater', 'r.raterMemberID', '=', 'rater.memberID')
            ->join('members as rated', 'r.ratedMemberID', '=', 'rated.memberID')
            ->select(
                'r.*',
                'rater.memberNameFirst as raterFirst',
                'rater.memberNameLast as raterLast',
                'rated.memberNameFirst as ratedFirst',
                'rated.memberNameLast as ratedLast',
                'rated.memberSlug'
            )
            ->orderBy('r.created_at', 'desc')
            ->get();

        $summary = DB::table('player-ratings as r')
            ->join('members as m', 'r.ratedMemberID', '=', 'm.memberID')
            ->select(
                'r.ratedMemberID',
                'm.memberNameFirst',
                'm.memberNameLast',
                'm.memberSlug',
                DB::raw('COUNT(*) as ratingCount'),
                DB::raw('ROUND(AVG(r.ratingGoal), 1) as avgGoal'),
                DB::raw('ROUND(AVG(r.ratingPassing), 1) as avgPassing'),
                DB::raw('ROUND(AVG(r.ratingWork), 1) as avgWork'),
                DB::raw('ROUND(AVG(r.ratingDefending), 1) as avgDefending'),
                DB::raw('ROUND(AVG(r.ratingOverall), 1) as avgOverall'),
                DB::raw('ROUND((AVG(r.ratingGoal) + AVG(r.ratingPassing) + AVG(r.ratingWork) + AVG(r.ratingDefending) + AVG(r.ratingOverall)) / 5 * 24.75, 0) as compositeRating')
            )
            ->groupBy('r.ratedMemberID', 'm.memberNameFirst', 'm.memberNameLast', 'm.memberSlug')
            ->orderByDesc('compositeRating')
            ->get();

        return view('admin.ratings', compact('ratings', 'summary'));
    }

    public function playerRatings($memberID)
    {
        $player = DB::table('members')->where('memberID', $memberID)->firstOrFail();

        $ratings = DB::table('player-ratings as r')
            ->join('members as rater', 'r.raterMemberID', '=', 'rater.memberID')
            ->where('r.ratedMemberID', $memberID)
            ->select('r.*', 'rater.memberNameFirst', 'rater.memberNameLast')
            ->orderBy('r.created_at', 'desc')
            ->get();

        $averages = DB::table('player-ratings')
            ->where('ratedMemberID', $memberID)
            ->select(
                DB::raw('ROUND(AVG(ratingGoal), 2) as avgGoal'),
                DB::raw('ROUND(AVG(ratingPassing), 2) as avgPassing'),
                DB::raw('ROUND(AVG(ratingWork), 2) as avgWork'),
                DB::raw('ROUND(AVG(ratingDefending), 2) as avgDefending'),
                DB::raw('ROUND(AVG(ratingOverall), 2) as avgOverall'),
                DB::raw('COUNT(*) as total')
            )
            ->first();

        return view('admin.player-ratings', compact('player', 'ratings', 'averages'));
    }

    public function messages()
    {
        $messages = DB::table('messages')->orderBy('created_at', 'desc')->get();
        return view('admin.messages.index', compact('messages'));
    }

    public function createMessage()
    {
        return view('admin.messages.create');
    }

    public function storeMessage(Request $request)
    {
        $code = strtoupper(\Illuminate\Support\Str::random(8));
        DB::table('messages')->insert([
            'messageCode'    => $code,
            'messageSubject' => $request->input('subject'),
            'messageBody'    => $request->input('body'),
            'messageActive'  => 1,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
        return redirect('/admin/messages/' . $code . '/links')->with('success', 'Message created!');
    }

    public function editMessage($messageCode)
    {
        $message = DB::table('messages')->where('messageCode', $messageCode)->firstOrFail();
        return view('admin.messages.edit', compact('message'));
    }

    public function updateMessage(Request $request, $messageCode)
    {
        DB::table('messages')->where('messageCode', $messageCode)->update([
            'messageSubject' => $request->input('subject'),
            'messageBody'    => $request->input('body'),
            'messageActive'  => $request->input('active', 1),
            'updated_at'     => now(),
        ]);
        return redirect('/admin/messages')->with('success', 'Message updated.');
    }

    public function messageLinks($messageCode)
    {
        $message = DB::table('messages')->where('messageCode', $messageCode)->firstOrFail();

        $players = DB::table('members')
            ->where('memberActive', 1)
            ->whereNull('memberParent')
            ->orderBy('memberNameLast')
            ->orderBy('memberNameFirst')
            ->get();

        return view('admin.messages.links', compact('message', 'players'));
    }

    public function printSheet($gameID)
    {
        $game = DB::table('games as g')
            ->join('seasons as s', 'g.gameSeasonID', '=', 's.seasonID')
            ->where('g.gameID', $gameID)
            ->select('g.*', 's.seasonName')
            ->firstOrFail();

        $registered = DB::table('game-registrations as r')
            ->join('members as m', 'r.memberID', '=', 'm.memberID')
            ->where('r.gameID', $gameID)
            ->where('r.registrationStatus', 1)
            ->orderBy('m.memberNameLast')
            ->orderBy('m.memberNameFirst')
            ->select('m.*')
            ->get();

        $memberIDs = $registered->pluck('memberID');

        $gamesPlayed = DB::table('results')
            ->whereIn('resultMemberID', $memberIDs)
            ->where('resultActive', 1)
            ->select('resultMemberID', DB::raw('COUNT(DISTINCT resultGameID) as total'))
            ->groupBy('resultMemberID')
            ->get()
            ->keyBy('resultMemberID');

        $bibCounts = DB::table('games')
            ->whereNotNull('gameBibs')
            ->where('gameBibs', '!=', '')
            ->where('gameVisible', 1)
            ->select('gameBibs', DB::raw('COUNT(*) as total'))
            ->groupBy('gameBibs')
            ->get()
            ->keyBy('gameBibs');

        $balances = DB::table('account')
            ->whereIn('memberID', $memberIDs)
            ->where('accountVisible', 1)
            ->select('memberID', DB::raw('SUM(accountValue) as balance'))
            ->groupBy('memberID')
            ->get()
            ->keyBy('memberID');

        $bibsHolder = null;
        if ($game->gameBibs) {
            $bibsHolder = DB::table('members')->where('memberID', $game->gameBibs)->first();
        }

        $players = $registered->map(function ($member) use ($gamesPlayed, $bibCounts, $balances) {
            $games      = $gamesPlayed[$member->memberID]->total ?? 0;
            $bibs       = isset($bibCounts[$member->memberID]) ? $bibCounts[$member->memberID]->total : 0;
            $bibPercent = $games > 0 ? round(($bibs / $games) * 100, 1) : 0;
            $balance    = $balances[$member->memberID]->balance ?? 0;

            return (object) [
                'memberID'        => $member->memberID,
                'memberNameFirst' => $member->memberNameFirst,
                'memberNameLast'  => $member->memberNameLast,
                'memberCode'      => $member->memberCode,
                'games'           => $games,
                'bibPercent'      => $bibPercent,
                'balance'         => $balance,
            ];
        });

        $players = $players->sortBy('memberNameLast')->values();

        return view('admin.print', compact('game', 'players', 'bibsHolder'));
    }

    public function saveTeams(Request $request, $gameID)
    {
        $game      = DB::table('games')->where('gameID', $gameID)->firstOrFail();
        $pointsMap = [1 => 3, 2 => 2, 3 => 1];

        foreach ($request->input('teams', []) as $memberID => $teamID) {
            $teamID = (int) $teamID;
            if (!$teamID || !isset($pointsMap[$teamID])) continue;

            $existing = DB::table('results')
                ->where('resultGameID', $game->gameID)
                ->where('resultMemberID', $memberID)
                ->first();

            if ($existing) {
                DB::table('results')->where('resultID', $existing->resultID)->update([
                    'resultTeamID' => $teamID,
                    'resultPoints' => $pointsMap[$teamID],
                    'resultEdited' => now(),
                ]);
            } else {
                DB::table('results')->insert([
                    'resultSeasonID' => $game->gameSeasonID,
                    'resultGameID'   => $game->gameID,
                    'resultMemberID' => $memberID,
                    'resultTeamID'   => $teamID,
                    'resultPoints'   => $pointsMap[$teamID],
                    'resultActive'   => 1,
                    'resultVisited'  => 0,
                    'resultCreated'  => now(),
                    'resultEdited'   => now(),
                ]);
            }
        }

        return redirect("/admin/teams/{$gameID}")->with('success', 'Teams saved successfully!');
    }
}
