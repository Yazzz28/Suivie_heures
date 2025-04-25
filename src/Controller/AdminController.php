<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Flex\Options;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
final class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin')]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->render('admin/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/export/{userId}/{year}/{month}', name: 'app_admin_export')]
    public function export(
        int $userId,
        int $year,
        int $month,
        UserRepository $userRepository
    ): Response {
        $user = $userRepository->find($userId);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        // Filtrer les entrées de travail pour le mois et l'année donnés
        $works = $user->getWorks()->filter(function ($work) use ($year, $month) {
            return $work->getDate()->format('Y') == $year
                && $work->getDate()->format('m') == sprintf('%02d', $month);
        });

        // Rendu HTML pour le PDF
        $html = $this->renderView('admin/pdf_export.html.twig', [
            'user' => $user,
            'works' => $works,
            'year' => $year,
            'month' => $month,
        ]);

        // Configuration de Dompdf
        $options = new Options();
        $options->get('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Génération d’un nom de fichier propre
        $firstName = strtolower(str_replace(' ', '-', $user->getFirstName()));
        $lastName = strtolower(str_replace(' ', '-', $user->getLastName()));
        $filename = "heures-{$firstName}-{$lastName}-" . sprintf('%02d', $month) . "-{$year}.pdf";

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
            ]
        );
    }

    #[Route('/admin/delete/{userId}', name: 'app_admin_delete')]
    public function deleteUser(
        int $userId,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $userRepository->find($userId);
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin');
    }
}
