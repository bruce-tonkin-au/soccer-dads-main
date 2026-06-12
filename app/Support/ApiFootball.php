<?php

namespace App\Support;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Thin client for the API-Football (api-sports.io) v3 API. Centralises the base
 * URL and the x-apisports-key auth header so the sync commands stay focused on
 * their own logic.
 */
class ApiFootball
{
    /** World Cup league id and season we sync. */
    public const LEAGUE_ID = 1;
    public const SEASON = 2026;

    protected function client(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.api_football.base_url'), '/'))
            ->withHeaders(['x-apisports-key' => (string) config('services.api_football.key')])
            ->acceptJson()
            ->timeout(30)
            ->retry(2, 500);
    }

    /**
     * GET /fixtures with the given query params. Returns the API's `response`
     * array (one element per fixture).
     *
     * @return array<int, array<string, mixed>>
     */
    public function fixtures(array $query): array
    {
        $response = $this->client()->get('/fixtures', $query);
        $response->throw();

        // API-Football returns HTTP 200 with a non-empty `errors` map for plan /
        // parameter problems (e.g. season not covered by the current plan). Treat
        // those as failures so callers don't mistake them for "no fixtures".
        $errors = $response->json('errors');
        if (! empty($errors)) {
            throw new \RuntimeException('API-Football error: ' . json_encode($errors));
        }

        return $response->json('response', []);
    }

    /**
     * All World Cup fixtures for the configured league/season.
     *
     * @return array<int, array<string, mixed>>
     */
    public function worldCupFixtures(): array
    {
        return $this->fixtures([
            'league' => self::LEAGUE_ID,
            'season' => self::SEASON,
        ]);
    }

    /**
     * A single fixture (with events) by its API-Football id, times in UTC.
     *
     * @return array<string, mixed>|null
     */
    public function fixtureById(int $apiFootballId): ?array
    {
        $response = $this->fixtures([
            'id'       => $apiFootballId,
            'timezone' => 'UTC',
        ]);

        return $response[0] ?? null;
    }
}
