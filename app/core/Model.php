<?php

namespace App\Core;

class Model
{
    protected $db;
    protected $table;
    protected $config;
    protected $queryBuilder;
    

    public function __construct()
    {      
            // Fallback to singleton pattern for backward compatibility
            $this->db = Database::getInstance();
            $this->config = Config::getInstance();
            $this->queryBuilder = new QueryBuilder($this->db);
        
    }

    public function findAll()
    {
        return $this->db->fetchAll("SELECT * FROM {$this->table}");
    }

    public function findById($id)
    {
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE id = :id", ['id' => $id]);
    }

    public function create($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data)
    {
        return $this->db->update($this->table, $data, 'id = :id', ['id' => $id]);
    }

    public function delete($id)
    {
        return $this->db->delete($this->table, 'id = :id', ['id' => $id]);
    }

    public function where($condition, $params = [])
    {
        return $this->db->fetchAll("SELECT * FROM {$this->table} WHERE $condition", $params);
    }

    public function count($condition = '1=1', $params = [])
    {
        $result = $this->db->fetch("SELECT COUNT(*) as count FROM {$this->table} WHERE $condition", $params);
        return $result['count'];
    }

    /**
     * Get a new query builder instance for this model's table
     */
    protected function query(): QueryBuilder
    {
        return $this->queryBuilder->table($this->table);
    }

    /**
     * Get a new query builder instance for any table
     */
    protected function queryTable(string $table): QueryBuilder
    {
        return $this->queryBuilder->table($table);
    }
}