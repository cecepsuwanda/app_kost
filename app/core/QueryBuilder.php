<?php

namespace App\Core;

use PDO;

class QueryBuilder
{
    private $db;
    private $table;
    private $select = ['*'];
    private $joins = [];
    private $where = [];
    private $groupBy = [];
    private $having = [];
    private $orderBy = [];
    private $limit;
    private $offset;
    private $params = [];
    private $paramCounter = 0;

    public function __construct(Database $database = null)
    {
        $this->db = $database ?: Database::getInstance();
    }

    /**
     * Start a new query with table
     */
    public function table(string $table): self
    {
        $this->reset();
        $this->table = $table;
        return $this;
    }

    /**
     * Set SELECT columns
     */
    public function select(...$columns): self
    {
        if (empty($columns)) {
            $this->select = ['*'];
        } else {
            $this->select = is_array($columns[0]) ? $columns[0] : $columns;
        }
        return $this;
    }

    /**
     * Add JOIN clause
     */
    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self
    {
        $this->joins[] = [
            'type' => strtoupper($type),
            'table' => $table,
            'condition' => "$first $operator $second"
        ];
        return $this;
    }

    /**
     * Add LEFT JOIN clause
     */
    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    /**
     * Add RIGHT JOIN clause
     */
    public function rightJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'RIGHT');
    }

    /**
     * Add INNER JOIN clause
     */
    public function innerJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'INNER');
    }

    /**
     * Add WHERE clause
     */
    public function where(string $column, $operator = null, $value = null): self
    {
        if ($operator === null) {
            // Raw where clause
            $this->where[] = ['type' => 'raw', 'condition' => $column];
        } else {
            $paramName = $this->generateParamName($column);
            $this->where[] = ['type' => 'where', 'condition' => "$column $operator :$paramName"];
            $this->params[$paramName] = $value;
        }
        return $this;
    }

    /**
     * Add WHERE OR clause
     */
    public function orWhere(string $column, $operator = null, $value = null): self
    {
        if ($operator === null) {
            $this->where[] = ['type' => 'or_raw', 'condition' => $column];
        } else {
            $paramName = $this->generateParamName($column);
            $this->where[] = ['type' => 'or_where', 'condition' => "$column $operator :$paramName"];
            $this->params[$paramName] = $value;
        }
        return $this;
    }

    /**
     * Add WHERE IN clause
     */
    public function whereIn(string $column, array $values): self
    {
        $placeholders = [];
        foreach ($values as $value) {
            $paramName = $this->generateParamName($column);
            $placeholders[] = ":$paramName";
            $this->params[$paramName] = $value;
        }
        $this->where[] = ['type' => 'where', 'condition' => "$column IN (" . implode(', ', $placeholders) . ")"];
        return $this;
    }

    /**
     * Add WHERE NOT IN clause
     */
    public function whereNotIn(string $column, array $values): self
    {
        $placeholders = [];
        foreach ($values as $value) {
            $paramName = $this->generateParamName($column);
            $placeholders[] = ":$paramName";
            $this->params[$paramName] = $value;
        }
        $this->where[] = ['type' => 'where', 'condition' => "$column NOT IN (" . implode(', ', $placeholders) . ")"];
        return $this;
    }

    /**
     * Add WHERE NULL clause
     */
    public function whereNull(string $column): self
    {
        $this->where[] = ['type' => 'where', 'condition' => "$column IS NULL"];
        return $this;
    }

    /**
     * Add WHERE NOT NULL clause
     */
    public function whereNotNull(string $column): self
    {
        $this->where[] = ['type' => 'where', 'condition' => "$column IS NOT NULL"];
        return $this;
    }

    /**
     * Add WHERE BETWEEN clause
     */
    public function whereBetween(string $column, $min, $max): self
    {
        $paramMin = $this->generateParamName($column . '_min');
        $paramMax = $this->generateParamName($column . '_max');
        $this->where[] = ['type' => 'where', 'condition' => "$column BETWEEN :$paramMin AND :$paramMax"];
        $this->params[$paramMin] = $min;
        $this->params[$paramMax] = $max;
        return $this;
    }

    /**
     * Add WHERE LIKE clause
     */
    public function whereLike(string $column, string $pattern): self
    {
        $paramName = $this->generateParamName($column);
        $this->where[] = ['type' => 'where', 'condition' => "$column LIKE :$paramName"];
        $this->params[$paramName] = $pattern;
        return $this;
    }

    /**
     * Add GROUP BY clause
     */
    public function groupBy(...$columns): self
    {
        $this->groupBy = array_merge($this->groupBy, is_array($columns[0]) ? $columns[0] : $columns);
        return $this;
    }

    /**
     * Add HAVING clause
     */
    public function having(string $column, string $operator, $value): self
    {
        $paramName = $this->generateParamName($column);
        $this->having[] = "$column $operator :$paramName";
        $this->params[$paramName] = $value;
        return $this;
    }

    /**
     * Add ORDER BY clause
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy[] = "$column " . strtoupper($direction);
        return $this;
    }

    /**
     * Add LIMIT clause
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Add OFFSET clause
     */
    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Execute query and get all results
     */
    public function get(): array
    {
        $sql = $this->buildSelectQuery();
        return $this->db->fetchAll($sql, $this->params);
    }

    /**
     * Execute query and get first result
     */
    public function first(): ?array
    {
        $sql = $this->buildSelectQuery();
        $result = $this->db->fetch($sql, $this->params);
        return $result ?: null;
    }

    /**
     * Execute query and get count
     */
    public function count(string $column = '*'): int
    {
        $originalSelect = $this->select;
        $this->select = ["COUNT($column) as count"];
        $sql = $this->buildSelectQuery();
        $this->select = $originalSelect;
        $result = $this->db->fetch($sql, $this->params);
        return (int)($result['count'] ?? 0);
    }

    /**
     * Insert data
     */
    public function insert(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = [];
        $params = [];

        foreach ($data as $key => $value) {
            $paramName = $this->generateParamName($key);
            $placeholders[] = ":$paramName";
            $params[$paramName] = $value;
        }

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $this->db->execute($sql, $params);
        return $this->db->getConnection()->lastInsertId();
    }

    /**
     * Update data
     */
    public function update(array $data): int
    {
        $set = [];
        $params = $this->params;

        foreach ($data as $key => $value) {
            $paramName = $this->generateParamName($key);
            $set[] = "$key = :$paramName";
            $params[$paramName] = $value;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $set);
        
        if (!empty($this->where)) {
            $sql .= " WHERE " . $this->buildWhereClause();
        }

        return $this->db->execute($sql, $params);
    }

    /**
     * Delete data
     */
    public function delete(): int
    {
        $sql = "DELETE FROM {$this->table}";
        
        if (!empty($this->where)) {
            $sql .= " WHERE " . $this->buildWhereClause();
        }

        return $this->db->execute($sql, $this->params);
    }

    /**
     * Execute raw query
     */
    public function raw(string $sql, array $params = []): array
    {
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Execute raw query and get first result
     */
    public function rawFirst(string $sql, array $params = []): ?array
    {
        $result = $this->db->fetch($sql, $params);
        return $result ?: null;
    }

    /**
     * Build SELECT query
     */
    private function buildSelectQuery(): string
    {
        $sql = "SELECT " . implode(', ', $this->select) . " FROM {$this->table}";

        // Add JOINs
        foreach ($this->joins as $join) {
            $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['condition']}";
        }

        // Add WHERE
        if (!empty($this->where)) {
            $sql .= " WHERE " . $this->buildWhereClause();
        }

        // Add GROUP BY
        if (!empty($this->groupBy)) {
            $sql .= " GROUP BY " . implode(', ', $this->groupBy);
        }

        // Add HAVING
        if (!empty($this->having)) {
            $sql .= " HAVING " . implode(' AND ', $this->having);
        }

        // Add ORDER BY
        if (!empty($this->orderBy)) {
            $sql .= " ORDER BY " . implode(', ', $this->orderBy);
        }

        // Add LIMIT
        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }

        // Add OFFSET
        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }

        return $sql;
    }

    /**
     * Build WHERE clause
     */
    private function buildWhereClause(): string
    {
        $conditions = [];
        
        foreach ($this->where as $index => $where) {
            if ($index === 0) {
                $conditions[] = $where['condition'];
            } elseif ($where['type'] === 'or_where' || $where['type'] === 'or_raw') {
                $conditions[] = "OR " . $where['condition'];
            } else {
                $conditions[] = "AND " . $where['condition'];
            }
        }

        return implode(' ', $conditions);
    }

    /**
     * Generate unique parameter name
     */
    private function generateParamName(string $column): string
    {
        $baseName = preg_replace('/[^a-zA-Z0-9_]/', '_', $column);
        return $baseName . '_' . (++$this->paramCounter);
    }

    /**
     * Reset query builder
     */
    private function reset(): void
    {
        $this->table = null;
        $this->select = ['*'];
        $this->joins = [];
        $this->where = [];
        $this->groupBy = [];
        $this->having = [];
        $this->orderBy = [];
        $this->limit = null;
        $this->offset = null;
        $this->params = [];
        $this->paramCounter = 0;
    }

    /**
     * Debug: Get the built SQL query
     */
    public function toSql(): string
    {
        return $this->buildSelectQuery();
    }

    /**
     * Debug: Get the parameters
     */
    public function getParams(): array
    {
        return $this->params;
    }
}