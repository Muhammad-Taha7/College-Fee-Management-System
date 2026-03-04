-- College Fee Management System Database
-- Created: 2026-03-04

CREATE DATABASE IF NOT EXISTS college_fee_db;
USE college_fee_db;

-- Students Table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    father_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20) NOT NULL,
    course VARCHAR(100) NOT NULL,
    semester INT NOT NULL DEFAULT 1,
    admission_date DATE NOT NULL,
    address TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Fee Structure Table
CREATE TABLE IF NOT EXISTS fee_structure (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course VARCHAR(100) NOT NULL,
    semester INT NOT NULL,
    tuition_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
    exam_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
    library_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
    lab_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
    other_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
    total_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Payments Table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL,
    receipt_no VARCHAR(30) UNIQUE NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method ENUM('cash', 'online', 'cheque') DEFAULT 'cash',
    semester INT NOT NULL,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
);

-- Insert Sample Fee Structures
INSERT INTO fee_structure (course, semester, tuition_fee, exam_fee, library_fee, lab_fee, other_fee, total_fee) VALUES
('BCA', 1, 15000, 2000, 1000, 2000, 500, 20500),
('BCA', 2, 15000, 2000, 1000, 2000, 500, 20500),
('BCA', 3, 16000, 2000, 1000, 2500, 500, 22000),
('BCA', 4, 16000, 2000, 1000, 2500, 500, 22000),
('BCA', 5, 17000, 2500, 1000, 3000, 500, 24000),
('BCA', 6, 17000, 2500, 1000, 3000, 500, 24000),
('MCA', 1, 25000, 3000, 1500, 3000, 1000, 33500),
('MCA', 2, 25000, 3000, 1500, 3000, 1000, 33500),
('MCA', 3, 26000, 3000, 1500, 3500, 1000, 35000),
('MCA', 4, 26000, 3000, 1500, 3500, 1000, 35000),
('BSc IT', 1, 12000, 1500, 800, 1500, 500, 16300),
('BSc IT', 2, 12000, 1500, 800, 1500, 500, 16300),
('BSc IT', 3, 13000, 1500, 800, 2000, 500, 17800),
('BSc IT', 4, 13000, 1500, 800, 2000, 500, 17800),
('BSc IT', 5, 14000, 2000, 1000, 2000, 500, 19500),
('BSc IT', 6, 14000, 2000, 1000, 2000, 500, 19500),
('MBA', 1, 30000, 3000, 1500, 1000, 1500, 37000),
('MBA', 2, 30000, 3000, 1500, 1000, 1500, 37000),
('MBA', 3, 32000, 3500, 1500, 1000, 1500, 39500),
('MBA', 4, 32000, 3500, 1500, 1000, 1500, 39500);

-- Insert Sample Students
INSERT INTO students (student_id, name, father_name, email, phone, course, semester, admission_date, address) VALUES
('STU2026001', 'Rahul Sharma', 'Rajesh Sharma', 'rahul@email.com', '9876543210', 'BCA', 3, '2025-07-01', 'Delhi, India'),
('STU2026002', 'Priya Verma', 'Suresh Verma', 'priya@email.com', '9876543211', 'MCA', 1, '2026-01-15', 'Mumbai, India'),
('STU2026003', 'Amit Kumar', 'Ramesh Kumar', 'amit@email.com', '9876543212', 'BSc IT', 2, '2025-07-10', 'Pune, India'),
('STU2026004', 'Sneha Patel', 'Dinesh Patel', 'sneha@email.com', '9876543213', 'MBA', 1, '2026-01-20', 'Ahmedabad, India'),
('STU2026005', 'Vikram Singh', 'Harpal Singh', 'vikram@email.com', '9876543214', 'BCA', 5, '2024-07-05', 'Jaipur, India');

-- Insert Sample Payments
INSERT INTO payments (student_id, receipt_no, amount, payment_date, payment_method, semester, remarks) VALUES
('STU2026001', 'RCP2026001', 22000, '2026-01-15', 'cash', 3, 'Semester 3 full payment'),
('STU2026002', 'RCP2026002', 33500, '2026-01-20', 'online', 1, 'Semester 1 full payment'),
('STU2026003', 'RCP2026003', 10000, '2026-02-01', 'cash', 2, 'Partial payment'),
('STU2026004', 'RCP2026004', 37000, '2026-02-10', 'cheque', 1, 'Full payment'),
('STU2026005', 'RCP2026005', 24000, '2026-02-15', 'online', 5, 'Full payment'),
('STU2026001', 'RCP2026006', 20500, '2025-07-20', 'cash', 1, 'Semester 1 full payment'),
('STU2026001', 'RCP2026007', 20500, '2025-12-10', 'online', 2, 'Semester 2 full payment'),
('STU2026003', 'RCP2026008', 6300, '2026-02-20', 'cash', 2, 'Remaining payment');
