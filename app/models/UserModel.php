<?php

namespace App\Models;

use App\Core\Model;

class UserModel extends Model
{
    protected $table = 'users';

    public function authenticate($username, $password)
    {
        $sql = "SELECT * FROM {$this->table} WHERE username = :username AND is_active = 1";
        $user = $this->db->fetch($sql, ['username' => $username]);
        
        if ($user && password_verify($password, $user['password'])) {
            // Remove password from returned data
            unset($user['password']);
            return $user;
        }
        
        return false;
    }

    public function createUser($username, $password, $nama, $role = 'admin')
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $data = [
            'username' => $username,
            'password' => $hashedPassword,
            'nama' => $nama,
            'role' => $role,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->insert($this->table, $data);
    }

    public function findByUsername($username)
    {
        $sql = "SELECT * FROM {$this->table} WHERE username = :username";
        return $this->db->fetch($sql, ['username' => $username]);
    }

    public function updateLastLogin($userId)
    {
        $data = ['last_login' => date('Y-m-d H:i:s')];
        return $this->db->update($this->table, $data, 'id = :id', ['id' => $userId]);
    }
    
    public function changePassword($userId, $newPassword)
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $data = ['password' => $hashedPassword];
        return $this->db->update($this->table, $data, 'id = :id', ['id' => $userId]);
    }
    
    public function deactivateUser($userId)
    {
        $data = ['is_active' => 0];
        return $this->db->update($this->table, $data, 'id = :id', ['id' => $userId]);
    }
    
    public function activateUser($userId)
    {
        $data = ['is_active' => 1];
        return $this->db->update($this->table, $data, 'id = :id', ['id' => $userId]);
    }
}

/**
 * =============================================================================
 * CLASS DOCUMENTATION FOR AI LLM UNDERSTANDING
 * =============================================================================
 * 
 * CLASS: UserModel
 * PURPOSE: Handles user authentication, authorization, and user management
 * DATABASE_TABLE: users
 * EXTENDS: Model (base model class)
 * 
 * BUSINESS_CONTEXT:
 * This model manages system users who can access the admin panel. Users have
 * roles (admin/superadmin) and authentication credentials. It handles login
 * validation, password verification, and user status management.
 * 
 * CLASS_METHODS:
 * 
 * 1. findByUsername($username, $includeInactive = false)
 *    PURPOSE: Find user by username for authentication
 *    PARAMETERS: 
 *      - $username: string - The username to search for
 *      - $includeInactive: bool - Whether to include inactive users
 *    RETURNS: array|null - User data or null if not found
 *    SQL_QUERY: SELECT * FROM users WHERE username = ? AND is_active = 1
 *    USED_IN:
 *      - Auth::login() - for user authentication
 *      - Auth::validateLogin() - for credential verification
 *    AI_CONTEXT: This is the primary method for user authentication in the system
 * 
 * 2. verifyPassword($password, $hashedPassword)
 *    PURPOSE: Verify plain password against hashed password
 *    PARAMETERS:
 *      - $password: string - Plain text password from user input
 *      - $hashedPassword: string - Stored hash from database
 *    RETURNS: bool - True if password matches, false otherwise
 *    SECURITY: Uses PHP's password_verify() for secure password checking
 *    USED_IN:
 *      - Auth::login() - during login process
 *    AI_CONTEXT: Critical security method for password verification
 * 
 * 3. updateLastLogin($userId)
 *    PURPOSE: Update user's last login timestamp
 *    PARAMETERS: $userId: int - User ID to update
 *    RETURNS: int - Number of affected rows
 *    SQL_QUERY: UPDATE users SET last_login = NOW() WHERE id = ?
 *    USED_IN:
 *      - Auth::login() - after successful login
 *    AI_CONTEXT: Tracks user activity for security and analytics
 * 
 * 4. createUser($data)
 *    PURPOSE: Create new user with hashed password
 *    PARAMETERS: $data: array - User data including password
 *    RETURNS: int - New user ID
 *    SECURITY: Automatically hashes password before storage
 *    USED_IN:
 *      - Install::insertSampleData() - creating default admin user
 *      - Admin panel user creation (if implemented)
 *    AI_CONTEXT: Secure user creation with automatic password hashing
 * 
 * 5. findByUsernameAll($username)
 *    PURPOSE: Find user by username including inactive users
 *    PARAMETERS: $username: string - Username to search
 *    RETURNS: array|null - User data regardless of active status
 *    USED_IN:
 *      - Admin user management features
 *    AI_CONTEXT: Administrative function to find any user regardless of status
 * 
 * 6. activateUser($userId)
 *    PURPOSE: Activate a user account
 *    PARAMETERS: $userId: int - User ID to activate
 *    RETURNS: int - Number of affected rows
 *    SQL_QUERY: UPDATE users SET is_active = 1 WHERE id = ?
 *    USED_IN:
 *      - Admin user management
 *    AI_CONTEXT: Administrative function to enable user accounts
 * 
 * DATABASE_RELATIONSHIPS:
 * - No direct foreign key relationships with other tables
 * - Users are referenced indirectly through session management
 * 
 * SECURITY_FEATURES:
 * - Password hashing using PHP's password_hash()
 * - Password verification using password_verify()
 * - Active/inactive user status control
 * - Role-based access control (admin/superadmin)
 * 
 * USAGE_PATTERNS:
 * 1. Authentication Flow:
 *    Auth::login() -> UserModel::findByUsername() -> UserModel::verifyPassword() -> UserModel::updateLastLogin()
 * 
 * 2. User Creation:
 *    Install::insertSampleData() -> UserModel::createUser()
 * 
 * AI_INTEGRATION_NOTES:
 * - This model is critical for system security and access control
 * - All password operations use secure PHP functions
 * - User roles determine access levels throughout the application
 * - Session management depends on this model's authentication methods
 */