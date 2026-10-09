<?php
//admin/reports.php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/EnrollmentRepository.php';
require_once __DIR__ . '/../classes/ClassSection.php';
require_once __DIR__ . '/../classes/Student.php';
require_once __DIR__ . '/../classes/Course.php';

$enrollRepo  = new EnrollmentRepository($db);
$classRepo   = new ClassSection($db);
$studentRepo = new Student($db);
$courseRepo  = new Course($db);


$allEnrollments = $enrollRepo->allWithDetails();


$allClasses = $classRepo->allWithCourse();

$allStudents = $studentRepo->all();


$totalStudents    = count($allStudents);
$totalEnrollments = count($allEnrollments);

$activeCount    = 0;
$cancelledCount = 0;
foreach ($allEnrollments as $row) {
    if (($row['status'] ?? 'active') === 'cancelled') {
        $cancelledCount++;
    } else {
        $activeCount++;
    }
}


$totalSlots = array_sum(array_column($allClasses, 'slots'));

$byCourse = [];
foreach ($allEnrollments as $row) {
    $key = $row['course_name'] ?? 'Unknown';
    if (!isset($byCourse[$key])) {
        $byCourse[$key] = ['active' => 0, 'cancelled' => 0, 'code' => $row['course_code'] ?? ''];
    }
    if (($row['status'] ?? 'active') === 'cancelled') {
        $byCourse[$key]['cancelled']++;
    } else {
        $byCourse[$key]['active']++;
    }
}

$statusFilter = $_GET['status'] ?? 'all';
$filtered = array_filter($allEnrollments, function ($row) use ($statusFilter) {
    if ($statusFilter === 'all') return true;
    return ($row['status'] ?? 'active') === $statusFilter;
});

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Training Admin</title>
    <meta name="description" content="Enrollment and capacity reports for the training management system.">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px;
            margin-bottom: 36px;
        }

        .kpi-card {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: var(--shadow);
            padding: 24px 20px 20px;
            text-align: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
        }

        .kpi-value {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 800;
            color: var(--primary);
            line-height: 1;
            margin-bottom: 6px;
        }

        .kpi-label {
            font-size: 0.82rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
        }

        .kpi-card.success .kpi-value { color: #047857; }
        .kpi-card.danger  .kpi-value { color: var(--danger); }
        .kpi-card.accent  .kpi-value { color: #7c3aed; }

        /* ── Section title ── */
        .section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 36px 0 16px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--border);
        }

        .section-title h2 { margin: 0; font-size: 1.3rem; }

        /* ── Report table ── */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--panel);
            box-shadow: var(--shadow);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 36px;
        }

        .report-table thead { background: var(--primary-dark); color: #fff; }

        .report-table th,
        .report-table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border);
            font-size: 0.9rem;
        }

        .report-table th {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .report-table tbody tr:last-child td { border-bottom: none; }
        .report-table tbody tr:hover { background: #f8fafc; }

        /* ── Inline bar ── */
        .bar-wrap { display: flex; align-items: center; gap: 8px; }

        .bar {
            height: 14px;
            border-radius: 999px;
            min-width: 4px;
            transition: width 0.4s ease;
        }

        .bar-active    { background: #1d4ed8; }
        .bar-cancelled { background: #dc2626; }

        .bar-label { font-size: 0.82rem; color: var(--muted); white-space: nowrap; }

        /* ── Slot fill ── */
        .slot-fill { display: flex; flex-direction: column; gap: 4px; }

        .slot-fill-track {
            background: #e2e8f0;
            border-radius: 999px;
            height: 8px;
            overflow: hidden;
            width: 120px;
        }

        .slot-fill-bar {
            height: 100%;
            border-radius: 999px;
            background: var(--primary);
            transition: width 0.4s ease;
        }

        .slot-fill-bar.full { background: var(--danger); }
        .slot-text { font-size: 0.78rem; color: var(--muted); }

        /* ── Badge ── */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .badge-active    { background: #d1fae5; color: #047857; }
        .badge-cancelled { background: #fee2e2; color: #b91c1c; }
        .badge-full      { background: #fee2e2; color: #b91c1c; }

        /* ── Filter bar ── */
        .filter-bar {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
            flex-wrap: wrap;
        }

        .filter-bar a {
            padding: 7px 16px;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 700;
            background: #e2e8f0;
            color: var(--secondary);
            text-decoration: none;
            transition: background 0.2s;
        }

        .filter-bar a:hover       { background: #dbeafe; }
        .filter-bar a.active-f    { background: var(--primary); color: #fff; }

        /* ── Empty ── */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--muted);
        }

        .subtitle {
            color: var(--muted);
            margin-bottom: 28px;
            font-size: 0.95rem;
        }
    </style>
</head>
<body>
    <nav>
        <a href="../index.php">Home</a> |
        <a href="courses.php">Courses</a> |
        <a href="classes.php">Classes</a> |
        <a href="students.php">Record Student</a> |
        <a href="enroll.php">Enroll</a> |
        <a href="enrollments.php">Enrollments</a> |
        <a href="reports.php">Reports</a>
    </nav>

    <main>
        <h1>Reports &amp; Overview</h1>
        <p class="subtitle">A read-only summary of enrollment activity, course demand, and class capacity.</p>

        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-value"><?= $totalStudents ?></div>
                <div class="kpi-label">Registered Students</div>
            </div>
            <div class="kpi-card success">
                <div class="kpi-value"><?= $activeCount ?></div>
                <div class="kpi-label">Active Enrollments</div>
            </div>
            <div class="kpi-card danger">
                <div class="kpi-value"><?= $cancelledCount ?></div>
                <div class="kpi-label">Cancelled Enrollments</div>
            </div>
            <div class="kpi-card accent">
                <div class="kpi-value"><?= $totalSlots ?></div>
                <div class="kpi-label">Available Slots Remaining</div>
            </div>
        </div>

        <div class="section-title">
            <h2>Enrollments by Course</h2>
        </div>

        <?php
        $maxTotal = 1;
        foreach ($byCourse as $c) {
            $t = $c['active'] + $c['cancelled'];
            if ($t > $maxTotal) $maxTotal = $t;
        }
        ?>

        <?php if (empty($byCourse)): ?>
            <div class="empty-state">No enrollment data yet.</div>
        <?php else: ?>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Course</th>
                    <th style="width:220px">Active</th>
                    <th style="width:220px">Cancelled</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($byCourse as $courseName => $counts):
                    $total   = $counts['active'] + $counts['cancelled'];
                    $activeW = round(($counts['active']    / $maxTotal) * 160);
                    $cancelW = round(($counts['cancelled'] / $maxTotal) * 160);
                ?>
                <tr>
                    <td><?= htmlspecialchars($counts['code'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($courseName, ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <div class="bar-wrap">
                            <div class="bar bar-active" style="width:<?= $activeW ?>px"></div>
                            <span class="bar-label"><?= $counts['active'] ?></span>
                        </div>
                    </td>
                    <td>
                        <div class="bar-wrap">
                            <div class="bar bar-cancelled" style="width:<?= $cancelW ?>px"></div>
                            <span class="bar-label"><?= $counts['cancelled'] ?></span>
                        </div>
                    </td>
                    <td><strong><?= $total ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <div class="section-title">
            <h2>Class Capacity</h2>
        </div>

        <?php
        $enrolledPerClass = [];
        foreach ($allEnrollments as $row) {
            if (($row['status'] ?? 'active') !== 'cancelled') {
                $cid = (int) $row['class_id'];
                $enrolledPerClass[$cid] = ($enrolledPerClass[$cid] ?? 0) + 1;
            }
        }
        ?>

        <?php if (empty($allClasses)): ?>
            <div class="empty-state">No classes found.</div>
        <?php else: ?>
        <table class="report-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Course</th>
                    <th>Schedule</th>
                    <th>Instructor</th>
                    <th>Fill</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allClasses as $cls):
                    $cid          = (int) $cls['class_id'];
                    $enrolled     = $enrolledPerClass[$cid] ?? 0;
                    $slotsLeft    = (int) $cls['slots'];
                    $origCapacity = $slotsLeft + $enrolled;
                    $fillPct      = $origCapacity > 0 ? round(($enrolled / $origCapacity) * 100) : 0;
                    $isFull       = $slotsLeft <= 0;
                ?>
                <tr>
                    <td><?= $cid ?></td>
                    <td><?= htmlspecialchars($cls['class_code'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($cls['course_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($cls['schedule']    ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($cls['instructor']  ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <div class="slot-fill">
                            <div class="slot-fill-track">
                                <div class="slot-fill-bar <?= $isFull ? 'full' : '' ?>"
                                     style="width:<?= $fillPct ?>%"></div>
                            </div>
                            <span class="slot-text"><?= $enrolled ?> / <?= $origCapacity ?> (<?= $fillPct ?>%)</span>
                        </div>
                    </td>
                    <td>
                        <?php if ($isFull): ?>
                            <span class="badge badge-full">Full</span>
                        <?php else: ?>
                            <span class="badge badge-active"><?= $slotsLeft ?> left</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <div class="section-title">
            <h2>Enrollment Detail</h2>
        </div>

        <div class="filter-bar">
            <span style="font-size:0.85rem;font-weight:700;color:var(--muted);">Filter:</span>
            <a href="reports.php?status=all"
               id="filter-all"
               class="<?= $statusFilter === 'all'       ? 'active-f' : '' ?>">All (<?= $totalEnrollments ?>)</a>
            <a href="reports.php?status=active"
               id="filter-active"
               class="<?= $statusFilter === 'active'    ? 'active-f' : '' ?>">Active (<?= $activeCount ?>)</a>
            <a href="reports.php?status=cancelled"
               id="filter-cancelled"
               class="<?= $statusFilter === 'cancelled' ? 'active-f' : '' ?>">Cancelled (<?= $cancelledCount ?>)</a>
        </div>

        <?php if (empty($filtered)): ?>
            <div class="empty-state">No enrollments match the selected filter.</div>
        <?php else: ?>
        <table class="report-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Course</th>
                    <th>Class ID</th>
                    <th>Enrolled On</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($filtered as $row):
                    $status = $row['status'] ?? 'active';
                ?>
                <tr>
                    <td><?= (int) $row['enrollment_id'] ?></td>
                    <td><?= htmlspecialchars($row['full_name']    ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['email']        ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['phone']        ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <?= htmlspecialchars($row['course_name']  ?? '', ENT_QUOTES, 'UTF-8') ?>
                        <br><small style="color:var(--muted)"><?= htmlspecialchars($row['course_code'] ?? '', ENT_QUOTES, 'UTF-8') ?></small>
                    </td>
                    <td><?= (int) $row['class_id'] ?></td>
                    <td style="white-space:nowrap">
                        <?= htmlspecialchars($row['enrollment_date'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                    </td>
                    <td>
                        <span class="badge badge-<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars(ucfirst($status), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

    </main>

    <footer>
        <p>Training Management System &mdash; Reports</p>
    </footer>
</body>
</html>
