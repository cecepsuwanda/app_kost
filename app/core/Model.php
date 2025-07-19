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
        // SQL: Mengambil semua data dari tabel model
        // SELECT * FROM table_name -> ambil semua kolom (*) dan semua baris
        // Method dasar untuk mendapatkan seluruh data tanpa filter
        return $this->db->fetchAll("SELECT * FROM {$this->table}");
    }

    public function findById($id)
    {
        // SQL: Mencari satu record berdasarkan ID (Primary Key)
        // SELECT * FROM table_name WHERE id = ? -> filter berdasarkan ID unik
        // Parameter binding (:id) mencegah SQL injection
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
        // SQL: Mengambil data dengan kondisi WHERE custom
        // SELECT * FROM table_name WHERE condition -> filter berdasarkan kondisi yang diberikan
        // $condition: string kondisi SQL (contoh: "nama = :nama AND aktif = 1")
        // $params: array parameter untuk binding (contoh: ['nama' => 'John'])
        return $this->db->fetchAll("SELECT * FROM {$this->table} WHERE $condition", $params);
    }

    public function count($condition = '1=1', $params = [])
    {
        // SQL AGGREGATE: Menghitung jumlah record dengan kondisi tertentu
        // SELECT COUNT(*) as count FROM table_name WHERE condition
        // COUNT(*): fungsi agregat untuk menghitung jumlah baris
        // WHERE condition: filter data yang akan dihitung (default '1=1' = semua data)
        // Return: integer jumlah record yang sesuai kondisi
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