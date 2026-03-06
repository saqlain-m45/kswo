CREATE DATABASE IF NOT EXISTS kswo_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kswo_db;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    father_name VARCHAR(120) NOT NULL,
    gender ENUM('Male','Female','Other') NOT NULL,
    ethnicity VARCHAR(100) NOT NULL,
    dob DATE NOT NULL,
    cnic VARCHAR(15) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(120) NOT NULL UNIQUE,
    cnic_front_image_path VARCHAR(255) DEFAULT NULL,
    student_card_front_image_path VARCHAR(255) DEFAULT NULL,
    designation VARCHAR(100) NOT NULL DEFAULT 'Member',
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user','admin','super_admin') NOT NULL DEFAULT 'user',
    membership_status ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS donations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('Easypaisa','JazzCash','Bank') NOT NULL,
    payment_status ENUM('paid','pending','failed') NOT NULL DEFAULT 'paid',
    is_monthly TINYINT(1) NOT NULL DEFAULT 0,
    transaction_id VARCHAR(64) NOT NULL UNIQUE,
    receipt_path VARCHAR(255) DEFAULT NULL,
    donated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS public_donations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    donor_name VARCHAR(120) NOT NULL,
    email VARCHAR(120) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('Easypaisa','JazzCash','Bank') NOT NULL,
    payment_status ENUM('paid','pending','failed') NOT NULL DEFAULT 'paid',
    transaction_id VARCHAR(64) NOT NULL UNIQUE,
    receipt_path VARCHAR(255) DEFAULT NULL,
    message VARCHAR(255) DEFAULT NULL,
    donated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS payment_accounts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    method ENUM('Easypaisa','JazzCash','Bank') NOT NULL,
    account_title VARCHAR(120) NOT NULL,
    account_holder VARCHAR(120) DEFAULT NULL,
    account_number VARCHAR(80) NOT NULL,
    icon_path VARCHAR(255) DEFAULT NULL,
    branch_info VARCHAR(180) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS presidents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    duration VARCHAR(30) NOT NULL,
    description TEXT NOT NULL,
    photo_path VARCHAR(255) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    message VARCHAR(255) NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value VARCHAR(255) NOT NULL
);

INSERT INTO users (full_name, father_name, gender, ethnicity, dob, cnic, phone, email, designation, password_hash, role, membership_status)
VALUES
('Admin User', 'System', 'Male', 'Khattak', '1990-01-01', '99999-9999999-9', '+92-300-0000000', 'admin@kswo.org', 'Administrator', '$2y$10$UPN6y29xh7jFkljKcvao7uwZDFjsmA6HA.3DB5CtF0sgZZbfwofX6', 'admin', 'verified'),
('Demo Member', 'Demo Father', 'Male', 'Khattak', '2000-01-01', '12345-1234567-1', '+92-300-1111111', 'member@kswo.org', 'Member', '$2y$10$UPN6y29xh7jFkljKcvao7uwZDFjsmA6HA.3DB5CtF0sgZZbfwofX6', 'user', 'pending'),
('System Super Admin', 'System', 'Male', 'Khattak', '1988-01-01', '88888-8888888-8', '+92-300-8888888', 'superadmin@kswo.org', 'Super Administrator', '$2y$10$UPN6y29xh7jFkljKcvao7uwZDFjsmA6HA.3DB5CtF0sgZZbfwofX6', 'super_admin', 'verified')
ON DUPLICATE KEY UPDATE
    full_name = VALUES(full_name),
    designation = VALUES(designation),
    phone = VALUES(phone),
    password_hash = VALUES(password_hash),
    role = VALUES(role),
    membership_status = VALUES(membership_status);

INSERT INTO donations (user_id, amount, payment_method, payment_status, is_monthly, transaction_id, donated_at)
SELECT 2, 2000, 'Easypaisa', 'paid', 1, 'KSWO-20260201-0001', '2026-02-01 10:00:00'
WHERE NOT EXISTS (SELECT 1 FROM donations WHERE transaction_id = 'KSWO-20260201-0001');

INSERT INTO donations (user_id, amount, payment_method, payment_status, is_monthly, transaction_id, donated_at)
SELECT 2, 2000, 'JazzCash', 'paid', 1, 'KSWO-20260101-0001', '2026-01-01 10:00:00'
WHERE NOT EXISTS (SELECT 1 FROM donations WHERE transaction_id = 'KSWO-20260101-0001');

INSERT INTO public_donations (donor_name, email, phone, amount, payment_method, payment_status, transaction_id, message, donated_at)
SELECT 'Community Donor', 'public@donor.com', '+92-300-2222222', 5000, 'Easypaisa', 'paid', 'KSWO-PUB-20260210-001', 'Support for students', '2026-02-10 14:30:00'
WHERE NOT EXISTS (SELECT 1 FROM public_donations WHERE transaction_id = 'KSWO-PUB-20260210-001');

INSERT INTO payment_accounts (method, account_title, account_holder, account_number, branch_info, is_active, sort_order)
SELECT 'Easypaisa', 'KSWO Welfare Easypaisa', 'KSWO', '03001234567', 'Use this number for Easypaisa transfer', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM payment_accounts WHERE method='Easypaisa' AND account_number='03001234567');

INSERT INTO payment_accounts (method, account_title, account_holder, account_number, branch_info, is_active, sort_order)
SELECT 'JazzCash', 'KSWO Welfare JazzCash', 'KSWO', '03007654321', 'Use this number for JazzCash transfer', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM payment_accounts WHERE method='JazzCash' AND account_number='03007654321');

INSERT INTO payment_accounts (method, account_title, account_holder, account_number, branch_info, is_active, sort_order)
SELECT 'Bank', 'KSWO Welfare Bank Account', 'KSWO Organization', '0210-1234-5678-90', 'Meezan Bank, Kohat Branch', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM payment_accounts WHERE method='Bank' AND account_number='0210-1234-5678-90');

INSERT INTO presidents (name, duration, description, sort_order)
SELECT 'Sajid Khattak', '2018 - 2019', 'Launched the first merit-based student support model.', 1
WHERE NOT EXISTS (SELECT 1 FROM presidents WHERE name='Sajid Khattak' AND duration='2018 - 2019');

INSERT INTO presidents (name, duration, description, sort_order)
SELECT 'Hamza Khan', '2020 - 2021', 'Introduced digital member registration and verification.', 2
WHERE NOT EXISTS (SELECT 1 FROM presidents WHERE name='Hamza Khan' AND duration='2020 - 2021');

INSERT INTO presidents (name, duration, description, sort_order)
SELECT 'Usman Ali', '2022 - 2023', 'Expanded monthly donor base and emergency aid program.', 3
WHERE NOT EXISTS (SELECT 1 FROM presidents WHERE name='Usman Ali' AND duration='2022 - 2023');

INSERT INTO presidents (name, duration, description, sort_order)
SELECT 'Arif Mehmood', '2024 - 2025', 'Started public transparency reporting and governance reforms.', 4
WHERE NOT EXISTS (SELECT 1 FROM presidents WHERE name='Arif Mehmood' AND duration='2024 - 2025');

INSERT INTO notifications (user_id, message)
SELECT 2, 'Your profile verification is in progress.'
WHERE NOT EXISTS (SELECT 1 FROM notifications WHERE user_id = 2 AND message = 'Your profile verification is in progress.');

INSERT INTO settings (setting_key, setting_value)
VALUES
('session_timeout_minutes', '30'),
('payment_trust_badge', 'Yes'),
('notification_email', 'admin@kswo.org'),
('default_currency', 'PKR')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
