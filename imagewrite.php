<?php
    require_once('vendor/autoload.php');

    function generatePDF($source, $output, $text, $image, $pos_arr, $width, $height, $page_no) 
    {
        $pdf = new FPDI('Portrait','mm',array($width,$height)); // Array sets the X, Y dimensions in mm
        $pdf->AddPage();
        $pagecount = $pdf->setSourceFile($source);
        $tppl = $pdf->importPage($page_no);

        $dimen = $pdf->useTemplate($tppl, 0, 0, $width, $height, true);

        $x_start = $pos_arr[0];
        $y_start = $pos_arr[1];
        $x_width = $pos_arr[2] - $pos_arr[0];
        $y_width = $pos_arr[3] - $pos_arr[1];
 
        $pdf->Image($image, $x_start, $y_start, $x_width, $y_width); // X start, Y start, X width, Y width in mm
        
        $pdf->SetFont('Helvetica','',10); // Font Name, Font Style (eg. 'B' for Bold), Font Size
        $pdf->SetTextColor(0,0,0); // RGB 
        $pdf->SetXY(51.5, 57); // X start, Y start in mm
        $pdf->Write(0, $text);
        
        $pdf->Output($output, "F");
    }

    $pos_trans = $_POST['pos_trans'];
    $width = $_POST['pg_width'];
    $height = $_POST['pg_height'];
    $curr_page = $_POST['curr_page'];

    generatePDF("pdf/contoh.pdf", "pdf/export.pdf", "Hello world", "image/10 Besar.jpg", $pos_trans, $width, $height, $curr_page);
?>