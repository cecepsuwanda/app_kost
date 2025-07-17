<?php

namespace App\Core;

class Model
{
    protected $db;
    protected $table;
    protected $config;
    protected $session;
    protected $request;
    protected $app;

    public function __construct(?Database $database = null, ?Application $app = null)
    {
        // If dependencies are provided via injection, use them
        if ($database !== null && $app !== null) {
            $this->app = $app;
            $this->db = $database;
            $this->config = $app->getConfig();
            $this->session = $app->getSession();
            $this->request = $app->getRequest();
        } else {
            // Fallback to singleton pattern for backward compatibility
            $this->db = Database::getInstance();
            $this->config = Config::getInstance();
            $this->session = Session::getInstance();
            $this->request = Request::getInstance();
        }
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
}