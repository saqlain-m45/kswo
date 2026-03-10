CREATE DATABASE IF NOT EXISTS kswo_db;
USE kswo_db;

-- Table for users (Members)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    father_name VARCHAR(100) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    ethnicity VARCHAR(50),
    dob DATE,
    cnic VARCHAR(15) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for admins
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('superadmin', 'admin') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for donations
CREATE TABLE IF NOT EXISTS donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    amount DECIMAL(10, 2) NOT NULL,
    method ENUM('Easypaisa', 'JazzCash') NOT NULL,
    reference_number VARCHAR(100),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    is_subscription BOOLEAN DEFAULT FALSE,
    donation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Table for past presidents
CREATE TABLE IF NOT EXISTS presidents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    duration VARCHAR(100) NOT NULL,
    description TEXT,
    photo_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert a default admin (password: admin123)
-- In a real scenario, this would be hashed. I'll use password_hash in PHP later.
-- For now, this is just a placeholder to ensure the table works.
INSERT INTO admins (username, email, password, role) 
VALUES ('admin', 'admin@kswo.org', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin');
-- Table for donation spending records
CREATE TABLE IF NOT EXISTS spend_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    area VARCHAR(255),
    amount DECIMAL(10, 2) NOT NULL,
    spend_date DATE NOT NULL,
    attachment_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
