<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\RegistrationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationServiceTest extends TestCase
{
    public function testRegisterUserPersistsUser()
    {
        $user = new User();
        $plainPassword = 'test1234';
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->method('hashPassword')->willReturn('hashed');
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist')->with($user);
        $em->expects($this->once())->method('flush');
        $service = new RegistrationService();
        $service->registerUser($user, $plainPassword, $passwordHasher, $em);
        $this->assertEquals('hashed', $user->getPassword());
    }
}
