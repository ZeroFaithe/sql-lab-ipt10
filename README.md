# IPT10 Training Enrollment System

A simple PHP/MySQL web app for managing course enrollments.

## Requirements

- XAMPP (Apache + MySQL + PHP 8+)

## Setup

1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Place the project folder inside `C:/xampp/htdocs/`.
3. Open your browser and go to:

`
http://localhost/IPT10/IPT10 Lab Act SQL/
`

The database (`training_db`) and all tables are created automatically on first load.

## Pages

| Page | URL |
|---|---|
| Home | `/index.php` |
| Courses | `/admin/courses.php` |
| Classes | `/admin/classes.php` |
| Record Student | `/admin/students.php` |
| Enroll | `/admin/enroll.php` |
| Enrollments | `/admin/enrollments.php` |
| Reports | `/admin/reports.php` |

## Design Pattern

This project uses the **Singleton** pattern (GoF — *Creational*) in `classes/Database.php`.

`Database::getInstance()` ensures only one PDO connection is created for the entire request, preventing redundant connections to MySQL.
