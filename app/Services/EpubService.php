<?php

namespace App\Services;

use ZipArchive;

class EpubService
{
    /**
     * Extracts text content from an EPUB file.
     *
     * @param string $filePath The path to the uploaded .epub file
     * @return string The extracted text content
     */
    public function extractText($filePath)
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== TRUE) {
            throw new \Exception("Could not open EPUB file.");
        }

        // 1. Locate the OPF file from META-INF/container.xml
        $containerXml = $zip->getFromName('META-INF/container.xml');
        if (!$containerXml) {
            $zip->close();
            throw new \Exception("Invalid EPUB: Missing container.xml");
        }

        $xml = simplexml_load_string($containerXml);
        $opfPath = (string) $xml->rootfiles->rootfile['full-path'];

        // 2. Parse the OPF file to get the spine (reading order)
        $opfContent = $zip->getFromName($opfPath);
        if (!$opfContent) {
            $zip->close();
            throw new \Exception("Invalid EPUB: OPF file not found at $opfPath");
        }

        $opfXml = simplexml_load_string($opfContent);
        // Register default namespace to handle querying
        $namespaces = $opfXml->getNamespaces(true);
        // Usually the default namespace is for the package, but simplexml handling of namespaces can be tricky.
        // We will try to map the manifest items first.

        $manifest = [];
        foreach ($opfXml->manifest->item as $item) {
            $manifest[(string)$item['id']] = (string)$item['href'];
        }

        $spine = [];
        foreach ($opfXml->spine->itemref as $itemref) {
            $spine[] = (string)$itemref['idref'];
        }

        // 3. Extract text from each spine item in order
        $fullText = "";
        $opfDir = dirname($opfPath);
        if ($opfDir === '.') $opfDir = '';
        else $opfDir .= '/';

        foreach ($spine as $idref) {
            if (isset($manifest[$idref])) {
                $fileHref = $manifest[$idref];
                // Resolve path relative to OPF file
                $contentPath = $opfDir . $fileHref;

                $htmlContent = $zip->getFromName($contentPath);
                if ($htmlContent) {
                    // Simple text extraction: strip tags.
                    // Improvements: Handle <p> tags to ensure line breaks.
                    $htmlContent = preg_replace('/<br\s*\/?>/i', "\n", $htmlContent);
                    $htmlContent = preg_replace('/<\/p>/i', "\n\n", $htmlContent);
                    $text = strip_tags($htmlContent);
                    $fullText .= html_entity_decode($text) . "\n\n";
                }
            }
        }

        $zip->close();

        // Clean up excessive whitespace
        $fullText = preg_replace('/\n{3,}/', "\n\n", trim($fullText));

        return $fullText;
    }
}
