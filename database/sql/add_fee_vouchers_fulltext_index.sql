-- Add FULLTEXT index on fee_vouchers for fast name search
-- Supports MATCH ... AGAINST with boolean mode prefix matching
-- e.g. searching "doe" finds "John Doe" via word-level matching

ALTER TABLE `fee_vouchers`
ADD FULLTEXT INDEX `ft_student_parent_name` (`student_name`, `parent_name`);
