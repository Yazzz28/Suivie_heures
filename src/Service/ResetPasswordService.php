<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;
use Twig\Environment;

class ResetPasswordService
{
    public function __construct(
        private EntityManagerInterface $em,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private Environment $twig
    ) {}

    public function generateToken(): string
    {
        return Uuid::v4()->toRfc4122();
    }

    public function sendResetEmail(User $user): void
    {
        $resetUrl = $this->urlGenerator->generate('app_reset_password', [
            'token' => $user->getResetToken(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $html = $this->twig->render('email/reset_password.html.twig', [
            'user' => $user,
            'resetUrl' => $resetUrl,
        ]);

        $emailMessage = (new Email())
            ->from('no-reply@populaire.com')
            ->to($user->getEmail())
            ->subject('Réinitialisation de votre mot de passe')
            ->html($html);
        $this->mailer->send($emailMessage);
    }

    public function setUserToken(User $user): void
    {
        $user->setResetToken($this->generateToken());
        $this->em->flush();
    }
}
