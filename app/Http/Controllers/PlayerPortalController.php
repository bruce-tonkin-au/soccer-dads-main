<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Webhook;

class PlayerPortalController extends Controller
{
    private function getPlayer()
    {
        return DB::table('members')->where('memberID', session('player_id'))->first();
    }

    public function index()
    {
        $player = $this->getPlayer();

        $balance = DB::table('account')
            ->where('memberID', $player->memberID)
            ->where('accountVisible', 1)
            ->sum('accountValue');

        $currentSeason = DB::table('seasons')
            ->where('seasonVisible', 1)
            ->orderBy('seasonID', 'desc')
            ->first();

        $nextGame = null;
        if ($currentSeason) {
            $nextGame = DB::table('games as g')
                ->join('seasons as s', 'g.gameSeason', '=', 's.seasonKey')
                ->where('g.gameVisible', 1)
                ->where('g.gameSeason', $currentSeason->seasonKey)
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('scoring')
                        ->whereColumn('scoring.gameID', 'g.gameID')
                        ->whereNotNull('scoring.scoringEnded');
                })
                ->orderBy('g.gameID', 'asc')
                ->select('g.*', 's.seasonName')
                ->first();
        }

        $registration = null;
        if ($nextGame) {
            $registration = DB::table('game-registrations')
                ->where('gameID', $nextGame->gameID)
                ->where('memberID', $player->memberID)
                ->first();
        }

        $child = DB::table('members')
            ->where('memberParent', $player->memberID)
            ->where('memberActive', 1)
            ->first();

        $childRegistration = null;
        if ($child) {
            $childRegistration = DB::table('game-registrations')
                ->where('gameID', $nextGame->gameID ?? 0)
                ->where('memberID', $child->memberID)
                ->first();
        }

        $transactions = DB::table('account as a')
            ->leftJoin('games as g', 'a.gameID', '=', 'g.gameID')
            ->leftJoin('seasons as s', 'g.gameSeason', '=', 's.seasonKey')
            ->where('a.memberID', $player->memberID)
            ->where('a.accountVisible', 1)
            ->orderBy('a.accountCreated', 'desc')
            ->select(
                'a.accountID',
                'a.accountValue',
                'a.accountComment',
                'a.accountCreated',
                'g.gameDate',
                'g.gameRound',
                's.seasonName',
                's.seasonLink'
            )
            ->get();

        return view('player.portal', compact('player', 'nextGame', 'registration', 'balance', 'child', 'childRegistration', 'transactions'));
    }

    public function profile()
    {
        $player = $this->getPlayer();
        return view('player.profile', compact('player'));
    }

    public function updateProfile(Request $request)
    {
        $player = $this->getPlayer();

        $data = [
            'memberEmail'       => $request->input('email'),
            'memberPhoneMobile' => $request->input('mobile'),
        ];

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('players/photos', 'public');
            $data['memberPhoto'] = $path;
        }

        if ($request->hasFile('photo_card')) {
            $path = $request->file('photo_card')->store('players/cards', 'public');
            $data['memberPhotoCard'] = $path;
        }

        DB::table('members')->where('memberID', $player->memberID)->update($data);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function account()
    {
        $player = $this->getPlayer();

        $balance = DB::table('account')
            ->where('memberID', $player->memberID)
            ->where('accountVisible', 1)
            ->sum('accountValue');

        return view('player.account', compact('player', 'balance'));
    }

    public function history()
    {
        $player = $this->getPlayer();

        $actions = DB::table('scoring-actions as a')
            ->join('scoring as s', 'a.scoringID', '=', 's.scoringID')
            ->join('games as g', 's.gameID', '=', 'g.gameID')
            ->join('seasons as se', 'g.gameSeason', '=', 'se.seasonKey')
            ->leftJoin('members as m2', 'a.secondID', '=', 'm2.memberID')
            ->where('a.memberID', $player->memberID)
            ->where('a.actionActive', 1)
            ->where('a.actionGoal', 1)
            ->orderBy('a.actionTime', 'desc')
            ->select(
                'a.*',
                'g.gameDate',
                'g.gameRound',
                'se.seasonName',
                'se.seasonLink',
                'm2.memberNameFirst as assisterFirst',
                'm2.memberNameLast as assisterLast'
            )
            ->get();

        return view('player.history', compact('player', 'actions'));
    }

    public function topup()
    {
        $player  = $this->getPlayer();
        $balance = DB::table('account')
            ->where('memberID', $player->memberID)
            ->where('accountVisible', 1)
            ->sum('accountValue');

        return view('player.topup', compact('player', 'balance'));
    }

    public function createPayment(Request $request)
    {
        $player = $this->getPlayer();
        $amount = (int) $request->input('amount');

        if (!in_array($amount, [10, 20, 30, 50, 100]) && ($amount % 10 !== 0 || $amount < 10 || $amount > 500)) {
            return back()->with('error', 'Invalid amount. Please choose a valid top-up amount.');
        }

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency'     => 'aud',
                    'product_data' => [
                        'name'        => 'Soccer Dads Account Top Up',
                        'description' => 'Credit for ' . $player->memberNameFirst . ' ' . $player->memberNameLast,
                    ],
                    'unit_amount' => $amount * 100,
                ],
                'quantity' => 1,
            ]],
            'mode'           => 'payment',
            'success_url'    => url('/portal/topup/success?session_id={CHECKOUT_SESSION_ID}'),
            'cancel_url'     => url('/portal/topup/cancel'),
            'metadata'       => [
                'memberID'   => $player->memberID,
                'memberCode' => $player->memberCode,
                'amount'     => $amount,
            ],
            'customer_email' => $player->memberEmail,
        ]);

        return redirect($session->url);
    }

    public function paymentSuccess(Request $request)
    {
        $player = $this->getPlayer();

        Stripe::setApiKey(config('services.stripe.secret'));
        $session = StripeSession::retrieve($request->query('session_id'));

        if ($session && $session->payment_status === 'paid') {
            $this->fulfillPayment($session->id, (float) ($session->amount_total / 100), $session->metadata->member_id ?? $player->memberID);
        }

        return view('player.topup-success', compact('player', 'session'));
    }

    public function paymentCancel()
    {
        $player = $this->getPlayer();
        return redirect('/portal/account')->with('info', 'Payment cancelled.');
    }

    public function stripeWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, config('services.stripe.webhook_secret'));
        } catch (\Exception $e) {
            return response('Webhook error', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            if ($session->payment_status === 'paid') {
                $this->fulfillPayment($session->id, (float) ($session->amount_total / 100), $session->metadata->member_id);
            }
        }

        return response('OK', 200);
    }

    private function fulfillPayment(string $sessionId, float $amount, int $memberID): void
    {
        // Idempotent — skip if account credit already exists for this session
        $exists = DB::table('account')
            ->where('accountComment', 'Stripe top-up ' . $sessionId)
            ->exists();

        if ($exists) return;

        DB::table('account')->insert([
            'memberID'       => $memberID,
            'accountValue'   => $amount,
            'accountComment' => 'Stripe top-up ' . $sessionId,
            'accountVisible' => 1,
            'gameID'         => null,
            'accountCreated' => now(),
        ]);

        DB::table('account-payments')
            ->where('formPaymentID', $sessionId)
            ->update([
                'formPaymentStatus' => 1,
                'paymentVisible'    => 1,
                'paymentEdited'     => now()->format('Y-m-d H:i:s'),
            ]);
    }
}
