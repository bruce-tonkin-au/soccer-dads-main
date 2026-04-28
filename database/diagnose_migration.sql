-- ============================================================
-- Migration Diagnostics
-- Purpose: distinguish pre-existing data issues from migration
--          bugs. Run each section and review the output.
-- ============================================================


-- ════════════════════════════════════════════════════════════
-- SECTION A: orphaned_games + orphaned_game_seasons (2 rows)
-- ════════════════════════════════════════════════════════════
-- gameSeasonID is NULL → the original gameSeason VARCHAR
-- did not match any seasons.seasonKey at migration time.
-- This is pre-existing if the season was already missing/deleted.
-- ────────────────────────────────────────────────────────────

-- A1. Show the 2 orphaned games
SELECT
    g.gameID,
    g.gameRound,
    g.gameDate,
    g.gameVisible,
    g.gameSeasonID,        -- NULL confirms the match failed
    g.gameKey              -- original identifier, still present
FROM games g
WHERE g.gameSeasonID IS NULL;

-- A2. Check if gameKey appears as resultSeasonID source (cross-ref)
--     If results rows reference these games, losing the season
--     is consequential.
SELECT
    g.gameID,
    COUNT(r.resultID) AS results_referencing_this_game
FROM games g
LEFT JOIN results r ON r.resultGameID = g.gameID
WHERE g.gameSeasonID IS NULL
GROUP BY g.gameID;

-- A3. Are there ANY seasons left in the table we can match to?
--     (Sanity check — confirms seasons table is intact)
SELECT seasonID, seasonKey, seasonName, seasonVisible
FROM seasons
ORDER BY seasonID;


-- ════════════════════════════════════════════════════════════
-- SECTION B: results_missing_game (11 rows)
-- ════════════════════════════════════════════════════════════
-- resultGameID is NULL → the original resultGame VARCHAR key
-- did not match any games.gameKey at migration time.
-- Likely cause: game was deleted from games table, or resultGame
-- stored a value that was never a real gameKey.
-- ────────────────────────────────────────────────────────────

-- B1. Show sample rows — what else do we know about them?
SELECT
    r.resultID,
    r.resultGameID,        -- NULL
    r.resultMemberID,
    r.resultSeasonID,
    r.resultTeamID,
    r.resultActive,
    r.resultCreated,
    r.resultEdited,
    m.memberNameFirst,
    m.memberNameLast
FROM results r
LEFT JOIN members m ON r.resultMemberID = m.memberID
WHERE r.resultGameID IS NULL
ORDER BY r.resultCreated DESC;

-- B2. Are all 11 also missing season? (compounding orphan)
SELECT
    r.resultID,
    CASE WHEN r.resultGameID   IS NULL THEN 'YES' ELSE 'no' END AS missing_game,
    CASE WHEN r.resultMemberID IS NULL THEN 'YES' ELSE 'no' END AS missing_member,
    CASE WHEN r.resultSeasonID IS NULL THEN 'YES' ELSE 'no' END AS missing_season,
    CASE WHEN r.resultTeamID NOT IN (1,2,3)
              OR r.resultTeamID IS NULL  THEN 'YES' ELSE 'no' END AS bad_team
FROM results r
WHERE r.resultGameID IS NULL;


-- ════════════════════════════════════════════════════════════
-- SECTION C: results_missing_member (1 row)
-- ════════════════════════════════════════════════════════════
-- resultMemberID is NULL → the original resultMember VARCHAR key
-- did not match any members.memberKey at migration time.
-- Most likely cause: member was deleted from the members table.
-- ────────────────────────────────────────────────────────────

-- C1. Show the single orphaned result
SELECT
    r.resultID,
    r.resultGameID,
    r.resultMemberID,      -- NULL
    r.resultSeasonID,
    r.resultTeamID,
    r.resultActive,
    r.resultCreated,
    r.resultEdited,
    g.gameDate,
    g.gameRound
FROM results r
LEFT JOIN games g ON r.resultGameID = g.gameID
WHERE r.resultMemberID IS NULL;

-- C2. Is there a member with a NULL memberKey? (key was never set)
SELECT memberID, memberNameFirst, memberNameLast, memberKey, memberActive
FROM members
WHERE memberKey IS NULL OR memberKey = '';


-- ════════════════════════════════════════════════════════════
-- SECTION D: results_missing_season (30 rows)
-- ════════════════════════════════════════════════════════════
-- resultSeasonID is NULL → the original resultSeason VARCHAR
-- did not match any seasons.seasonKey.
-- IMPORTANT: saveTeams() stored resultSeason = game.gameSeason.
-- If the game itself is now orphaned (Section A), its season
-- was already unknown — those rows explain some of the 30.
-- The rest are likely from deleted seasons or data entry errors.
-- ────────────────────────────────────────────────────────────

-- D1. How many of the 30 are explained by an orphaned game?
SELECT
    CASE
        WHEN r.resultGameID IS NULL   THEN 'game also missing'
        WHEN g.gameSeasonID IS NULL   THEN 'game season orphaned'
        ELSE                               'game OK, season still missing'
    END AS diagnosis,
    COUNT(*) AS row_count
FROM results r
LEFT JOIN games g ON r.resultGameID = g.gameID
WHERE r.resultSeasonID IS NULL
GROUP BY 1;

-- D2. For rows where the game IS valid, what season does the
--     game say it belongs to? This reveals what resultSeason
--     SHOULD have been.
SELECT
    r.resultID,
    r.resultGameID,
    g.gameSeasonID         AS game_says_season,  -- what it should be
    s.seasonName,
    r.resultSeasonID,                             -- NULL (the problem)
    r.resultActive,
    r.resultCreated
FROM results r
JOIN  games   g ON r.resultGameID   = g.gameID   -- only valid games
LEFT JOIN seasons s ON g.gameSeasonID = s.seasonID
WHERE r.resultSeasonID IS NULL
ORDER BY r.resultCreated DESC
LIMIT 10;

-- D3. Date range of the 30 affected rows
--     (Helps determine if these predate a season that was deleted)
SELECT
    MIN(r.resultCreated) AS earliest,
    MAX(r.resultCreated) AS latest,
    COUNT(*)             AS total
FROM results r
WHERE r.resultSeasonID IS NULL;


-- ════════════════════════════════════════════════════════════
-- SECTION E: results_bad_team (278 rows)  ← biggest issue
-- ════════════════════════════════════════════════════════════
-- resultTeamID is NULL or not in (1,2,3).
-- The migration only converted three hardcoded keys:
--   'DHJ902klu908' → 1, 'WHD891094lkm' → 2, '902ULK982nbg' → 3
-- Any other value was left NULL.
--
-- LIKELY CAUSE: An older version of saveTeams() stored
-- the integer team number directly ('1','2','3') before the
-- hardcoded-key approach was introduced. Those rows would
-- not match the long-key UPDATE and became NULL.
--
-- This is almost certainly a PRE-EXISTING issue.
-- ────────────────────────────────────────────────────────────

-- E1. What IS in resultTeamID for bad rows?
--     NULL = old value wasn't a recognised key.
--     Other = unexpected value.
SELECT
    resultTeamID,
    COUNT(*) AS row_count
FROM results
WHERE resultTeamID IS NULL OR resultTeamID NOT IN (1, 2, 3)
GROUP BY resultTeamID
ORDER BY row_count DESC;

-- E2. Date distribution — older rows more likely pre-hardcoded-key era
SELECT
    DATE_FORMAT(resultCreated, '%Y-%m') AS month,
    COUNT(*)                            AS bad_team_rows
FROM results
WHERE resultTeamID IS NULL OR resultTeamID NOT IN (1, 2, 3)
GROUP BY 1
ORDER BY 1;

-- E3. Cross-check: for bad-team rows, do the member and game
--     references at least resolve correctly?
SELECT
    CASE WHEN r.resultMemberID IS NOT NULL AND m.memberID IS NOT NULL
              THEN 'member OK'
         ELSE 'member MISSING'
    END AS member_status,
    CASE WHEN r.resultGameID IS NOT NULL AND g.gameID IS NOT NULL
              THEN 'game OK'
         ELSE 'game MISSING'
    END AS game_status,
    COUNT(*) AS rows
FROM results r
LEFT JOIN members m ON r.resultMemberID = m.memberID
LEFT JOIN games   g ON r.resultGameID   = g.gameID
WHERE r.resultTeamID IS NULL OR r.resultTeamID NOT IN (1, 2, 3)
GROUP BY 1, 2;

-- E4. Sample 10 bad-team rows for manual inspection
SELECT
    r.resultID,
    r.resultMemberID,
    r.resultGameID,
    r.resultTeamID,        -- NULL or unexpected
    r.resultActive,
    r.resultCreated,
    m.memberNameFirst,
    m.memberNameLast,
    g.gameDate,
    g.gameRound
FROM results r
LEFT JOIN members m ON r.resultMemberID = m.memberID
LEFT JOIN games   g ON r.resultGameID   = g.gameID
WHERE r.resultTeamID IS NULL OR r.resultTeamID NOT IN (1, 2, 3)
ORDER BY r.resultCreated ASC   -- oldest first — likely the pre-key-era rows
LIMIT 10;

-- E5. Compare: how many GOOD team rows exist (for scale)?
SELECT
    CASE WHEN resultTeamID IN (1,2,3) THEN 'valid team'
         ELSE 'bad / NULL team'
    END AS team_status,
    COUNT(*) AS rows
FROM results
GROUP BY 1;


-- ════════════════════════════════════════════════════════════
-- SECTION F: Summary — pre-existing vs migration-caused?
-- ════════════════════════════════════════════════════════════
-- Run this last for a quick overall picture.
-- ────────────────────────────────────────────────────────────

SELECT
    'orphaned_games'        AS issue,
    COUNT(*)                AS count,
    'gameSeasonID IS NULL — season key never existed in seasons table'
                            AS likely_cause
FROM games WHERE gameSeasonID IS NULL

UNION ALL

SELECT
    'results_missing_game',
    COUNT(*),
    'resultGameID IS NULL — game was deleted or gameKey never matched'
FROM results WHERE resultGameID IS NULL

UNION ALL

SELECT
    'results_missing_member',
    COUNT(*),
    'resultMemberID IS NULL — member deleted or memberKey never matched'
FROM results WHERE resultMemberID IS NULL

UNION ALL

SELECT
    'results_missing_season',
    COUNT(*),
    'resultSeasonID IS NULL — season key mismatch; see D1 for breakdown'
FROM results WHERE resultSeasonID IS NULL

UNION ALL

SELECT
    'results_bad_team',
    COUNT(*),
    'resultTeamID NULL — old rows likely stored int 1/2/3, not the long key string'
FROM results
WHERE resultTeamID IS NULL OR resultTeamID NOT IN (1, 2, 3);
