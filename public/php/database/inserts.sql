SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE activities;
TRUNCATE TABLE projects;
TRUNCATE TABLE budgets;
TRUNCATE TABLE transactions;
TRUNCATE TABLE project_members;
TRUNCATE TABLE tasks;
TRUNCATE TABLE user_permissions;
TRUNCATE TABLE enterprise_users;
TRUNCATE TABLE client_previous_projects;
TRUNCATE TABLE client_companies;
TRUNCATE TABLE enterprises;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;

-- Sample enterprise
INSERT INTO enterprises (id, name, type, email, phone, address, nif, sector, employees, revenue, responsible, certificates, country, activity_date, website) VALUES
(1, 'NexoraTech Solutions', 'Technology', 'contact@nexoratech.com', '+1234567890', '123 Tech Street', '123456789', 'Technology', 50, '1000000', 'John Smith', 'ISO 9001', 'United States', '2020-01-01', 'https://nexoratech.com');

-- Users
INSERT INTO users (id, name, email, username, password, is_master) VALUES 
(1, 'Master Admin', 'master@yourdomain.com', 'masteradmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1), 
(2, 'John Smith', 'john@nexoratech.com', 'johnsmith', '$2y$10$XXJlUZi4NpeNWaqb9Ay3uOu8MmEpkdJzZekqAUL8tnnV53qXqwJL.', 0), 
(3, 'Jane Doe', 'jane@nexoratech.com', 'janedoe', '$2y$10$wR1p1wZx7yFJGxfxcNzZ1uWJraCkzY0K6Z4b7T85h5v/Z1U1Q9BZO', 0), 
(4, 'Mark Taylor', 'mark@nexoratech.com', 'marktaylor', '$2y$10$sMpDfGyJ2ZTIkVh7smgk7OTwvxw7lUbebJNWk4tEexjxlPwFz5cpi', 0), 
(5, 'Lisa Ray', 'lisa@nexoratech.com', 'lisaray', '$2y$10$yoIE2X34cW2zGAe2uYgWEeK7kpqJ8LBm2sCEB/LOXynm3kNwPOmsW', 0);

-- Sample enterprise users
INSERT INTO enterprise_users (enterprise_id, name, email, phone, role, username, password, profile, nif, cc, consent, is_admin) VALUES
(1, 'John Smith', 'john@nexoratech.com', '+1234567890', 'CEO', 'johnsmith', '$2y$10$XXJlUZi4NpeNWaqb9Ay3uOu8MmEpkdJzZekqAUL8tnnV53qXqwJL.', 'admin', '123456789', '987654321', 1, 1),
(1, 'Jane Doe', 'jane@nexoratech.com', '+1234567891', 'Developer', 'janedoe', '$2y$10$wR1p1wZx7yFJGxfxcNzZ1uWJraCkzY0K6Z4b7T85h5v/Z1U1Q9BZO', 'user', '223456789', '123123123', 1, 0),
(1, 'Mark Taylor', 'mark@nexoratech.com', '+1234567892', 'Database Admin', 'marktaylor', '$2y$10$sMpDfGyJ2ZTIkVh7smgk7OTwvxw7lUbebJNWk4tEexjxlPwFz5cpi', 'user', '323456789', '456456456', 1, 0),
(1, 'Lisa Ray', 'lisa@nexoratech.com', '+1234567893', 'Backend Developer', 'lisaray', '$2y$10$yoIE2X34cW2zGAe2uYgWEeK7kpqJ8LBm2sCEB/LOXynm3kNwPOmsW', 'user', '423456789', '789789789', 1, 0);

-- Projects
INSERT INTO projects (enterprise_id, name, description, status, start_date, end_date) VALUES
(1, 'Website Redesign', 'Complete overhaul of company website', 'active', '2024-01-01', '2024-06-30'),
(1, 'Mobile App Development', 'New mobile application for clients', 'active', '2024-02-15', '2024-08-15'),
(1, 'Marketing Campaign', 'Q2 marketing campaign', 'upcoming', '2024-04-01', '2024-06-30'),
(1, 'Office Renovation', 'Main office space renovation', 'completed', '2023-10-01', '2024-01-31');

-- Budgets
INSERT INTO budgets (enterprise_id, amount, description) VALUES
(1, 250000.00, 'Annual Budget 2024'),
(1, 50000.00, 'Q1 Marketing Budget'),
(1, 100000.00, 'IT Infrastructure Budget');

-- Transactions
INSERT INTO transactions (enterprise_id, type, amount, description, date) VALUES
(1, 'revenue', 50000.00, 'Client Project Payment', '2024-03-01'),
(1, 'revenue', 35000.00, 'Monthly Subscription Revenue', '2024-03-15'),
(1, 'revenue', 45000.00, 'Consulting Services', '2024-02-01'),
(1, 'revenue', 40000.00, 'Client Project Payment', '2024-02-15'),
(1, 'revenue', 55000.00, 'Monthly Subscription Revenue', '2024-01-01'),
(1, 'revenue', 30000.00, 'Consulting Services', '2024-01-15'),
(1, 'expense', 15000.00, 'Office Rent', '2024-03-01'),
(1, 'expense', 8000.00, 'Software Licenses', '2024-03-10'),
(1, 'expense', 12000.00, 'Team Training', '2024-02-05'),
(1, 'expense', 10000.00, 'Marketing Campaign', '2024-02-20'),
(1, 'expense', 15000.00, 'Office Rent', '2024-01-01'),
(1, 'expense', 5000.00, 'Equipment Purchase', '2024-01-15');

-- Activities
INSERT INTO activities (enterprise_id, title, description, icon) VALUES
(1, 'New Project Started', 'Website Redesign project has been initiated', 'project-diagram'),
(1, 'Budget Updated', 'Q1 marketing budget has been adjusted', 'dollar-sign'),
(1, 'Team Member Added', 'John Doe joined the development team', 'user-plus'),
(1, 'Project Completed', 'Office renovation project finished successfully', 'check-circle'),
(1, 'New Revenue', 'Received payment for consulting services', 'chart-line');

-- Permissions
INSERT INTO user_permissions (user_id, permission) VALUES
(1, 'manage_projects'),
(1, 'manage_employees'),
(1, 'manage_budgets'),
(1, 'manage_reports'),
(1, 'delete_project'),
(1, 'delete_employee'),
(1, 'archive_project'),
(1, 'archive_employee');

-- Tasks
INSERT INTO tasks (project_id, title, description, status) VALUES
(1, 'Design Homepage', 'Create the homepage design', 'completed'),
(1, 'Implement Navigation', 'Implement the navigation bar', 'pending'),
(2, 'Setup Database', 'Setup the database schema', 'completed'),
(2, 'Create API Endpoints', 'Create the necessary API endpoints', 'pending');

-- Project Members
INSERT INTO project_members (project_id, user_id, role) VALUES
(1, 1, 'Designer'),
(1, 2, 'Developer'),
(2, 3, 'Database Admin'),
(2, 4, 'Backend Developer');

-- Fake Revenue/Expenses (últimos 6 meses)
INSERT INTO transactions (enterprise_id, type, amount, description, date) VALUES
(1, 'revenue', 52000.00, 'Fake Revenue Nov',   DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m-01')),
(1, 'expense', 12000.00, 'Fake Expense Nov',   DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m-10')),

(1, 'revenue', 61000.00, 'Fake Revenue Dec',   DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 4 MONTH), '%Y-%m-01')),
(1, 'expense', 15000.00, 'Fake Expense Dec',   DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 4 MONTH), '%Y-%m-10')),

(1, 'revenue', 57000.00, 'Fake Revenue Jan',   DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 3 MONTH), '%Y-%m-01')),
(1, 'expense', 11000.00, 'Fake Expense Jan',   DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 3 MONTH), '%Y-%m-10')),

(1, 'revenue', 68000.00, 'Fake Revenue Feb',   DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m-01')),
(1, 'expense', 14000.00, 'Fake Expense Feb',   DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m-10')),

(1, 'revenue', 63000.00, 'Fake Revenue Mar',   DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01')),
(1, 'expense', 13000.00, 'Fake Expense Mar',   DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-10')),

(1, 'revenue', 70000.00, 'Fake Revenue Apr',   DATE_FORMAT(CURDATE(), '%Y-%m-01')),
(1, 'expense', 16000.00, 'Fake Expense Apr',   DATE_FORMAT(CURDATE(), '%Y-%m-10'));

-- Atualizar a empresa existente para ser pública
UPDATE client_companies SET is_public = 1 WHERE id = 1;

-- Depois, garantir que as client_companies existem:
INSERT INTO client_companies (id, user_id, name, website, is_public, description) VALUES
(2, 2, 'Tech Solutions', 'https://techsolutions.com', 1, 'Empresa de tecnologia'),
(3, 2, 'Design Studio', 'https://designstudio.com', 1, 'Estúdio de design'),
(4, 3, 'Marketing Pro', 'https://marketingpro.com', 1, 'Agência de marketing');

-- Agora sim, os projetos:
INSERT INTO client_previous_projects (user_id, name, website, image, company_id, description, is_public) VALUES
(2, 'E-commerce Platform', 'https://techsolutions.com/ecommerce', NULL, 2, 'Complete e-commerce platform.', 1),
(2, 'Brand Identity', 'https://designstudio.com/brand', NULL, 3, 'Visual identity development.', 1),
(3, 'Social Media Campaign', 'https://marketingpro.com/campaign', NULL, 4, 'Digital marketing campaign.', 1);
