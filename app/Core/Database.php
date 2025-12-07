<?php

namespace App\Core;

use PDO;

class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        // For PoC we default to SQLite
        $this->pdo = new PDO('sqlite:' . __DIR__ . '/../../database.sqlite');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->initializeSchema();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->pdo;
    }

    private function initializeSchema() {
        // Simple schema for PoC
        $query = "CREATE TABLE IF NOT EXISTS projects (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT,
            status TEXT DEFAULT 'pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS panels (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            project_id INTEGER,
            panel_number INTEGER,
            script_text TEXT,
            image_prompt TEXT,
            image_path TEXT,
            FOREIGN KEY(project_id) REFERENCES projects(id)
        );";

        $this->pdo->exec($query);
    }
}
