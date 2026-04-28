-- ============================================================
-- Migration Verification Queries
-- Run after: php artisan migrate
-- Every query should return zero rows or pass its assertion.
-- ============================================================

-- ── 1. Schema: old columns are gone ─────────────────────────

-- Should return 0 rows (gameSeason column must not exist)
SELECT COUNT(*)
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME   = 'games'
  AND COLUMN_NAME  = 'gameSeason';

-- Should return 0 rows (resultGame/Member/Season/Team must not exist)
SELECT COLUMN_NAME
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME   = 'results'
  AND COLUMN_NAME IN ('resultGame', 'resultMember', 'resultSeason', 'resultTeam');

-- ── 2. Schema: new INT columns exist ────────────────────────

-- Should return exactly 1 row per column listed (5 total)
SELECT COLUMN_NAME, DATA_TYPE
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND (
    (TABLE_NAME = 'games'   AND COLUMN_NAME = 'gameSeasonID')
    OR (TABLE_NAME = 'results' AND COLUMN_NAME IN (
          'resultGameID', 'resultMemberID', 'resultSeasonID', 'resultTeamID'))
  )
ORDER BY TABLE_NAME, COLUMN_NAME;

-- ── 3. Data integrity: no NULL FK values post-migration ─────

-- Should return 0 (every game maps to a valid season)
SELECT COUNT(*) AS orphaned_games
FROM games
WHERE gameSeasonID IS NULL;

-- Should return 0 (every result maps to a valid game)
SELECT COUNT(*) AS results_missing_game
FROM results
WHERE resultGameID IS NULL;

-- Should return 0 (every result maps to a valid member)
SELECT COUNT(*) AS results_missing_member
FROM results
WHERE resultMemberID IS NULL;

-- Should return 0 (every result maps to a valid season)
SELECT COUNT(*) AS results_missing_season
FROM results
WHERE resultSeasonID IS NULL;

-- Should return 0 (every result has a team 1, 2, or 3)
SELECT COUNT(*) AS results_bad_team
FROM results
WHERE resultTeamID IS NULL OR resultTeamID NOT IN (1, 2, 3);

-- ── 4. Data integrity: FK values resolve correctly ──────────

-- Should return 0 orphans (gameSeasonID points to a real season)
SELECT COUNT(*) AS orphaned_game_seasons
FROM games g
LEFT JOIN seasons s ON g.gameSeasonID = s.seasonID
WHERE s.seasonID IS NULL;

-- Should return 0 orphans (resultGameID points to a real game)
SELECT COUNT(*) AS orphaned_result_games
FROM results r
LEFT JOIN games g ON r.resultGameID = g.gameID
WHERE g.gameID IS NULL;

-- Should return 0 orphans (resultMemberID points to a real member)
SELECT COUNT(*) AS orphaned_result_members
FROM results r
LEFT JOIN members m ON r.resultMemberID = m.memberID
WHERE m.memberID IS NULL;

-- ── 5. Row count sanity: nothing was lost ───────────────────

-- Compare these to your pre-migration counts.
-- Before migration, save: SELECT COUNT(*) FROM results; etc.
SELECT 'results'  AS tbl, COUNT(*) AS rows FROM results
UNION ALL
SELECT 'games',           COUNT(*)         FROM games
UNION ALL
SELECT 'members',         COUNT(*)         FROM members
UNION ALL
SELECT 'seasons',         COUNT(*)         FROM seasons;

-- ── 6. Cross-check: old string keys still round-trip ────────
-- Verify that every resultMemberID correctly maps back to the
-- same memberKey that was stored in resultMember before migration.
-- Should return 0 mismatches.
SELECT COUNT(*) AS key_mismatches
FROM results r
JOIN members m ON r.resultMemberID = m.memberID
JOIN games   g ON r.resultGameID   = g.gameID
WHERE m.memberKey IS NULL
   OR g.gameKey   IS NULL;

-- ── 7. Application smoke-test queries ───────────────────────

-- Dashboard next-game join (mirrors AdminController::dashboard)
SELECT g.gameID, g.gameRound, s.seasonName
FROM games AS g
JOIN seasons AS s ON g.gameSeasonID = s.seasonID
WHERE g.gameVisible = 1
ORDER BY g.gameID ASC
LIMIT 1;

-- Team assignment read-back (mirrors AdminController::teams)
SELECT r.resultID, r.resultMemberID, r.resultGameID, r.resultTeamID,
       m.memberNameFirst, m.memberNameLast
FROM results r
JOIN members m ON r.resultMemberID = m.memberID
WHERE r.resultActive = 1
LIMIT 10;

-- Games-by-season (mirrors SeasonsController::show)
SELECT g.gameID, g.gameRound, g.gameDate
FROM games g
JOIN seasons s ON g.gameSeasonID = s.seasonID
WHERE s.seasonVisible = 1
ORDER BY s.seasonID DESC, g.gameRound ASC
LIMIT 10;
