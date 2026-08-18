-- Add enable/disable status to classes.
-- Run against each tenant DB (see databases table in the super DB).
ALTER TABLE classes
  ADD COLUMN status ENUM('enable','disable') NOT NULL DEFAULT 'enable' AFTER priority;
