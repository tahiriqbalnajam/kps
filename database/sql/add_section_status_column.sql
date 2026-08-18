-- Add enable/disable status to sections.
-- Run against each tenant DB (see databases table in the super DB).
ALTER TABLE sections
  ADD COLUMN status ENUM('enable','disable') NOT NULL DEFAULT 'enable' AFTER class_id;
