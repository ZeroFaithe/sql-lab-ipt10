<?php
// admin/classes.php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/ClassSection.php';

$classesRepo = new ClassSection($db);
$message = '';

// Fetch courses for the dropdown
$stmt = $db->query(
    "SELECT course_id, course_name FROM courses ORDER BY course_name"
);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle new class submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = (int) ($_POST['course_id'] ?? 0);
    $code = trim($_POST['code'] ?? '');
    $schedule = trim($_POST['schedule'] ?? '');
    $instructor = trim($_POST['instructor'] ?? '');
    $slots = filter_var(
        $_POST['slots'] ?? null,
        FILTER_VALIDATE_INT
    );

    if (
        $course_id <= 0 ||
        $code === '' ||
        $schedule === '' ||
        $instructor === '' ||
        $slots === false ||
        $slots === null ||
        $slots < 0
    ) {
        $message = 'Please enter valid information in every field.';
    } else {
        try {
            $newId = $classesRepo->create(
                $course_id,
                $code,
                $schedule,
                $instructor,
                $slots
            );

            $message = 'Class section created successfully!';
        } catch (PDOException $e) {
            $message = 'Could not create the class section.';
        }
    }
}

// Fetch class list after processing the form
$classList = $classesRepo->allWithCourse();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Classes</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <nav>
        <a href="../index.php">Home</a> |
        <a href="courses.php">Courses</a> |
        <a href="classes.php">Classes</a> |
        <a href="students.php">Record Student</a> |
        <a href="enroll.php">Enroll</a> |
        <a href="enrollments.php">Enrollments</a>
    </nav>

    <main>
        <h1>Manage Classes</h1>

        <?php if ($message !== ''): ?>
            <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <h2>Add a Class Section</h2>

        <form method="POST">
            <label for="course_id">Course:</label>
            <select name="course_id" id="course_id" required>
                <option value="">-- Select a course --</option>

                <?php foreach ($courses as $course): ?>
                    <option value="<?= (int) $course['course_id'] ?>">
                        <?= htmlspecialchars(
                            $course['course_name'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <br><br>

            <label for="code">Class Code:</label>
            <input type="text" name="code" id="code" required>

            <br><br>

            <label for="schedule">Schedule:</label>
            <input type="text" name="schedule" id="schedule" required>

            <br><br>

            <label for="instructor">Instructor:</label>
            <input type="text" name="instructor" id="instructor" required>

            <br><br>

            <label for="slots">Available Slots:</label>
            <input type="number" name="slots" id="slots" min="0" required>

            <br><br>

            <button type="submit">Create Class</button>
        </form>

        <h2>Existing Classes</h2>

        <table border="1" cellpadding="8">
            <thead>
                <tr>
                    <th>Class ID</th>
                    <th>Course</th>
                    <th>Class Code</th>
                    <th>Schedule</th>
                    <th>Instructor</th>
                    <th>Available Slots</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($classList)): ?>
                    <tr>
                        <td colspan="6">No classes found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($classList as $class): ?>
                        <tr>
                            <td><?= (int) $class['class_id'] ?></td>
                            <td>
                                <?= htmlspecialchars(
                                    $class['course_name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars(
                                    $class['class_code'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars(
                                    $class['schedule'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars(
                                    $class['instructor'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>
                            <td><?= (int) $class['slots'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>