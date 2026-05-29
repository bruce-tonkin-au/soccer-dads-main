<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClaimController extends Controller
{
    private function findMember(string $memberCode)
    {
        return DB::table('members')
            ->whereRaw('LOWER("memberCode") = LOWER(?)', [$memberCode])
            ->first();
    }

    public function show(string $memberCode)
    {
        $member = $this->findMember($memberCode);
        if (!$member) abort(404);

        if ($member->memberClaimed) {
            return view('claim.already', compact('member'));
        }

        return view('claim.show', compact('member'));
    }

    public function store(Request $request, string $memberCode)
    {
        $member = $this->findMember($memberCode);
        if (!$member) abort(404);

        if ($member->memberClaimed) {
            return redirect('/claim/' . $memberCode);
        }

        $validated = $request->validate([
            'firstName' => 'required|string|max:100',
            'lastName'  => 'required|string|max:100',
            'email'     => 'required|email|max:255',
            'mobile'    => 'required|string|max:50',
            'birthday'  => 'required|date|before:today',
            'country'   => 'required|string|max:10',
            'photo'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'contacts'                => 'required|array|min:2',
            'contacts.*.name'         => 'required|string|max:100',
            'contacts.*.relationship' => 'nullable|string|max:100',
            'contacts.*.phone'        => 'required|string|max:50',
            'contacts.*.email'        => 'nullable|email|max:255',
            'primaryContact'          => 'required|integer|min:0',
        ]);

        $contacts = $validated['contacts'];
        $primaryIndex = (int) $validated['primaryContact'];
        if (!isset($contacts[$primaryIndex])) {
            return back()->withInput()->withErrors([
                'primaryContact' => 'Please mark one emergency contact as primary.',
            ]);
        }

        $update = [
            'memberNameFirst'   => $validated['firstName'],
            'memberNameLast'    => $validated['lastName'],
            'memberEmail'       => $validated['email'],
            'memberPhoneMobile' => $validated['mobile'],
            'memberBirthday'    => $validated['birthday'],
            'memberCountry'     => $validated['country'],
            'memberClaimed'     => true,
            'memberClaimedAt'   => now(),
        ];

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('members/' . $member->memberID, 'public');
            $update['memberPhoto'] = $path;
        }

        DB::transaction(function () use ($member, $update, $contacts, $primaryIndex) {
            DB::table('members')->where('memberID', $member->memberID)->update($update);

            DB::table('emergency_contacts')->where('memberID', $member->memberID)->delete();

            foreach ($contacts as $i => $c) {
                DB::table('emergency_contacts')->insert([
                    'memberID'             => $member->memberID,
                    'contactName'          => $c['name'],
                    'contactRelationship'  => $c['relationship'] ?? null,
                    'contactPhone'         => $c['phone'],
                    'contactEmail'         => $c['email'] ?? null,
                    'contactPrimary'       => $i === $primaryIndex,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);
            }
        });

        session(['player_id' => $member->memberID]);

        return redirect('/claim/' . $memberCode . '/welcome');
    }

    public function welcome(string $memberCode)
    {
        $member = $this->findMember($memberCode);
        if (!$member) abort(404);
        if (!$member->memberClaimed) {
            return redirect('/claim/' . $memberCode);
        }
        return view('claim.welcome', compact('member'));
    }
}
