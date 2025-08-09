<?php
require_once __DIR__ . '/../config.php';

class CourseRepository {
    private $pdo;
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    /**
     * Create a new course
     */
    public function create($title, $description, $status = 'active') {
        $sql = "INSERT INTO courses (title, description, status) VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$title, $description, $status]);
    }
    
    /**
     * Find course by ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM courses WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get all courses
     */
    public function getAll($status = null) {
        if ($status) {
            $sql = "SELECT * FROM courses WHERE status = ? ORDER BY created_at DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$status]);
        } else {
            $sql = "SELECT * FROM courses ORDER BY created_at DESC";
            $stmt = $this->pdo->query($sql);
        }
        return $stmt->fetchAll();
    }
    
    /**
     * Get active courses
     */
    public function getActive() {
        return $this->getAll('active');
    }
    
    /**
     * Update course
     */
    public function update($id, $title, $description, $status) {
        $sql = "UPDATE courses SET title = ?, description = ?, status = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$title, $description, $status, $id]);
    }
    
    /**
     * Delete course
     */
    public function delete($id) {
        $sql = "DELETE FROM courses WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Get course count
     */
    public function getCount() {
        $sql = "SELECT COUNT(*) as count FROM courses WHERE status = 'active'";
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    /**
     * Get courses with enrollment info for a specific user
     */
    public function getCoursesWithEnrollmentStatus($userId) {
        $sql = "SELECT c.*, 
                       CASE WHEN e.id IS NOT NULL THEN 1 ELSE 0 END as is_enrolled
                FROM courses c
                LEFT JOIN enrollments e ON c.id = e.course_id AND e.user_id = ?
                WHERE c.status = 'active'
                ORDER BY c.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
} 