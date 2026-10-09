
<?php
// admin/courses.php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/Course.php';

$courseRepo = new Course($db);
$message = '';
$editCourse = null;

// Handle edit request
if (isset($_GET['edit'])) {
    $editId = filter_var($_GET['edit'], FILTER_VALIDATE_INT);

    if ($editId && $editId > 0) {
        $editCourse = $courseRepo->find($editId);
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'create' || $action === 'update') {
            $code = trim($_POST['code'] ?? '');
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if ($code === '' || $name === '' || $description === '') {
                $message = 'Please fill in all fields.';
            } elseif ($action === 'create') {
                $courseRepo->create($code, $name, $description);
                header('Location: courses.php?message=created');
                exit;
            } else {
                $id = filter_var(
                    $_POST['course_id'] ?? null,
                    FILTER_VALIDATE_INT
                );

                if (!$id || $id <= 0) {
                    $message = 'Invalid course ID.';
                } else {
                    $courseRepo->update($id, $code, $name, $description);
                    header('Location: courses.php?message=updated');
                    exit;
                }
            }
        } elseif ($action === 'delete') {
            $id = filter_var(
                $_POST['course_id'] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!$id || $id <= 0) {
                $message = 'Invalid course ID.';
            } elseif ($courseRepo->delete($id)) {
                header('Location: courses.php?message=deleted');
                exit;
            } else {
                $message = 'Could not delete the course.';
            }
        }
    } catch (PDOException $e) {
        $message = 'Database operation failed. The course may be in use.';
    }
}

// Show messages after redirect
if (isset($_GET['message'])) {
    $messages = [
        'created' => 'Course created successfully!',
        'updated' => 'Course updated successfully!',
        'deleted' => 'Course deleted successfully!'
    ];

    $message = $messages[$_GET['message']] ?? '';
}

// Load all courses
$courses = $courseRepo->all();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses</title>
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
        <h1>Manage Courses</h1>

        <?php if ($message !== ''): ?>
            <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <h2>
            <?= $editCourse ? 'Edit Course' : 'Add a Course' ?>
        </h2>

        <form method="POST" action="courses.php">
            <input
                type="hidden"
                name="action"
                value="<?= $editCourse ? 'update' : 'create' ?>"
            >

            <?php if ($editCourse): ?>
                <input
                    type="hidden"
                    name="course_id"
                    value="<?= (int) ($editCourse['course_id'] ?? 0) ?>"
                >
            <?php endif; ?>

            <label for="code">Course Code:</label>
            <input
                type="text"
                id="code"
                name="code"
                required
                value="<?= htmlspecialchars($editCourse['course_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            >

            <br><br>

            <label for="name">Course Name:</label>
            <input
                type="text"
                id="name"
                name="name"
                required
                value="<?= htmlspecialchars($editCourse['course_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            >

            <br><br>

            <label for="description">Description:</label>
            <textarea
                id="description"
                name="description"
                required
            ><?= htmlspecialchars($editCourse['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>

            <br><br>

            <button type="submit">
                <?= $editCourse ? 'Update Course' : 'Add Course' ?>
            </button>

            <?php if ($editCourse): ?>
                <a href="courses.php">Cancel Edit</a>
            <?php endif; ?>
        </form>

        <hr>

        <h2>Course List</h2>

        <table border="1" cellpadding="8">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Course Code</th>
                    <th>Course Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($courses)): ?>
                    <tr>
                        <td colspan="5">No courses found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($courses as $course): ?>
                        <tr>
                            <td><?= (int) ($course['course_id'] ?? 0) ?></td>

                            <td>
                                <?= htmlspecialchars($course['course_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($course['course_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($course['description'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <td>
                                <a href="courses.php?edit=<?= (int) ($course['course_id'] ?? 0) ?>">
                                    Edit
                                </a>

                                <form method="POST" action="courses.php"
                                      onsubmit="return confirm('Delete this course?');"
                                      style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input
                                        type="hidden"
                                        name="course_id"
                                        value="<?= (int) ($course['course_id'] ?? 0) ?>"
                                    >
                                    <button type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>