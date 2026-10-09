<?php
// config/db.php
require_once __DIR__ . '/../classes/Database.php';

$user = 'root';
$pass = '';

$bootstrap = new PDO('mysql:host=localhost;charset=utf8mb4', $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$bootstrap->exec('CREATE DATABASE IF NOT EXISTS training_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

$dsn = 'mysql:host=localhost;dbname=training_db;charset=utf8mb4';
$db = Database::getInstance($dsn, $user, $pass);

$requiredTables = [
    'students' => "CREATE TABLE IF NOT EXISTS students (
        student_id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    'courses' => "CREATE TABLE IF NOT EXISTS courses (
        course_id INT AUTO_INCREMENT PRIMARY KEY,
        course_code VARCHAR(10) NOT NULL UNIQUE,
        course_name VARCHAR(100) NOT NULL,
        description TEXT
    )",
    'classes' => "CREATE TABLE IF NOT EXISTS classes (
        class_id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT NOT NULL,
        class_code VARCHAR(10) NOT NULL,
        schedule VARCHAR(50),
        instructor VARCHAR(50),
        slots INT NOT NULL DEFAULT 0,
        FOREIGN KEY (course_id) REFERENCES courses(course_id)
    )",
    'enrollments' => "CREATE TABLE IF NOT EXISTS enrollments (
        enrollment_id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        class_id INT NOT NULL,
        enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status VARCHAR(20) DEFAULT 'active',
        FOREIGN KEY (student_id) REFERENCES students(student_id),
        FOREIGN KEY (class_id) REFERENCES classes(class_id)
    )",
];

foreach ($requiredTables as $sql) {
    $db->exec($sql);
}