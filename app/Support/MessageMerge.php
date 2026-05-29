<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class MessageMerge
{
    public static function variables(): array
    {
        return [
            'firstName' => "Player's first name",
            'lastName'  => "Player's last name",
            'fullName'  => "Player's full name",
            'code'      => "Player's 3-character member code",
            'claimLink' => "Link to claim their account",
            'balance'   => "Account balance (e.g. \$25.00)",
        ];
    }

    public static function valuesFor($member): array
    {
        $balance = DB::table('account')
            ->where('memberID', $member->memberID)
            ->where('accountVisible', 1)
            ->sum('accountValue');

        $first = $member->memberNameFirst ?? '';
        $last  = $member->memberNameLast  ?? '';

        return [
            'firstName' => $first,
            'lastName'  => $last,
            'fullName'  => trim($first . ' ' . $last),
            'code'      => $member->memberCode ?? '',
            'claimLink' => url('/claim/' . ($member->memberCode ?? '')),
            'balance'   => '$' . number_format((float) $balance, 2) . ((float) $balance < 0 ? ' owing' : ''),
        ];
    }

    public static function render(?string $body, $member): string
    {
        if ($body === null || $body === '') return '';
        $values = self::valuesFor($member);
        foreach ($values as $key => $val) {
            $body = preg_replace('/\{\{\s*' . preg_quote($key, '/') . '\s*\}\}/', $val, $body);
        }
        return $body;
    }
}
