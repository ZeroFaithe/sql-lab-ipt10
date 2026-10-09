<?php
// admin/enrollments.php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/EnrollmentRepository.php';

$repo = new EnrollmentRepository($db);

// Fetch all enrollments with student, class, and course details
$rows = $repo->allWithDetails();

$message = '';

// Handle cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['enrollment_id'])) {

    $enrollment_id = (int) $_POST['enrollment_id'];

    if ($enrollment_id > 0 && $repo->cancel($enrollment_id)) {
        $message = 'Enrollment cancelled successfully!';
    } else {
        $message = 'Cancellation failed.';
    }

    // Refresh the enrollment list after cancellation
    $rows = $repo->allWithDetails();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollments</title>
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
        <h1>All Enrollments</h1>

        <?php if ($message !== ''): ?>
            <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <table border="1" cellpadding="8">
            <thead>
                <tr>
                    <th>Enrollment ID</th>
                    <th>Student</th>
                    <th>Class ID</th>
                    <th>Course</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="6">No enrollments found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><?= (int) $row['enrollment_id'] ?></td>
                            <td>
                                <?= htmlspecialchars(
                                    $row['full_name'] ?? 'Unknown',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>
                            <td><?= (int) $row['class_id'] ?></td>
                            <td>
                                <?= htmlspecialchars(
                                    $row['course_name'] ?? $row['name'] ?? 'Unknown',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars(
                                    $row['status'] ?? 'active',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>
                            <td>
                                <?php if (($row['status'] ?? 'active') !== 'cancelled'): ?>
                                    <form method="POST">
                                        <input
                                            type="hidden"
                                            name="enrollment_id"
                                            value="<?= (int) $row['enrollment_id'] ?>"
                                        >
                                        <button type="submit">Cancel</button>
                                    </form>
                                <?php else: ?>
                                    Already cancelled
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>