<?php

namespace App\Controller;

use App\Entity\Transport;
use App\Form\TransportType;
use App\Repository\TransportRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Flex\Options;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
final class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin')]
    public function index(
        UserRepository $userRepository,
        TransportRepository $transportRepository
    ): Response {
        $users = $userRepository->findAll();

        $transport = $transportRepository->findOneBy([], ['id' => 'DESC']);
        $transportValue = $transport ? $transport->getValue() : 0;

        return $this->render('admin/index.html.twig', [
            'users' => $users,
            'transportValue' => $transportValue,
        ]);
    }

    #[Route('/edit/transport/', name: 'app_admin_edit_transport')]
    public function editTransport(
        TransportRepository $transportRepository,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        $transport = $transportRepository->findOneBy([], ['id' => 'DESC']);

        if (!$transport) {
            $transport = new Transport();
            $transport->setValue(0);
            $entityManager->persist($transport);
        }

        $form = $this->createForm(TransportType::class, $transport);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Transport modifié avec succès !');

            return $this->redirectToRoute('app_admin');
        }

        return $this->render('admin/edit_transport.html.twig', [
            'form' => $form->createView(),
            'transport' => $transport,
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

    #[Route('/admin/export-week/{userId}/{year}/{week}', name: 'app_admin_export_week')]
    public function exportWeek(
        int $userId,
        int $year,
        int $week,
        UserRepository $userRepository,
        TransportRepository $transportRepository
    ): Response {
        $user = $userRepository->find($userId);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        // Récupérer la valeur du transport actuelle
        $transport = $transportRepository->findOneBy([], ['id' => 'DESC']);
        $transportValue = $transport ? $transport->getValue() : 0;

        // Filtrer les entrées de travail pour la semaine et l'année données (ISO week)
        $works = $user->getWorks()->filter(function ($work) use ($year, $week) {
            return $work->getDate()->format('o') == $year  // Année ISO
                && $work->getDate()->format('W') == sprintf('%02d', $week); // Semaine ISO
        });

        // Calculer les dates de début et fin de semaine pour l'affichage
        $weekDates = [];
        foreach ($works as $work) {
            $weekDates[] = $work->getDate();
        }

        $startDate = null;
        $endDate = null;
        if (!empty($weekDates)) {
            usort($weekDates, function($a, $b) {
                return $a <=> $b;
            });
            $startDate = reset($weekDates);
            $endDate = end($weekDates);
        }

        // Rendu HTML pour le PDF
        $html = $this->renderView('admin/pdf_export_week.html.twig', [
            'user' => $user,
            'works' => $works,
            'year' => $year,
            'week' => $week,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'transportValue' => $transportValue,
        ]);

        // Configuration de Dompdf
        $options = new Options();
        $options->get('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Génération d'un nom de fichier propre
        $firstName = strtolower(str_replace(' ', '-', $user->getFirstName()));
        $lastName = strtolower(str_replace(' ', '-', $user->getLastName()));
        $filename = "heures-{$firstName}-{$lastName}-semaine-" . sprintf('%02d', $week) . "-{$year}.pdf";

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
