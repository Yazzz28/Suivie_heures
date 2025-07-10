<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\ResetPasswordService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class ResetPasswordServiceTest extends TestCase
{
    public function testGenerateTokenReturnsUuidString()
    {
        $service = new ResetPasswordService(
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(MailerInterface::class),
            $this->createMock(UrlGeneratorInterface::class),
            $this->createMock(Environment::class)
        );
        $token = $service->generateToken();
        $this->assertIsString($token);
        $this->assertMatchesRegularExpression('/^[0-9a-f\-]{36}$/', $token);
    }
}
