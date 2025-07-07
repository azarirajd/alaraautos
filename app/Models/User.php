<?php
// app/Models/User.php

namespace Models;

/**
 * Modelo de Usuario
 */
class User extends Model {
    
    protected $table = 'users';
    
    protected $fillable = [
        'name', 'email', 'password', 'role', 'avatar', 'is_active'
    ];
    
    /**
     * Buscar usuario por email
     */
    public function findByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }
    
    /**
     * Verificar contraseña
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Crear usuario con contraseña hasheada
     */
    public function create($data) {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        return parent::create($data);
    }
    
    /**
     * Actualizar usuario (hashear contraseña si se proporciona)
     */
    public function update($id, $data) {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }
        return parent::update($id, $data);
    }
}