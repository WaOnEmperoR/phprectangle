<?php
    require_once('vendor/autoload.php');

    function generatePDF($source, $output, $text, $image) 
    {
        $pdf = new FPDI('Portrait','mm',array(612,792)); // Array sets the X, Y dimensions in mm
        // $pdf = new FPDI();
        $pdf->AddPage();
        $pagecount = $pdf->setSourceFile($source);
        $tppl = $pdf->importPage(1);

        $dimen = $pdf->useTemplate($tppl, 0, 0, 612, 792, true);
        print_r($dimen);
 
        $pdf->Image($image,0,0, 100,100); // X start, Y start, X width, Y width in mm
        
        $pdf->SetFont('Helvetica','',10); // Font Name, Font Style (eg. 'B' for Bold), Font Size
        $pdf->SetTextColor(0,0,0); // RGB 
        $pdf->SetXY(51.5, 57); // X start, Y start in mm
        $pdf->Write(0, $text);
        
        $pdf->Output($output, "F");
    }

    print_r($_POST['position']);

    generatePDF("pdf/contoh.pdf", "pdf/export.pdf", "Hello world", "image/10 Besar.jpg");
?>