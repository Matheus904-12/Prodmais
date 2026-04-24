<?php

use Smalot\PdfParser\Parser;

class PdfParser
{
    public function parse(string $filePath): array
    {
        $parser = new Parser();
        try {
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();
            
            // DEBUG: Retornando dados simples para teste
            return [
                [
                    'id'    => uniqid('pdf_debug_'),
                    'title' => 'TEXTO BRUTO DO PDF: ' . $text, // Adiciona um prefixo para ser óbvio
                    'researcher_name' => 'DEBUG TESTE PDF', // Nome do pesquisador para teste
                    'year' => 2025,
                    'type' => 'PDF Teste',
                    'doi' => ''
                ]
            ];
        } catch (\Exception $e) {
            error_log("Could not parse PDF: " . $e->getMessage());
            return [];
        }
    }
}
