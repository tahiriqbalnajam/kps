-- Record absence per paper on marks.
--
--   exam_results.absent — new column: a student did not sit that exam paper. `obtained_marks`
--                         stays numeric (NOT NULL), so this flag is what marks the paper and
--                         every reader excludes the row instead of reading its 0.
--   test_results.absent — the same flag on tests (already used by the code); added here only
--                         on tenant DBs that are missing it.
--
-- Run against each tenant DB (see the `databases` table in the super DB). The /super panel's
-- "Run SQL" action loops every registered tenant in one go.
-- Each column is added only when it is missing, so this is safe to re-run on DBs that already
-- have it — MySQL has no ADD COLUMN IF NOT EXISTS (MariaDB does).

-- 1. exam_results.absent — the column this change adds.
SET @missing := (SELECT COUNT(*) = 0 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'exam_results' AND COLUMN_NAME = 'absent');
SET @sql := IF(@missing,
    'ALTER TABLE `exam_results` ADD COLUMN `absent` ENUM(''yes'',''no'') NOT NULL DEFAULT ''no'' AFTER `student_id`',
    'DO 0');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. test_results.absent — dependency of the same feature (no-op where it already exists).
SET @missing := (SELECT COUNT(*) = 0 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'test_results' AND COLUMN_NAME = 'absent');
SET @sql := IF(@missing,
    'ALTER TABLE `test_results` ADD COLUMN `absent` ENUM(''yes'',''no'') NOT NULL DEFAULT ''no'' AFTER `student_id`',
    'DO 0');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verify afterwards (needs a MySQL client — the /super panel reports only success/error per DB
-- and does not print result sets):
-- SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT
--   FROM information_schema.COLUMNS
--  WHERE TABLE_SCHEMA = DATABASE() AND COLUMN_NAME = 'absent'
--    AND TABLE_NAME IN ('exam_results', 'test_results');
