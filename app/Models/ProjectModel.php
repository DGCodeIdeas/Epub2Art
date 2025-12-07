<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class ProjectModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function createProject($title)
    {
        $stmt = $this->pdo->prepare("INSERT INTO projects (title) VALUES (:title)");
        $stmt->execute(['title' => $title]);
        return $this->pdo->lastInsertId();
    }

    public function savePanels($projectId, $script)
    {
        $stmt = $this->pdo->prepare("INSERT INTO panels (project_id, panel_number, script_text, image_prompt, image_path) VALUES (:pid, :num, :text, :prompt, :path)");

        foreach ($script as $panel) {
            $stmt->execute([
                'pid' => $projectId,
                'num' => $panel['panel_number'] ?? 0,
                'text' => $panel['dialogue'] ?? '',
                'prompt' => $panel['description'] ?? '',
                'path' => $panel['image_url'] ?? '' // We will inject this before saving
            ]);
        }
    }

    public function getProjectPanels($projectId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM panels WHERE project_id = :pid ORDER BY panel_number ASC");
        $stmt->execute(['pid' => $projectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLatestProject() {
        $stmt = $this->pdo->query("SELECT * FROM projects ORDER BY id DESC LIMIT 1");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
