CREATE DATABASE IF NOT EXISTS training_db;
 USE training_db;

 CREATE TABLE students (
 student_id INT AUTO_INCREMENT PRIMARY KEY,
 full_name VARCHAR(100) NOT NULL,
 email VARCHAR(100),
 phone VARCHAR(20),
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
 );

 CREATE TABLE courses (
 course_id INT AUTO_INCREMENT PRIMARY KEY,
 course_code VARCHAR(10) NOT NULL UNIQUE,
 course_name VARCHAR(100) NOT NULL,
 description TEXT
 );

 CREATE TABLE classes (
 class_id INT AUTO_INCREMENT PRIMARY KEY,
 course_id INT NOT NULL,
 class_code VARCHAR(10) NOT NULL,
 schedule VARCHAR(50),
 instructor VARCHAR(50),
 slots INT NOT NULL DEFAULT 0,
 FOREIGN KEY (course_id) REFERENCES courses(course_id)
 );

 CREATE TABLE enrollments (
 enrollment_id INT AUTO_INCREMENT PRIMARY KEY,
 student_id INT NOT NULL,
 class_id INT NOT NULL,
 enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 status VARCHAR(20) DEFAULT 'active',
 FOREIGN KEY (student_id) REFERENCES students(student_id),
 FOREIGN KEY (class_id) REFERENCES classes(class_id)
 );