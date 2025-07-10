<?php

namespace App\Tests\Service;

use App\Service\ExportService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ExportServiceIntegrationTest extends KernelTestCase
{
    public function testGeneratePdfWithTwigTemplate()
    {
        self::bootKernel();
        $container = static::getContainer();
        $user = new \App\Entity\User();
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setEmail('test@example.com');
        $twig = $container->get('twig');
        $service = $container->get(ExportService::class);
        $html = $twig->render('admin/pdf_export.html.twig', [
            'user' => $user,
            'works' => [],
            'year' => 2025,
            'month' => 7,
        ]);
        $dompdf = $service->generatePdf($html);
        $this->assertNotEmpty($dompdf->output());
    }
}
