<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Commentator;

class AdminController extends Controller
{
    // AUTH
    public function showLogin()
    {
        if (Auth::check()) return redirect('/admin');
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, false)) {
            $request->session()->regenerate();
            return redirect('/admin');
        }

        return back()->with('error', 'Invalid credentials.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/admin/login');
    }

    // DASHBOARD
    public function dashboard()
    {
        $stats = [
            'players' => DB::table('members')->count(),
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
                ->where('g.gameDate', '>=', now()->toDateString())
                ->orderBy('g.gameDate', 'asc')
                ->orderBy('g.gameID', 'asc')
                ->select('g.*', 's.seasonName')
                ->first();
        }

        $registrations = null;
        $recentUnregistered = null;
        $notGoingRegistrations = null;
        if ($nextGame) {
            $registrations = DB::table('game-registrations as r')
                ->join('members as m', 'r.memberID', '=', 'm.memberID')
                ->where('r.gameID', $nextGame->gameID)
                ->where('r.registrationStatus', 1)
                ->where('r.registrationBench', 0)
                ->orderBy('m.memberNameLast')
                ->select('m.memberID', 'm.memberNameFirst', 'm.memberNameLast', 'm.memberSlug')
                ->get();

            $notGoingRegistrations = DB::table('game-registrations as r')
                ->join('members as m', 'r.memberID', '=', 'm.memberID')
                ->where('r.gameID', $nextGame->gameID)
                ->where('r.registrationStatus', 2)
                ->orderBy('m.memberNameLast')
                ->select('m.memberID', 'm.memberNameFirst', 'm.memberNameLast', 'm.memberSlug')
                ->get();

            $recentGameIds = DB::table('games')
                ->where('gameVisible', 1)
                ->where('gameID', '<', $nextGame->gameID)
                ->whereExists(function ($q) {
                    $q->select(DB::raw(1))->from('scoring')
                      ->whereColumn('scoring.gameID', 'games.gameID')
                      ->whereNotNull('scoring.scoringEnded');
                })
                ->orderBy('gameID', 'desc')
                ->limit(5)
                ->pluck('gameID');

            if ($recentGameIds->isNotEmpty()) {
                $registeredIds = $registrations->pluck('memberID')
                    ->merge($notGoingRegistrations->pluck('memberID'))
                    ->unique();

                $recentUnregistered = DB::table('results as r')
                    ->join('members as m', 'r.resultMemberID', '=', 'm.memberID')
                    ->where('r.resultActive', 1)
                    ->where('m.memberActive', 1)
                    ->whereIn('r.resultGameID', $recentGameIds)
                    ->when($registeredIds->isNotEmpty(), fn ($q) => $q->whereNotIn('m.memberID', $registeredIds))
                    ->orderBy('m.memberNameLast')
                    ->select('m.memberID', 'm.memberNameFirst', 'm.memberNameLast', 'm.memberSlug')
                    ->distinct()
                    ->get();
            }
        }

        return view('admin.dashboard', compact('stats', 'nextGame', 'registrations', 'recentUnregistered', 'notGoingRegistrations'));
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
            ->select('memberID', DB::raw('SUM("accountValue") as balance'))
            ->groupBy('memberID')
            ->get()
            ->keyBy('memberID');

        $players = $players->map(function($player) use ($balances) {
            $player->balance = $balances[$player->memberID]->balance ?? 0;
            return $player;
        });

        $totalOwing = $players->where('balance', '<', 0)->sum('balance');
        $totalOwed  = $players->where('balance', '>', 0)->sum('balance');

        return view('admin.players.index', compact('players', 'totalOwing', 'totalOwed'));
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

        $chargeableIDs = $games->where('gameDate', '>=', '2026-05-01')->pluck('gameID');

        $gamesWithTeams = $chargeableIDs->isNotEmpty()
            ? DB::table('scoring-teams-players as stp')
                ->join('scoring-teams as st', 'stp.teamID', '=', 'st.teamsID')
                ->whereIn('st.gameID', $chargeableIDs)
                ->where('stp.playerActive', 1)
                ->distinct()
                ->pluck('st.gameID')
            : collect();

        $chargedGameIDs = $chargeableIDs->isNotEmpty()
            ? DB::table('account')
                ->whereIn('gameID', $chargeableIDs)
                ->whereNotNull('gameID')
                ->distinct()
                ->pluck('gameID')
            : collect();

        return view('admin.games.index', compact('season', 'games', 'gamesWithTeams', 'chargedGameIDs'));
    }

    public function createGame($seasonID)
    {
        $season = DB::table('seasons')->where('seasonID', $seasonID)->firstOrFail();
        return view('admin.games.create', compact('season'));
    }

    public function storeGame(Request $request, $seasonID)
    {
        do {
            $gameCode = strtoupper(Str::random(4));
        } while (DB::table('games')->where('gameCode', $gameCode)->exists());

        $gameID = DB::table('games')->insertGetId([
            'gameSeasonID' => $seasonID,
            'gameRound'    => $request->input('gameRound'),
            'gameDate'     => $request->input('gameDate'),
            'gameYouTube'  => $request->input('gameYouTube'),
            'gameVisible'  => $request->input('gameVisible', 1),
            'gameCode'     => $gameCode,
        ], 'gameID');

        DB::table('scoring-settings')->insert([
            'gameID'               => $gameID,
            'settingsRounds'       => 7,
            'settingsGamesPerRound'=> 3,
            'settingsGameDuration' => 60,
            'settingsTeams'        => 3,
            'teamAID'              => null,
            'teamBID'              => null,
            'teamCID'              => null,
            'settingsPointsWin'    => 2,
            'settingsPointsDraw'   => 1,
            'commentatorID'        => Commentator::where('commentatorDefault', 1)->value('commentatorID') ?? Commentator::orderBy('commentatorID')->value('commentatorID'),
            'settingsActive'       => 1,
            'settingsVisible'      => 1,
        ]);

        return redirect("/admin/seasons/{$seasonID}/games")->with('success', 'Game created.');
    }

    public function editGame($seasonID, $gameID)
    {
        $season = DB::table('seasons')->where('seasonID', $seasonID)->firstOrFail();
        $game   = DB::table('games')->where('gameID', $gameID)->firstOrFail();

        // Attendees for this game night, plus the already-saved bibs washer (if any)
        $attendeeIDs = DB::table('results')
            ->where('resultGameID', $gameID)
            ->where('resultActive', 1)
            ->pluck('resultMemberID')
            ->unique();

        if ($game->gameBibsMemberID) {
            $attendeeIDs = $attendeeIDs->push($game->gameBibsMemberID)->unique();
        }

        $members = DB::table('members')
            ->whereIn('memberID', $attendeeIDs)
            ->orderBy('memberNameLast')
            ->orderBy('memberNameFirst')
            ->select('memberID', 'memberNameFirst', 'memberNameLast')
            ->get();

        return view('admin.games.edit', compact('season', 'game', 'members'));
    }

    public function updateGame(Request $request, $seasonID, $gameID)
    {
        $gameCode = strtoupper(trim($request->input('gameCode', ''))) ?: null;

        if ($gameCode) {
            $taken = DB::table('games')
                ->where('gameCode', $gameCode)
                ->where('gameID', '!=', $gameID)
                ->exists();
            if ($taken) {
                return back()->withErrors(['gameCode' => 'This game code is already in use by another game.'])->withInput();
            }
        }

        DB::table('games')->where('gameID', $gameID)->update([
            'gameRound'          => $request->input('gameRound'),
            'gameDate'           => $request->input('gameDate'),
            'gameYouTube'        => $request->input('gameYouTube'),
            'gameYouTubeStart'   => $request->input('gameYouTubeStart') ?: null,
            'gameVisible'        => $request->input('gameVisible', 1),
            'gameCode'           => $gameCode,
            'gameBibsMemberID'   => $request->input('gameBibsMemberID') ?: null,
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
                DB::raw('COUNT(*) as "ratingCount"'),
                DB::raw('AVG("ratingGoal") as "avgGoal"'),
                DB::raw('AVG("ratingPassing") as "avgPassing"'),
                DB::raw('AVG("ratingWork") as "avgWork"'),
                DB::raw('AVG("ratingDefending") as "avgDefending"'),
                DB::raw('AVG("ratingOverall") as "avgOverall"')
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

            // Weighting: peer reviews vs performance stats (must sum to 1.0)
            $peerWeight        = 1.00;
            $performanceWeight = 0.00;

            $performanceRating = $player->rating;
            if ($peer && $peer->ratingCount >= 1) {
                $peerScore = min(99, round(
                    (((float) $peer->avgGoal * 0.35) +
                     ((float) $peer->avgPassing * 0.25) +
                     ((float) $peer->avgWork * 0.20) +
                     ((float) $peer->avgDefending * 0.10) +
                     ((float) $peer->avgOverall * 0.10)) / 4 * 99
                ));
                $player->rating = min(99, round(($peerScore * $peerWeight) + ($performanceRating * $performanceWeight)));
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

    public function promotePlayer($gameID, $memberID)
    {
        DB::table('game-registrations')
            ->where('gameID', $gameID)
            ->where('memberID', $memberID)
            ->where('registrationStatus', 1)
            ->update([
                'registrationBench'  => 0,
                'registrationEdited' => now(),
            ]);

        $seq = DB::table('game-registrations')
            ->where('gameID', $gameID)
            ->where('registrationStatus', 1)
            ->where('registrationBench', 0)
            ->count();
        DB::table('game-registration-events')->insert([
            'gameID'               => $gameID,
            'memberID'             => $memberID,
            'eventType'            => 'bench_promoted',
            'registrationSequence' => $seq,
            'created_at'           => now(),
        ]);

        return redirect('/admin')->with('success', 'Player promoted to active.');
    }

    public function demotePlayer($gameID, $memberID)
    {
        $existing = DB::table('game-registrations')
            ->where('gameID', $gameID)
            ->where('memberID', $memberID)
            ->first();

        $wasActive = $existing && $existing->registrationStatus == 1 && $existing->registrationBench == 0;

        DB::table('game-registrations')
            ->where('gameID', $gameID)
            ->where('memberID', $memberID)
            ->update([
                'registrationStatus' => 2,
                'registrationBench'  => 0,
                'registrationEdited' => now(),
            ]);

        if ($wasActive) {
            DB::table('game-registration-events')->insert([
                'gameID'               => $gameID,
                'memberID'             => $memberID,
                'eventType'            => 'admin_deregistered',
                'registrationSequence' => null,
                'created_at'           => now(),
            ]);
        }

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect('/admin')->with('success', 'Player marked as not going.');
    }

    public function registerPlayer($gameID, $memberID)
    {
        $existing = DB::table('game-registrations')
            ->where('gameID', $gameID)
            ->where('memberID', $memberID)
            ->first();

        if ($existing) {
            DB::table('game-registrations')
                ->where('gameID', $gameID)
                ->where('memberID', $memberID)
                ->update([
                    'registrationStatus' => 1,
                    'registrationBench'  => 0,
                    'registrationEdited' => now(),
                ]);
        } else {
            DB::table('game-registrations')->insert([
                'gameID'              => $gameID,
                'memberID'            => $memberID,
                'registrationStatus'  => 1,
                'registrationBench'   => 0,
                'registrationCreated' => now(),
                'registrationEdited'  => now(),
            ]);
        }

        $seq = DB::table('game-registrations')
            ->where('gameID', $gameID)
            ->where('registrationStatus', 1)
            ->where('registrationBench', 0)
            ->count();
        DB::table('game-registration-events')->insert([
            'gameID'               => $gameID,
            'memberID'             => $memberID,
            'eventType'            => 'admin_registered',
            'registrationSequence' => $seq,
            'created_at'           => now(),
        ]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect('/admin')->with('success', 'Player registered.');
    }

    public function registrations($gameID = null)
    {
        $currentSeason = DB::table('seasons')
            ->where('seasonVisible', 1)
            ->orderBy('seasonID', 'desc')
            ->first();

        $nextGame = null;
        if ($currentSeason) {
            $nextGame = DB::table('games')
                ->where('gameVisible', 1)
                ->where('gameSeasonID', $currentSeason->seasonID)
                ->where('gameDate', '>=', now()->toDateString())
                ->orderBy('gameDate', 'asc')
                ->orderBy('gameID', 'asc')
                ->first();
        }

        $allGames = DB::table('games as g')
            ->join('seasons as s', 'g.gameSeasonID', '=', 's.seasonID')
            ->where('g.gameVisible', 1)
            ->orderBy('g.gameDate', 'desc')
            ->orderBy('g.gameID', 'desc')
            ->select('g.gameID', 'g.gameRound', 'g.gameDate', 's.seasonName')
            ->get();

        if (!$gameID) {
            $gameID = $nextGame ? $nextGame->gameID : ($allGames->first() ? $allGames->first()->gameID : null);
        }

        if (!$gameID) abort(404);

        $game = DB::table('games as g')
            ->join('seasons as s', 'g.gameSeasonID', '=', 's.seasonID')
            ->where('g.gameID', $gameID)
            ->select('g.*', 's.seasonName')
            ->firstOrFail();

        // All registrations ordered by when they first registered
        $registrations = DB::table('game-registrations as r')
            ->join('members as m', 'r.memberID', '=', 'm.memberID')
            ->where('r.gameID', $gameID)
            ->orderBy('r.registrationCreated', 'asc')
            ->orderBy('r.registrationID', 'asc')
            ->select('r.*', 'm.memberNameFirst', 'm.memberNameLast', 'm.memberSlug')
            ->get();

        // Assign registration sequence: rank active registrations by registrationCreated
        $activeSeq = 0;
        $benchSeq  = 0;
        $registrations = $registrations->map(function ($r) use (&$activeSeq, &$benchSeq) {
            $r->activeSequence = null;
            $r->benchSequence  = null;
            if ($r->registrationStatus == 1 && $r->registrationBench == 0) {
                $r->activeSequence = ++$activeSeq;
            } elseif ($r->registrationStatus == 1 && $r->registrationBench == 1) {
                $r->benchSequence = ++$benchSeq;
            }
            return $r;
        });

        // Event log for this game (from new events table)
        $events = DB::table('game-registration-events as e')
            ->join('members as m', 'e.memberID', '=', 'm.memberID')
            ->where('e.gameID', $gameID)
            ->orderBy('e.created_at', 'asc')
            ->orderBy('e.eventID', 'asc')
            ->select('e.*', 'm.memberNameFirst', 'm.memberNameLast')
            ->get();

        $isNextGame = $nextGame && $nextGame->gameID == $gameID;

        return view('admin.registrations', compact(
            'game', 'allGames', 'registrations', 'events', 'isNextGame', 'nextGame'
        ));
    }

    public function resetNight($gameID)
    {
        DB::table('games')->where('gameID', $gameID)->update(['gameStartTime' => null]);
        return redirect('/admin')->with('success', 'Game night start time has been reset.');
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
                DB::raw('COUNT(*) as "ratingCount"'),
                DB::raw('ROUND(AVG(r."ratingGoal"), 1) as "avgGoal"'),
                DB::raw('ROUND(AVG(r."ratingPassing"), 1) as "avgPassing"'),
                DB::raw('ROUND(AVG(r."ratingWork"), 1) as "avgWork"'),
                DB::raw('ROUND(AVG(r."ratingDefending"), 1) as "avgDefending"'),
                DB::raw('ROUND(AVG(r."ratingOverall"), 1) as "avgOverall"'),
                DB::raw('ROUND((AVG(r."ratingGoal") + AVG(r."ratingPassing") + AVG(r."ratingWork") + AVG(r."ratingDefending") + AVG(r."ratingOverall")) / 5 * 24.75, 0) as "compositeRating"')
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
                DB::raw('ROUND(AVG("ratingGoal"), 2) as "avgGoal"'),
                DB::raw('ROUND(AVG("ratingPassing"), 2) as "avgPassing"'),
                DB::raw('ROUND(AVG("ratingWork"), 2) as "avgWork"'),
                DB::raw('ROUND(AVG("ratingDefending"), 2) as "avgDefending"'),
                DB::raw('ROUND(AVG("ratingOverall"), 2) as "avgOverall"'),
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
            ->select('resultMemberID', DB::raw('COUNT(DISTINCT "resultGameID") as total'))
            ->groupBy('resultMemberID')
            ->get()
            ->keyBy('resultMemberID');

        $bibCounts = DB::table('games')
            ->whereNotNull('gameBibsMemberID')
            ->where('gameVisible', 1)
            ->select('gameBibsMemberID', DB::raw('COUNT(*) as total'))
            ->groupBy('gameBibsMemberID')
            ->get()
            ->keyBy('gameBibsMemberID');

        $balances = DB::table('account')
            ->whereIn('memberID', $memberIDs)
            ->where('accountVisible', 1)
            ->select('memberID', DB::raw('SUM("accountValue") as balance'))
            ->groupBy('memberID')
            ->get()
            ->keyBy('memberID');

        $bibsHolder = null;
        if ($game->gameBibsMemberID) {
            $bibsHolder = DB::table('members')->where('memberID', $game->gameBibsMemberID)->first();
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
        $gameID = (int) $gameID;

        try {
            $game      = DB::table('games')->where('gameID', $gameID)->firstOrFail();
            $pointsMap = [1 => 3, 2 => 2, 3 => 1];

            // Collect valid assignments: memberID => teamColor (1/2/3)
            $assignments = [];
            foreach ($request->input('teams', []) as $memberID => $teamColor) {
                $teamColor = (int) $teamColor;
                if (!$teamColor || !isset($pointsMap[$teamColor])) continue;
                $assignments[(int) $memberID] = $teamColor;
            }

            \Log::info('saveTeams: starting', [
                'gameID'          => $gameID,
                'assignmentCount' => count($assignments),
            ]);

            DB::transaction(function () use ($gameID, $game, $pointsMap, $assignments) {

                // ── scoring-teams ─────────────────────────────────────────────
                // Ensure one row exists per team color (1=Orange, 2=Green, 3=Blue).
                // Done first so the scoreboard rows exist even before players are
                // assigned, and so $teamRowMap is available for scoring-teams-players.
                $teamRowMap = []; // teamColor => teamsID
                foreach ([1, 2, 3] as $teamColor) {
                    $row = DB::table('scoring-teams')
                        ->where('gameID', $gameID)
                        ->where('teamID', $teamColor)
                        ->first();

                    if ($row) {
                        $teamRowMap[$teamColor] = (int) $row->teamsID;
                        \Log::info("saveTeams: scoring-teams row exists for teamColor=$teamColor", [
                            'gameID'  => $gameID,
                            'teamsID' => $row->teamsID,
                        ]);
                    } else {
                        $newTeamsID = DB::table('scoring-teams')->insertGetId([
                            'gameID'       => $gameID,
                            'teamID'       => $teamColor,
                            'teamsActive'  => 1,
                            'teamsVisible' => 1,
                        ], 'teamsID');
                        $teamRowMap[$teamColor] = (int) $newTeamsID;
                        \Log::info("saveTeams: scoring-teams row created for teamColor=$teamColor", [
                            'gameID'  => $gameID,
                            'teamsID' => $newTeamsID,
                        ]);
                    }
                }

                // ── scoring-teams-players ─────────────────────────────────────
                // scoring-teams-players.teamID is the scoring-teams.teamsID
                // surrogate PK, not the 1/2/3 colour identifier.
                DB::table('scoring-teams-players')
                    ->whereIn('teamID', array_values($teamRowMap))
                    ->delete();

                foreach ($assignments as $memberID => $teamColor) {
                    DB::table('scoring-teams-players')->insert([
                        'teamID'        => $teamRowMap[$teamColor],
                        'memberID'      => $memberID,
                        'playerActive'  => 1,
                        'playerVisible' => 1,
                    ]);
                }

                \Log::info('saveTeams: scoring-teams-players written', [
                    'gameID'       => $gameID,
                    'teamRowMap'   => $teamRowMap,
                    'playersSaved' => count($assignments),
                ]);

                // ── results ───────────────────────────────────────────────────
                foreach ($assignments as $memberID => $teamColor) {
                    $existing = DB::table('results')
                        ->where('resultGameID', $game->gameID)
                        ->where('resultMemberID', $memberID)
                        ->first();

                    if ($existing) {
                        DB::table('results')->where('resultID', $existing->resultID)->update([
                            'resultTeamID' => $teamColor,
                            'resultPoints' => $pointsMap[$teamColor],
                            'resultEdited' => now(),
                        ]);
                    } else {
                        DB::table('results')->insert([
                            'resultSeasonID' => $game->gameSeasonID,
                            'resultGameID'   => $game->gameID,
                            'resultMemberID' => $memberID,
                            'resultTeamID'   => $teamColor,
                            'resultPoints'   => $pointsMap[$teamColor],
                            'resultActive'   => 1,
                            'resultCreated'  => now(),
                            'resultEdited'   => now(),
                        ]);
                    }
                }

                \Log::info('saveTeams: complete', [
                    'gameID'       => $gameID,
                    'playersSaved' => count($assignments),
                ]);

                // ── scoring schedule ──────────────────────────────────────────
                // Generate round-robin rows in the scoring table if none exist
                // yet for this game. Safe to call repeatedly — skipped when rows
                // already exist. Standard 3-team schedule: 3 matchups per round,
                // same fixture order every round.
                if (!DB::table('scoring')->where('gameID', $gameID)->exists()) {
                    $settings   = DB::table('scoring-settings')->where('gameID', $gameID)->first();
                    $roundCount = $settings ? ($settings->settingsRounds ?? 7) : 7;

                    $matchups = [
                        1 => ['home' => 1, 'away' => 2],
                        2 => ['home' => 2, 'away' => 3],
                        3 => ['home' => 3, 'away' => 1],
                    ];

                    $scoringRows = [];
                    for ($round = 1; $round <= $roundCount; $round++) {
                        foreach ($matchups as $gameNum => $matchup) {
                            $scoringRows[] = [
                                'gameID'          => $gameID,
                                'scoringRound'    => $round,
                                'scoringGame'     => $gameNum,
                                'scoringTeamHome' => $matchup['home'],
                                'scoringTeamAway' => $matchup['away'],
                                'scoringElapsed'  => 0,
                                'scoringActive'   => 1,
                                'scoringVisible'  => 1,
                                'scoringCreated'  => now(),
                                'scoringEdited'   => now(),
                            ];
                        }
                    }

                    DB::table('scoring')->insert($scoringRows);
                    \Log::info('saveTeams: scoring schedule generated', [
                        'gameID'       => $gameID,
                        'rounds'       => $roundCount,
                        'rowsInserted' => count($scoringRows),
                    ]);
                }
            });

        } catch (\Throwable $e) {
            \Log::error('saveTeams failed', [
                'gameID'  => $gameID,
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            throw $e;
        }

        return redirect("/admin/teams/{$gameID}")->with('success', 'Teams saved successfully!');
    }

    // CHARGE PLAYERS
    public function previewCharges($seasonID, $gameID)
    {
        $game = DB::table('games as g')
            ->join('seasons as s', 'g.gameSeasonID', '=', 's.seasonID')
            ->where('g.gameID', $gameID)
            ->where('g.gameDate', '>=', '2026-05-01')
            ->select('g.*', 's.seasonName')
            ->first();

        if (!$game) {
            return response()->json(['error' => 'Game not found or not eligible'], 404);
        }

        if (DB::table('account')->where('gameID', $gameID)->exists()) {
            return response()->json(['error' => 'Charges have already been applied for this game night'], 422);
        }

        $charges = $this->buildCharges($game);

        return response()->json([
            'gameID'    => $gameID,
            'seasonID'  => $seasonID,
            'gameRound' => $game->gameRound,
            'gameDate'  => $game->gameDate,
            'charges'   => $charges,
        ]);
    }

    public function processCharges(Request $request, $seasonID, $gameID)
    {
        $request->validate([
            'charges'            => 'required|array|min:1',
            'charges.*.memberID' => 'required|integer',
            'charges.*.amount'   => 'required|numeric|min:0|max:50',
        ]);

        $game = DB::table('games as g')
            ->join('seasons as s', 'g.gameSeasonID', '=', 's.seasonID')
            ->where('g.gameID', $gameID)
            ->where('g.gameDate', '>=', '2026-05-01')
            ->select('g.*', 's.seasonName')
            ->first();

        if (!$game) {
            return redirect("/admin/seasons/{$seasonID}/games")->with('error', 'Game not found or not eligible.');
        }

        if (DB::table('account')->where('gameID', $gameID)->exists()) {
            return redirect("/admin/seasons/{$seasonID}/games")->with('error', 'Charges have already been applied for this game night.');
        }

        // Build canonical list for descriptions and memberID whitelist
        $canonical = collect($this->buildCharges($game))->keyBy('memberID');

        if ($canonical->isEmpty()) {
            return redirect("/admin/seasons/{$seasonID}/games")->with('error', 'No players found to charge for this game night.');
        }

        $posted = collect($request->input('charges'));

        DB::transaction(function () use ($posted, $canonical, $gameID) {
            foreach ($posted as $entry) {
                $memberID = (int) $entry['memberID'];
                if (!$canonical->has($memberID)) {
                    continue; // ignore any memberIDs not in the canonical list
                }
                $amount      = (float) $entry['amount'];
                $description = $canonical[$memberID]['description'];
                DB::table('account')->insert([
                    'memberID'       => $memberID,
                    'accountValue'   => $amount > 0 ? -$amount : 0,
                    'gameID'         => $gameID,
                    'accountComment' => $description,
                    'accountVisible' => 1,
                    'accountCreated' => now(),
                    'accountEdited'  => now(),
                ]);
            }
        });

        $count = $posted->count();
        return redirect("/admin/seasons/{$seasonID}/games")->with('success', "Charges applied: {$count} player" . ($count === 1 ? '' : 's') . " charged for Round {$game->gameRound}.");
    }

    private function buildCharges($game)
    {
        $gameID    = $game->gameID;
        $gameDate  = $game->gameDate;
        $round     = $game->gameRound;
        $season    = $game->seasonName;

        $playerMemberIDs = DB::table('scoring-teams-players as stp')
            ->join('scoring-teams as st', 'stp.teamID', '=', 'st.teamsID')
            ->where('st.gameID', $gameID)
            ->where('stp.playerActive', 1)
            ->distinct()
            ->pluck('stp.memberID');

        if ($playerMemberIDs->isEmpty()) {
            return [];
        }

        $members = DB::table('members')
            ->whereIn('memberID', $playerMemberIDs)
            ->get(['memberID', 'memberNameFirst', 'memberNameLast', 'memberParent']);

        // New players — no result in any game before this one
        $newPlayerIDs = [];
        foreach ($members as $member) {
            $hasPlayedBefore = DB::table('results as r')
                ->join('games as g', 'r.resultGameID', '=', 'g.gameID')
                ->where('r.resultMemberID', $member->memberID)
                ->where('r.resultActive', 1)
                ->where('g.gameDate', '<', $gameDate)
                ->exists();
            if (!$hasPlayedBefore) {
                $newPlayerIDs[] = $member->memberID;
            }
        }

        // Family groups — group by root parent ID
        // Players with memberParent = X share a group with X (and each other)
        $familyGroups = [];
        foreach ($members as $member) {
            $groupKey = ($member->memberParent && $member->memberParent != 0)
                ? $member->memberParent
                : $member->memberID;
            $familyGroups[$groupKey][] = $member->memberID;
        }
        $familyDiscountIDs = [];
        foreach ($familyGroups as $groupMembers) {
            if (count($groupMembers) >= 2) {
                foreach ($groupMembers as $mID) {
                    $familyDiscountIDs[] = $mID;
                }
            }
        }

        $baseDesc = "Game night fee — {$season}, Round {$round}";

        $charges = [];
        foreach ($members as $member) {
            $isNew    = in_array($member->memberID, $newPlayerIDs);
            $isFamily = in_array($member->memberID, $familyDiscountIDs);

            if ($isNew) {
                $amount      = 0;
                $description = 'First night free';
                $reason      = 'First night free';
            } elseif ($isFamily) {
                $amount      = -7.50;
                $description = $baseDesc . ' (family)';
                $reason      = 'Family discount — $7.50';
            } else {
                $amount      = -10.00;
                $description = $baseDesc;
                $reason      = 'Standard — $10.00';
            }

            $charges[] = [
                'memberID'    => $member->memberID,
                'memberName'  => $member->memberNameFirst . ' ' . $member->memberNameLast,
                'amount'      => $amount,
                'description' => $description,
                'reason'      => $reason,
            ];
        }

        usort($charges, fn($a, $b) => strcmp($a['memberName'], $b['memberName']));

        return $charges;
    }

    // FINANCES
    public function finances()
    {
        $transactions = DB::table('account as a')
            ->join('members as m', 'a.memberID', '=', 'm.memberID')
            ->leftJoin('account-payments as ap', 'a.paymentID', '=', 'ap.paymentID')
            ->where('a.accountVisible', 1)
            ->orderBy('a.accountCreated', 'desc')
            ->select(
                'a.accountID',
                'a.accountValue',
                'a.accountComment',
                'a.accountCreated',
                'a.gameID',
                'a.paymentID',
                'm.memberID',
                'm.memberNameFirst',
                'm.memberNameLast',
                'ap.paymentSource',
                'ap.formPaymentType'
            )
            ->get();

        return view('admin.finances', compact('transactions'));
    }

    // COMMENTATORS

    public function commentators()
    {
        $commentators = Commentator::orderBy('commentatorNameFirst')->get();
        return view('admin.commentators.index', compact('commentators'));
    }

    public function createCommentator()
    {
        return view('admin.commentators.create');
    }

    public function storeCommentator(Request $request)
    {
        $data = $request->validate([
            'commentatorNameFirst'    => ['required', 'max:32'],
            'commentatorNameLast'     => ['required', 'max:32'],
            'commentatorAge'          => ['nullable', 'max:2'],
            'commentatorElevenLabsID' => ['nullable', 'max:32'],
            'commentatorAccent'       => ['nullable'],
            'commentatorBackground'   => ['nullable'],
            'commentatorStyle'        => ['nullable'],
            'commentatorFacts'        => ['nullable'],
            'commentatorActive'       => ['nullable', 'boolean'],
            'commentatorVisible'      => ['nullable', 'boolean'],
            'commentatorDefault'      => ['nullable', 'boolean'],
        ]);

        $data['commentatorActive']  = $request->boolean('commentatorActive');
        $data['commentatorVisible'] = $request->boolean('commentatorVisible');
        $data['commentatorDefault'] = $request->boolean('commentatorDefault');

        if ($data['commentatorDefault']) {
            Commentator::query()->update(['commentatorDefault' => 0]);
        }

        Commentator::create($data);

        return redirect('/admin/commentators')->with('success', 'Commentator created.');
    }

    public function editCommentator($commentatorID)
    {
        $commentator = Commentator::findOrFail($commentatorID);
        return view('admin.commentators.edit', compact('commentator'));
    }

    public function updateCommentator(Request $request, $commentatorID)
    {
        $commentator = Commentator::findOrFail($commentatorID);

        $data = $request->validate([
            'commentatorNameFirst'    => ['required', 'max:32'],
            'commentatorNameLast'     => ['required', 'max:32'],
            'commentatorAge'          => ['nullable', 'max:2'],
            'commentatorElevenLabsID' => ['nullable', 'max:32'],
            'commentatorAccent'       => ['nullable'],
            'commentatorBackground'   => ['nullable'],
            'commentatorStyle'        => ['nullable'],
            'commentatorFacts'        => ['nullable'],
            'commentatorActive'       => ['nullable', 'boolean'],
            'commentatorVisible'      => ['nullable', 'boolean'],
            'commentatorDefault'      => ['nullable', 'boolean'],
        ]);

        $data['commentatorActive']  = $request->boolean('commentatorActive');
        $data['commentatorVisible'] = $request->boolean('commentatorVisible');
        $data['commentatorDefault'] = $request->boolean('commentatorDefault');

        if ($data['commentatorDefault']) {
            Commentator::where('commentatorID', '!=', $commentatorID)->update(['commentatorDefault' => 0]);
        }

        $commentator->update($data);

        return redirect('/admin/commentators')->with('success', 'Commentator updated.');
    }
}
