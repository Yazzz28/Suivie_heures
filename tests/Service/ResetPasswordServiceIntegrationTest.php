<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\ResetPasswordService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ResetPasswordServiceIntegrationTest extends KernelTestCase
{
    public function testSetUserTokenPersistsToken()
    {
        self::bootKernel();
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $service = $container->get(ResetPasswordService::class);

        $user = new User();
        $user->setEmail('reset-integration@example.com');
        $user->setFirstName('Reset');
        $user->setLastName('Integration');
        $user->setPassword('dummy');
        $em->persist($user);
        $em->flush();

        $service->setUserToken($user);
        $this->assertNotNull($user->getResetToken());

        // Nettoyage
        $em->remove($user);
        $em->flush();
    }
}
