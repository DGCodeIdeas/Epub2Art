# Specification: Epub Novel to Manga Script Converter

## 1. Overview
This project aims to create a workflow for converting EPUB novels into structured Manga Scripts. The system uses AI (LLMs) to reinterpret novel text into visual script formats (Pages, Panels, Visuals, Dialogue, SFX).

**Key Features:**
*   **Input:** EPUB Novel.
*   **Processing:** Automatic parsing, scene chunking, and AI script generation.
*   **Human-in-the-Loop:** Strict chapter-by-chapter approval workflow.
*   **Output:** A new EPUB file containing *only* the approved text scripts.
*   **Format:** Standardized Manga Script format (Page/Panel breakdown).

## 2. Architecture & Data Model

### 2.1 Database Schema (SQLite)

We will extend the existing schema to support the hierarchical nature of a book conversion.

```sql
-- Projects (The Novel)
CREATE TABLE projects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    original_filename TEXT,
    status TEXT DEFAULT 'processing', -- processing, ready_for_review, completed
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Chapters (Parsed from EPUB spine)
CREATE TABLE chapters (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    project_id INTEGER,
    chapter_index INTEGER, -- 1, 2, 3...
    title TEXT,
    raw_text TEXT, -- The full text of the chapter
    status TEXT DEFAULT 'pending', -- pending, processing, drafted, approved, rejected
    FOREIGN KEY(project_id) REFERENCES projects(id)
);

-- Scenes (Chunks of text derived from Chapters)
CREATE TABLE scenes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    chapter_id INTEGER,
    scene_index INTEGER,
    raw_text TEXT, -- The paragraph(s) being converted
    FOREIGN KEY(chapter_id) REFERENCES chapters(id)
);

-- Script Pages (The AI Output)
CREATE TABLE script_pages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    scene_id INTEGER,
    page_number INTEGER, -- Logical page number in the final script
    side TEXT, -- 'Left' or 'Right'
    content_json TEXT, -- JSON structure of panels, visuals, dialogue
    content_markdown TEXT, -- The rendered script text (for display/export)
    FOREIGN KEY(scene_id) REFERENCES scenes(id)
);
```

## 3. Core Logic Pipelines

### 3.1 Ingestion (EPUB Parsing)
*   **Class:** `App\Services\EpubService`
*   **Logic:**
    1.  Unzip EPUB.
    2.  Parse OPF to get the spine (reading order).
    3.  Iterate through spine items.
    4.  Extract clean text from each item.
    5.  **New:** Identify distinct chapters (heuristics: H1/H2 tags, file boundaries) and save them to the `chapters` table.

### 3.2 Preprocessing (Scene Chunking)
*   **Class:** `App\Services\SceneChunkerService`
*   **Goal:** Break a long chapter into manageable chunks (Scenes) that roughly correspond to 1-2 manga pages.
*   **Logic:**
    *   Use an NLP-based heuristic (or a lightweight LLM call) to detect scene breaks (time jumps, location changes) or simply chunk by paragraph groups (approx 300-500 words).
    *   Store chunks in `scenes` table.

### 3.3 Generation (Text-to-Script)
*   **Class:** `App\Services\ScriptGenService` (wraps `LLMService`)
*   **Input:** A `scene` text block.
*   **Prompt Strategy:**
    *   **Role:** Expert Manga Scripter.
    *   **Constraint:** Output *must* follow a specific JSON Schema.
    *   **Input Context:** The novel text.
*   **JSON Schema Definition:**
    ```json
    {
      "type": "object",
      "properties": {
        "page_number": { "type": "integer" },
        "side": { "type": "string", "enum": ["Left", "Right"] },
        "panels": {
          "type": "array",
          "items": {
            "type": "object",
            "properties": {
              "number": { "type": "integer" },
              "type": { "type": "string", "description": "Size/Shape (e.g., Wide, Vertical)" },
              "shot": { "type": "string", "description": "Shot type (e.g., Close-Up, Establishing)" },
              "visual": { "type": "string", "description": "Visual description of the scene" },
              "sfx": { "type": "string", "description": "Sound effects" },
              "caption": { "type": "string" },
              "dialogue": {
                "type": "array",
                "items": {
                  "type": "object",
                  "properties": {
                    "character": { "type": "string" },
                    "text": { "type": "string" },
                    "type": { "type": "string", "enum": ["speech", "thought", "scream"] }
                  }
                }
              }
            }
          }
        }
      }
    }
    ```
*   **Post-Processing:** Convert the JSON output into the readable Markdown format specified by the user (Panel headers, bold keys, separators).

## 4. Workflow: Human Approval

1.  **Dashboard:** User sees list of Chapters with status (e.g., "Chapter 1: Drafted", "Chapter 2: Pending").
2.  **Review Interface:**
    *   Clicking a "Drafted" chapter opens the **Script Editor**.
    *   **Left Column:** Original Novel Text (for reference).
    *   **Right Column:** Generated Manga Script (Markdown).
    *   **Action:** User can edit the script directly.
    *   **Controls:** `Approve Chapter`, `Regenerate`, `Save Draft`.
3.  **Progression:** Only `Approved` chapters are eligible for the final export.

## 5. Export (Script EPUB)

*   **Trigger:** User clicks "Export Approved Chapters".
*   **Logic:**
    1.  Create a new `ZipArchive` (EPUB structure).
    2.  Generate `mimetype`, `META-INF/container.xml`.
    3.  Generate `content.opf` listing all approved chapters as spine items.
    4.  **Content Generation:**
        *   Convert the Markdown Script of each chapter into simple HTML.
        *   Format:
            ```html
            <h1>Chapter X</h1>
            <div class="page">
              <h4>PAGE 01 (Left)</h4>
              <div class="panel">
                 <p><strong>PANEL 1</strong> | ...</p>
                 <p><strong>Visual:</strong> ...</p>
                 ...
              </div>
            </div>
            ```
    5.  Download the `.epub` file.
