<?php
class RC_Race_Manager_PDF {
    private $pdf;
    private $pageWidth = 210;  // A4 width in mm
    private $pageHeight = 297; // A4 height in mm
    private $margin = 15;
    private $fontSize = 12;
    private $lineHeight = 5;
    private $currentY = 0;

    public function __construct($title) {
        // Imposta l'header per il download del PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $title . '.pdf"');
        
        // Inizializza il documento PDF
        $this->pdf = ""; // Buffer per il contenuto PDF
        $this->addHeader();
        $this->currentY = $this->margin;
    }

    private function addHeader() {
        // Header PDF minimo necessario
        $this->pdf .= "%PDF-1.4\n";
        $this->pdf .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $this->pdf .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $this->pdf .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 " . 
                     $this->pageWidth * 2.83465 . " " . $this->pageHeight * 2.83465 . 
                     "] /Contents 4 0 R >>\nendobj\n";
    }

    public function addTitle($text) {
        $this->currentY += $this->lineHeight * 2;
        $content = "BT\n";
        $content .= "/F1 " . ($this->fontSize + 4) . " Tf\n";
        $content .= "50 " . (($this->pageHeight - $this->currentY) * 2.83465) . " Td\n";
        $content .= "(" . $this->escapeText($text) . ") Tj\n";
        $content .= "ET\n";
        $this->pdf .= $content;
        $this->currentY += $this->lineHeight * 2;
    }

    public function addText($text, $x = 50) {
        $this->currentY += $this->lineHeight;
        $content = "BT\n";
        $content .= "/F1 " . $this->fontSize . " Tf\n";
        $content .= $x . " " . (($this->pageHeight - $this->currentY) * 2.83465) . " Td\n";
        $content .= "(" . $this->escapeText($text) . ") Tj\n";
        $content .= "ET\n";
        $this->pdf .= $content;
    }

    public function addTable($headers, $rows) {
        $this->currentY += $this->lineHeight * 2;
        $colWidth = ($this->pageWidth - ($this->margin * 2)) / count($headers);
        
        // Headers
        $x = $this->margin;
        foreach ($headers as $header) {
            $this->addText($header, $x * 2.83465);
            $x += $colWidth;
        }
        
        $this->currentY += $this->lineHeight;
        
        // Righe
        foreach ($rows as $row) {
            $x = $this->margin;
            foreach ($row as $cell) {
                $this->addText($cell, $x * 2.83465);
                $x += $colWidth;
            }
            $this->currentY += $this->lineHeight;
        }
    }

    private function escapeText($text) {
        return str_replace(
            array('\\', '(', ')', '\n'),
            array('\\\\', '\(', '\)', '\\n'),
            $text
        );
    }

    public function output() {
        // Aggiungi il contenuto della pagina
        $this->pdf .= "4 0 obj\n<< /Length " . strlen($this->pdf) . " >>\nstream\n";
        $this->pdf .= $this->pdf;
        $this->pdf .= "\nendstream\nendobj\n";
        
        // Aggiungi il trailer
        $this->pdf .= "trailer\n<< /Size 5 /Root 1 0 R >>\n";
        $this->pdf .= "startxref\n" . strlen($this->pdf) . "\n";
        $this->pdf .= "%%EOF";
        
        echo $this->pdf;
    }
}
?>
