<?php
// admin/enroll.php -- Enroll an EXISTING student into a class

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/EnrollmentRepository.php';
require_once __DIR__ . '/../classes/ClassSection.php';

$repo = new EnrollmentRepository($db);
$message = '';

// Load students and classes for the dropdowns
$stmt = $db->query(
    "SELECT student_id, full_name FROM students ORDER BY full_name"
);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

$classesRepo = new ClassSection($db);
$classList = $classesRepo->allWithCourse();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = (int) ($_POST['student_id'] ?? 0);
    $class_id = (int) ($_POST['class_id'] ?? 0);

    // Validate IDs
    if ($student_id <= 0 || $class_id <= 0) {
        $message = 'Please select a valid student and class.';
    } else {
        // Enroll the existing student
        $result = $repo->enroll($student_id, $class_id);

        if ($result !== false) {
            $message = 'Student successfully enrolled!';
        } else {
            $message = 'Enrollment failed or no slots are available.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enroll Existing Student</title>
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
        <h1>Enroll an Existing Student</h1>

        <?php if ($message !== ''): ?>
            <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="student_id">Student:</label>
            <select id="student_id" name="student_id" required>
                <option value="">-- Select a student --</option>

                <?php foreach ($students as $student): ?>
                    <option value="<?= (int) $student['student_id'] ?>">
                        <?= htmlspecialchars(
                            $student['full_name'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <br><br>

            <label for="class_id">Class:</label>
            <select id="class_id" name="class_id" required>
                <option value="">-- Select a class --</option>

                <?php foreach ($classList as $class): ?>
                    <option value="<?= (int) $class['class_id'] ?>">
                        <?= htmlspecialchars(
                            $class['course_name'] ?? $class['name'] ?? 'Class',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                        (Slots: <?= (int) $class['slots'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>

            <br><br>

            <button type="submit">Enroll Student</button>
        </form>
    </main>
</body>
</html>