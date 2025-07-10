<?php

namespace App\Tests\Service;

use App\Service\ExportService;
use Dompdf\Dompdf;
use PHPUnit\Framework\TestCase;

class ExportServiceTest extends TestCase
{
    public function testGeneratePdfReturnsDompdfInstance()
    {
        $service = new ExportService();
        $html = '<html><body>Test PDF</body></html>';
        $dompdf = $service->generatePdf($html);
        $this->assertInstanceOf(Dompdf::class, $dompdf);
    }
}
