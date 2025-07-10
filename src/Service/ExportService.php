<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;

class ExportService
{
    public function generatePdf(string $html): Dompdf
    {
        $options = new Options();
        $options->get('defaultFont');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return $dompdf;
    }
}
