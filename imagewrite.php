<?php
    require_once('vendor/autoload.php');

    function generatePDF($source, $output, $text, $image) 
    {
        $pdf = new FPDI('Portrait','mm',array(215.9,279.4)); // Array sets the X, Y dimensions in mm
        $pdf->AddPage();
        $pagecount = $pdf->setSourceFile($source);
        $tppl = $pdf->importPage(1);

        $pdf->useTemplate($tppl, 0, 0, 0, 0);
 
        $pdf->Image($image,10,10,50,50); // X start, Y start, X width, Y width in mm
        
        $pdf->SetFont('Helvetica','',10); // Font Name, Font Style (eg. 'B' for Bold), Font Size
        $pdf->SetTextColor(0,0,0); // RGB 
        $pdf->SetXY(51.5, 57); // X start, Y start in mm
        $pdf->Write(0, $text);
        
        $pdf->Output($output, "F");
    }

    generatePDF("pdf/contoh.pdf", "pdf/export.pdf", "Hello world", "image/10 Besar.jpg");
?>