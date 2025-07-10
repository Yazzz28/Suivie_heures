<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\RegistrationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationServiceIntegrationTest extends KernelTestCase
{
    public function testRegisterUserPersistsUserInDatabase()
    {
        self::bootKernel();
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);
        $service = $container->get(RegistrationService::class);

        $user = new User();
        $user->setEmail('integration@example.com');
        $user->setFirstName('Integration');
        $user->setLastName('Test');
        $plainPassword = 'integration123';

        $service->registerUser($user, $plainPassword, $passwordHasher, $em);

        $found = $em->getRepository(User::class)->findOneBy(['email' => 'integration@example.com']);
        $this->assertNotNull($found);
        $this->assertTrue($passwordHasher->isPasswordValid($found, $plainPassword));

        // Nettoyage
        $em->remove($found);
        $em->flush();
    }
}
