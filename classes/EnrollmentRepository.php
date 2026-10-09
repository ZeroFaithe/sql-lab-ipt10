<?php
 // classes/EnrollmentRepository.php
 // Data-access class that records students, enrollments and slot updates
 // inside a single database transaction.
 class EnrollmentRepository {
 private $db;
 public function __construct(PDO $db) { $this->db = $db; }

 public function recordStudent($full_name, $email, $phone, $class_id) {
     try {
         $this->db->beginTransaction();

         $stmt = $this->db->prepare(
             "SELECT slots FROM classes WHERE class_id = :class_id FOR UPDATE"
         );
         $stmt->execute([':class_id' => $class_id]);

         $class = $stmt->fetch(PDO::FETCH_ASSOC);

         if (!$class || (int) $class['slots'] <= 0) {
             $this->db->rollBack();
             return false;
         }

         $stmt = $this->db->prepare(
             "INSERT INTO students (full_name, email, phone)
              VALUES (:full_name, :email, :phone)"
         );
         $stmt->execute([
             ':full_name' => $full_name,
             ':email' => $email,
             ':phone' => $phone
         ]);

         $student_id = $this->db->lastInsertId();

         $stmt = $this->db->prepare(
             "INSERT INTO enrollments (student_id, class_id)
              VALUES (:student_id, :class_id)"
         );
         $stmt->execute([
             ':student_id' => $student_id,
             ':class_id' => $class_id
         ]);

         $stmt = $this->db->prepare(
             "UPDATE classes SET slots = slots - 1 WHERE class_id = :class_id"
         );
         $stmt->execute([':class_id' => $class_id]);

         $this->db->commit();
         return $student_id;

     } catch (Throwable $e) {
         if ($this->db->inTransaction()) {
             $this->db->rollBack();
         }
         return false;
     }
 }

 public function enroll($student_id, $class_id) {
     try {
         $this->db->beginTransaction();

         $stmt = $this->db->prepare(
             "SELECT slots FROM classes WHERE class_id = :class_id FOR UPDATE"
         );
         $stmt->execute([':class_id' => $class_id]);

         $class = $stmt->fetch(PDO::FETCH_ASSOC);

         if (!$class || (int) $class['slots'] <= 0) {
             $this->db->rollBack();
             return false;
         }

         $stmt = $this->db->prepare(
             "INSERT INTO enrollments (student_id, class_id)
              VALUES (:student_id, :class_id)"
         );
         $stmt->execute([
             ':student_id' => $student_id,
             ':class_id' => $class_id
         ]);

         $stmt = $this->db->prepare(
             "UPDATE classes SET slots = slots - 1 WHERE class_id = :class_id"
         );
         $stmt->execute([':class_id' => $class_id]);

         $this->db->commit();
         return true;

     } catch (Throwable $e) {
         if ($this->db->inTransaction()) {
             $this->db->rollBack();
         }
         return false;
     }
 }

 public function cancel($enrollment_id) {
     try {
         $this->db->beginTransaction();

         $stmt = $this->db->prepare(
             "SELECT class_id, status FROM enrollments
              WHERE enrollment_id = :enrollment_id
              FOR UPDATE"
         );
         $stmt->execute([':enrollment_id' => $enrollment_id]);

         $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);

         if (!$enrollment || $enrollment['status'] === 'cancelled') {
             $this->db->rollBack();
             return false;
         }

         $stmt = $this->db->prepare(
             "UPDATE enrollments
              SET status = 'cancelled'
              WHERE enrollment_id = :enrollment_id"
         );
         $stmt->execute([':enrollment_id' => $enrollment_id]);

         $stmt = $this->db->prepare(
             "UPDATE classes
              SET slots = slots + 1
              WHERE class_id = :class_id"
         );
         $stmt->execute([':class_id' => $enrollment['class_id']]);

         $this->db->commit();
         return true;

     } catch (Throwable $e) {
         if ($this->db->inTransaction()) {
             $this->db->rollBack();
         }
         return false;
     }
 }

 public function allWithDetails() {
    $sql = "SELECT
                enrollments.*,
                students.full_name,
                students.email,
                students.phone,
                classes.slots,
                courses.course_code,
                courses.course_name,
                courses.description
            FROM enrollments
            JOIN students
                ON enrollments.student_id = students.student_id
            JOIN classes
                ON enrollments.class_id = classes.class_id
            JOIN courses
                ON classes.course_id = courses.course_id";

     $stmt = $this->db->prepare($sql);
     $stmt->execute();

     return $stmt->fetchAll(PDO::FETCH_ASSOC);
 }
 }