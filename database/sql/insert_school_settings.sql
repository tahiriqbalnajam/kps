-- Sample settings data for school information
-- Insert school settings into the settings table
-- These keys match the actual database structure

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('school_name', 'IDL Excellence School'),
('address', '123 Education Street, Knowledge City'),
('phone', '+92-300-1234567'),
('school_email', 'info@idlschool.edu'),
('website', 'www.idlschool.edu'),
('tagline', 'Excellence in Education'),
('school_logo', 'images/school-logo.png'),
('default_fine_amount', '100'),
('default_due_days', '30')
ON DUPLICATE KEY UPDATE 
`setting_value` = VALUES(`setting_value`);
