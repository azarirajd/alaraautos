<?php
// app/Models/Model.php

namespace Models;

/**
 * Modelo base del que heredan todos los demás
 */
abstract class Model {
    
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $timestamps = true;
    
    public function __construct() {
        $this->db = \Database::getInstance()->getConnection();
    }
    
    /**
     * Encontrar por ID
     */
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    /**
     * Obtener todos los registros
     */
    public function all($orderBy = null) {
        $sql = "SELECT * FROM {$this->table}";
        
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar con condiciones
     */
    public function where($conditions, $orderBy = null) {
        $where = [];
        $params = [];
        
        foreach ($conditions as $key => $value) {
            $where[] = "$key = :$key";
            $params[$key] = $value;
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $where);
        
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Crear nuevo registro
     */
    public function create($data) {
        // Filtrar solo campos permitidos
        $data = array_intersect_key($data, array_flip($this->fillable));
        
        // Agregar timestamps si está habilitado
        if ($this->timestamps) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute($data)) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Actualizar registro
     */
    public function update($id, $data) {
        // Filtrar solo campos permitidos
        $data = array_intersect_key($data, array_flip($this->fillable));
        
        // Actualizar timestamp
        if ($this->timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "$key = :$key";
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . 
               " WHERE {$this->primaryKey} = :id";
        
        $data['id'] = $id;
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($data);
    }
    
    /**
     * Eliminar registro
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Contar registros
     */
    public function count($conditions = []) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "$key = :$key";
                $params[$key] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['total'] ?? 0;
    }
    
    /**
     * Paginación
     */
    public function paginate($page = 1, $perPage = 10, $conditions = [], $orderBy = null) {
        $offset = ($page - 1) * $perPage;
        $where = '';
        $params = [];
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $key => $value) {
                $whereClause[] = "$key = :$key";
                $params[$key] = $value;
            }
            $where = " WHERE " . implode(' AND ', $whereClause);
        }
        
        $sql = "SELECT * FROM {$this->table}" . $where;
        
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        
        $sql .= " LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        
        $data = $stmt->fetchAll();
        $total = $this->count($conditions);
        
        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }
}