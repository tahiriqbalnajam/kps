ALTER TABLE `exams` ADD COLUMN `start_date` DATE NULL AFTER `section_id`, ADD COLUMN `end_date` DATE NULL AFTER `start_date`;
