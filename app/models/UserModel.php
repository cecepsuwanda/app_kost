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