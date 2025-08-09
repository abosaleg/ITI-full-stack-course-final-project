<?php
require_once __DIR__ . '/../config.php';

class UserRepository {
    private $pdo;
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    /**
     * Create a new user
     */
    public function create($name, $email, $phone, $password, $role = 'student', $avatar_path = null) {
        $sql = "INSERT INTO users (name, email, phone, password_hash, role, avatar_path) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        return $stmt->execute([$name, $email, $phone, $password_hash, $role, $avatar_path]);
    }
    
    /**
     * Find user by email
     */
    public function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    /**
     * Find user by ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get all users with optional role filter
     */
    public function getAll($role = null) {
        if ($role) {
            $sql = "SELECT * FROM users WHERE role = ? ORDER BY created_at DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$role]);
        } else {
            $sql = "SELECT * FROM users ORDER BY created_at DESC";
            $stmt = $this->pdo->query($sql);
        }
        return $stmt->fetchAll();
    }
    
    /**
     * Update user
     */
    public function update($id, $name, $email, $phone, $avatar_path = null) {
        if ($avatar_path !== null) {
            $sql = "UPDATE users SET name = ?, email = ?, phone = ?, avatar_path = ? WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$name, $email, $phone, $avatar_path, $id]);
        } else {
            $sql = "UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$name, $email, $phone, $id]);
        }
    }
    
    /**
     * Update password
     */
    public function updatePassword($id, $password) {
        $sql = "UPDATE users SET password_hash = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        return $stmt->execute([$password_hash, $id]);
    }
    
    /**
     * Delete user
     */
    public function delete($id) {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Check if email exists
     */
    public function emailExists($email, $excludeId = null) {
        if ($excludeId) {
            $sql = "SELECT id FROM users WHERE email = ? AND id != ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$email, $excludeId]);
        } else {
            $sql = "SELECT id FROM users WHERE email = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$email]);
        }
        return $stmt->fetch() !== false;
    }
    
    /**
     * Check if phone exists
     */
    public function phoneExists($phone, $excludeId = null) {
        if ($excludeId) {
            $sql = "SELECT id FROM users WHERE phone = ? AND id != ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$phone, $excludeId]);
        } else {
            $sql = "SELECT id FROM users WHERE phone = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$phone]);
        }
        return $stmt->fetch() !== false;
    }
    
    /**
     * Get user count
     */
    public function getCount() {
        $sql = "SELECT COUNT(*) as count FROM users WHERE role = 'student'";
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch();
        return $result['count'];
    }
} 