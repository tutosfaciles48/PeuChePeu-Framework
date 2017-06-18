<?php

namespace Core\Database;

/**
 * Représente une table en base de données.
 */
class Table
{
    /**
     * @var Database
     */
    protected $database;

    /**
     * @var string Nom de la table en abse de données
     */
    protected $table;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Récupère un enregistrement en se basant sur l'ID.
     *
     * @param int $id
     *
     * @return array
     */
    public function find(int $id): array
    {
        return $this->database->fetch('SELECT * FROM ' . $this->table . ' WHERE id = ?', [$id]);
    }

    /**
     * Supprime un enregistrement.
     *
     * @param int $id
     *
     * @return \PDOStatement
     */
    public function delete(int $id): \PDOStatement
    {
        return $this->database->query('DELETE FROM ' . $this->table . ' WHERE id = ?', [$id]);
    }

    /**
     * Met à jour un enregistrement
     * Attention, les clefs ne sont pas échapées !
     *
     * @param int   $id
     * @param array $params
     *
     * @return \PDOStatement
     */
    public function update(int $id, array $params): \PDOStatement
    {
        $query = implode(', ', array_map(function ($field) {
            return "$field = :$field";
        }, array_keys($params)));
        $params['id'] = $id;

        return $this->database->query("UPDATE {$this->table} SET $query WHERE id = :id", $params);
    }

    /**
     * Crée un nouvel enregistrement.
     *
     * @param array $params
     *
     * @return int|null
     */
    public function create(array $params): ?int
    {
        $query = implode(', ', array_map(function ($field) {
            return "$field = :$field";
        }, array_keys($params)));
        $this->database->query("INSERT INTO {$this->table} SET $query", $params);

        return $this->database->lastInsertId();
    }
}
