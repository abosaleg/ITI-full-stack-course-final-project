<?php
require_once __DIR__ . '/../config.php';

class EnrollmentRepository {
    private $pdo;
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    /**
     * Enroll user in course
     */
    public function enroll($userId, $courseId) {
        $sql = "INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$userId, $courseId]);
    }
    
    /**
     * Unenroll user from course
     */
    public function unenroll($userId, $courseId) {
        $sql = "DELETE FROM enrollments WHERE user_id = ? AND course_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$userId, $courseId]);
    }
    
    /**
     * Check if user is enrolled in course
     */
    public function isEnrolled($userId, $courseId) {
        $sql = "SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId, $courseId]);
        return $stmt->fetch() !== false;
    }
    
    /**
     * Get enrollments for a user
     */
    public function getUserEnrollments($userId) {
        $sql = "SELECT e.*, c.title, c.description, c.status 
                FROM enrollments e 
                JOIN courses c ON e.course_id = c.id 
                WHERE e.user_id = ? 
                ORDER BY e.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all enrollments with user and course details
     */
    public function getAllWithDetails() {
        $sql = "SELECT e.*, u.name as user_name, u.email as user_email, 
                       c.title as course_title 
                FROM enrollments e 
                JOIN users u ON e.user_id = u.id 
                JOIN courses c ON e.course_id = c.id 
                ORDER BY e.created_at DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Delete enrollment by ID
     */
    public function delete($id) {
        $sql = "DELETE FROM enrollments WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Get enrollment count
     */
    public function getCount() {
        $sql = "SELECT COUNT(*) as count FROM enrollments";
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    /**
     * Get enrollments filtered by user or course
     */
    public function getFiltered($userId = null, $courseId = null) {
        $sql = "SELECT e.*, u.name as user_name, u.email as user_email, 
                       c.title as course_title 
                FROM enrollments e 
                JOIN users u ON e.user_id = u.id 
                JOIN courses c ON e.course_id = c.id";
        
        $params = [];
        $conditions = [];
        
        if ($userId) {
            $conditions[] = "e.user_id = ?";
            $params[] = $userId;
        }
        
        if ($courseId) {
            $conditions[] = "e.course_id = ?";
            $params[] = $courseId;
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $sql .= " ORDER BY e.created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
} 