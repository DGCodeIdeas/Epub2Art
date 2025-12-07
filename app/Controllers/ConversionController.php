<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\EpubService;
use App\Services\LLMService;
use App\Services\ImageGenService;
use App\Models\ProjectModel;

class ConversionController extends Controller
{
    public function upload()
    {
        $epubService = new EpubService();
        $llmService = new LLMService();
        $imageService = new ImageGenService();
        $projectModel = new ProjectModel();

        $text = "";
        $title = "Untitled Project";

        // 1. Handle Input (File or Text)
        if (isset($_FILES['epub']) && $_FILES['epub']['error'] === UPLOAD_ERR_OK) {
            $tmpPath = $_FILES['epub']['tmp_name'];
            $fileName = $_FILES['epub']['name'];
            $title = pathinfo($fileName, PATHINFO_FILENAME);

            try {
                $text = $epubService->extractText($tmpPath);
            } catch (\Exception $e) {
                die("Error reading EPUB: " . $e->getMessage());
            }
        } elseif (!empty($_POST['text_content'])) {
            $text = $_POST['text_content'];
            $title = "Text Snippet " . date('Y-m-d H:i');
        } else {
            die("No input provided.");
        }

        // Limit text for PoC to avoid timeouts/limits
        $textToProcess = substr($text, 0, 5000);

        // 2. Generate Script
        $script = $llmService->generateScript($textToProcess);

        // 3. Generate Images for each panel
        // Note: In a real job queue, this would be backgrounded.
        // For PoC, we do it inline (might be slow).
        foreach ($script as &$panel) {
            $description = $panel['description'] ?? 'Scene';
            $panel['image_url'] = $imageService->generateImage($description);
        }

        // 4. Save to DB
        $projectId = $projectModel->createProject($title);
        $projectModel->savePanels($projectId, $script);

        // 5. Redirect to Result
        header("Location: /result?project_id=$projectId");
        exit;
    }

    public function result()
    {
        $projectModel = new ProjectModel();

        $projectId = $_GET['project_id'] ?? null;

        if (!$projectId) {
            // Default to latest for convenience
            $latest = $projectModel->getLatestProject();
            if ($latest) {
                $projectId = $latest['id'];
            }
        }

        $panels = [];
        if ($projectId) {
            $panels = $projectModel->getProjectPanels($projectId);
        }

        $this->render('result', ['panels' => $panels]);
    }
}
