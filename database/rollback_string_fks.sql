-- ============================================================
-- Rollback: Integer FKs → Original String Keys
-- Run this ONLY if you need to undo the migration.
-- Prerequisite: memberKey / seasonKey / gameKey columns must
-- still exist on their source tables (they are NOT dropped by
-- the migration — this is intentional).
-- ============================================================

START TRANSACTION;

-- ── Step 1: Restore results string FK columns ────────────────

ALTER TABLE results
    ADD COLUMN resultGame   VARCHAR(12) NULL AFTER resultGameID,
    ADD COLUMN resultMember VARCHAR(12) NULL AFTER resultMemberID,
    ADD COLUMN resultSeason VARCHAR(12) NULL AFTER resultSeasonID,
    ADD COLUMN resultTeam   VARCHAR(12) NULL AFTER resultTeamID;

-- Back-fill resultGame from games.gameKey
UPDATE results r
    JOIN games g ON r.resultGameID = g.gameID
    SET r.resultGame = g.gameKey;

-- Back-fill resultMember from members.memberKey
UPDATE results r
    JOIN members m ON r.resultMemberID = m.memberID
    SET r.resultMember = m.memberKey;

-- Back-fill resultSeason from seasons.seasonKey
UPDATE results r
    JOIN seasons s ON r.resultSeasonID = s.seasonID
    SET r.resultSeason = s.seasonKey;

-- Back-fill resultTeam from integer team ID to original hardcoded keys
UPDATE results SET resultTeam = 'DHJ902klu908' WHERE resultTeamID = 1;
UPDATE results SET resultTeam = 'WHD891094lkm' WHERE resultTeamID = 2;
UPDATE results SET resultTeam = '902ULK982nbg' WHERE resultTeamID = 3;

-- Verify back-fill before dropping new columns
-- (abort if any row is missing a string value)
DO (SELECT IF(
    (SELECT COUNT(*) FROM results WHERE resultGame IS NULL
                                     OR resultMember IS NULL
                                     OR resultSeason IS NULL
                                     OR resultTeam   IS NULL) > 0,
    (SELECT RAISE_ERROR('Rollback aborted: NULL string FKs after back-fill')),
    1
));

-- Drop the new integer columns
ALTER TABLE results
    DROP COLUMN resultGameID,
    DROP COLUMN resultMemberID,
    DROP COLUMN resultSeasonID,
    DROP COLUMN resultTeamID;

-- ── Step 2: Restore games.gameSeason ────────────────────────

ALTER TABLE games
    ADD COLUMN gameSeason VARCHAR(12) NULL AFTER gameSeasonID;

-- Back-fill gameSeason from seasons.seasonKey
UPDATE games g
    JOIN seasons s ON g.gameSeasonID = s.seasonID
    SET g.gameSeason = s.seasonKey;

-- Verify
DO (SELECT IF(
    (SELECT COUNT(*) FROM games WHERE gameSeason IS NULL) > 0,
    (SELECT RAISE_ERROR('Rollback aborted: NULL gameSeason after back-fill')),
    1
));

ALTER TABLE games DROP COLUMN gameSeasonID;

-- ── Step 3: Verify rollback completeness ────────────────────

-- These should all return 0
SELECT 'results with NULL resultGame'   AS check_name, COUNT(*) AS failures FROM results WHERE resultGame   IS NULL
UNION ALL
SELECT 'results with NULL resultMember',                COUNT(*) FROM results WHERE resultMember IS NULL
UNION ALL
SELECT 'results with NULL resultSeason',                COUNT(*) FROM results WHERE resultSeason IS NULL
UNION ALL
SELECT 'results with NULL resultTeam',                  COUNT(*) FROM results WHERE resultTeam   IS NULL
UNION ALL
SELECT 'games with NULL gameSeason',                    COUNT(*) FROM games   WHERE gameSeason   IS NULL;

COMMIT;

-- ── After rollback: revert routes/web.php and controllers ───
-- The migration touched these files. Either:
--   git checkout HEAD~1 -- routes/web.php app/Http/Controllers/
-- or manually revert {seasonID} back to {seasonKey} in routes,
-- and revert all gameSeasonID → gameSeason, resultGameID → resultGame etc.
-- in the controller files.
