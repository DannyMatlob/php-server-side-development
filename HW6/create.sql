DROP DATABASE clientValidation;
CREATE DATABASE IF NOT EXISTS clientValidation;
USE clientValidation;

CREATE TABLE IF NOT EXISTS students (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    student_id CHAR(9) NOT NULL,
    email VARCHAR(100) NOT NULL,
    hash CHAR(60) NOT NULL
);

CREATE TABLE advisors (
    advisor_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone CHAR(10) NOT NULL,
    student_id_lower CHAR(2) NOT NULL,
    student_id_upper CHAR(2) NOT NULL
);

-- Insert statement for counselors covering all ranges
INSERT INTO advisors (advisor_id, full_name, email, phone, student_id_lower, student_id_upper)
VALUES 
    ('C001', 'Bob Smith', 'counselor_001@example.com', '4085189804', '00', '33'),
    ('C001', 'Bob Smith2', 'counselor_001@example.com', '4085189804', '00', '33'),
    ('C001', 'Bob Smith3', 'counselor_001@example.com', '4085189804', '00', '33'),
    ('C034', 'Danny Matlob', 'counselor_034@example.com', '4085189804','34', '66'),
    ('C034', 'Danny Matlob2', 'counselor_034@example.com', '4085189804','34', '66'),
    ('C034', 'Danny Matlob3', 'counselor_034@example.com', '4085189804','34', '66'),
    ('C067', 'Blud Weiser', 'counselor_067@example.com', '4085189804','67', '99'),
    ('C067', 'Blud Weiser2', 'counselor_067@example.com', '4085189804','67', '99'),
    ('C067', 'Blud Weiser3', 'counselor_067@example.com', '4085189804','67', '99');
