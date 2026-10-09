<?php
 // classes/Student.php
 class Student {
 private $db;
 public function __construct(PDO $db) { $this->db = $db; }

 public function find($id) {
 // TODO: return one student by id (prepared statement)
 // FILL IN CODE HERE
 $stmt = $this->db->prepare("SELECT * FROM students WHERE student_id = :id");
 $stmt->execute([`:id` => $id]);
 return $stmt->fetch();
 }

 public function all() {
 // TODO: return all students ordered by full_name
 // FILL IN CODE HERE
  return $this->db->query("SELECT * FROM students")->fetchAll();
 }
 }