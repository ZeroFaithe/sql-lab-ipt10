<?php
 // admin/students.php -- Student recording form
 require_once __DIR__ . '/../config/db.php';
 require_once __DIR__ . '/../classes/EnrollmentRepository.php';
 require_once __DIR__ . '/../classes/ClassSection.php';

 $repo = new EnrollmentRepository($db);
 $classes = new ClassSection($db);
 $classList = $classes->allWithCourse(); // for the class dropdown
 $message = '';

 if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 $full_name = trim($_POST['full_name'] ?? '');
 $email = trim($_POST['email'] ?? '');
 $phone = trim($_POST['phone'] ?? '');
 $class_id = (int) ($_POST['class_id'] ?? 0);

 // TODO: validate inputs (non-empty name, valid class_id, ...)
 // TODO: call $repo->recordStudent($full_name, $email, $phone, $class_id)
 // That single call must INSERT the student, INSERT the enrollment
 // and decrement the class slots inside ONE transaction.
 // TODO: show a success message, or "no slots available" on failure
 // FILL IN CODE HERE
if ($full_name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)
        || $phone === '' || $class_id <= 0) {
        $message = 'Please enter valid information in all fields.';
    } else {
        $result = $repo->recordStudent(
            $full_name,
            $email,
            $phone,
            $class_id
        );

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
    <title>Record Student</title>
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
        <h1>Record a Student</h1>

        <?php if ($message !== ''): ?>
            <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="full_name">Full Name:</label>
            <input
                type="text"
                id="full_name"
                name="full_name"
                required
            >

            <br><br>

            <label for="email">Email:</label>
            <input
                type="email"
                id="email"
                name="email"
                required
            >

            <br><br>

            <label for="phone">Phone:</label>
            <input
                type="text"
                id="phone"
                name="phone"
                required
            >

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
