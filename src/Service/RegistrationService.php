<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationService
{
    public function registerUser(User $user, string $plainPassword, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): void
    {
        $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
        $entityManager->persist($user);
        $entityManager->flush();
    }
}
