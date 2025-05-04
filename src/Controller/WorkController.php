<?php

namespace App\Controller;

use App\Entity\Work;
use App\Form\WorkType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WorkController extends AbstractController
{
    #[Route('/', name: 'app_work_index')]
    public function redirectUser(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        return $this->redirectToRoute('app_work', ['id' => $this->getUser()->getId()]);
    }

    #[Route('/{id}', name: 'app_work')]
    public function index(EntityManagerInterface $em): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        $works = $em->getRepository(Work::class)->findBy(
            ['user' => $this->getUser()],
            ['date' => 'ASC']
        );
        return $this->render('work/index.html.twig', [
            'user' => $this->getUser(),
            'works' => $works,
        ]);
    }

    #[Route('/{id}/add', name: 'app_work_add')]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        $this->denyAccessUnlessGranted('ROLE_USER');

        $work = new Work();
        $form = $this->createForm(WorkType::class, $work);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $work->setUser($this->getUser());
            if ($work->getDayOf() || $work->getIsPublicHolidays()) {
                $work->setStartTime(new DateTime('09:00'));
                $work->setEndTime(new DateTime('17:00'));
                $work->setPauseStart(new DateTime('12:00'));
                $work->setPauseEnd(new DateTime('13:00'));
            }
            if ($work->getDayOfWhitoutSolde()) {
                $work->setStartTime(null);
                $work->setEndTime(null);
                $work->setPauseStart(null);
                $work->setPauseEnd(null);
                $work->setDuree(new DateTime('00:00:00'));
            }
            $em->persist($work);
            $em->flush();

            $this->addFlash('success', 'Heure enregistrée avec succès !');
            return $this->redirectToRoute('app_work', ['id' => $this->getUser()->getId()]);
        }

        return $this->render('work/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit/{work}', name: 'app_work_edit')]
    public function edit(Request $request, Work $work, EntityManagerInterface $em): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        $this->denyAccessUnlessGranted('ROLE_USER');

        $form = $this->createForm(WorkType::class, $work);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($work->getDayOf() || $work->getIsPublicHolidays()) {
                $work->setStartTime(new DateTime('09:00'));
                $work->setEndTime(new DateTime('17:00'));
                $work->setPauseStart(new DateTime('12:00'));
                $work->setPauseEnd(new DateTime('13:00'));
            }
            if ($work->getDayOfWhitoutSolde()) {
                $work->setStartTime(null);
                $work->setEndTime(null);
                $work->setPauseStart(null);
                $work->setPauseEnd(null);
                $work->setDuree(new DateTime('00:00:00'));
            }
            $em->flush();

            $this->addFlash('success', 'Heure modifiée avec succès !');
            return $this->redirectToRoute('app_work', ['id' => $this->getUser()->getId()]);
        }

        return $this->render('work/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete/{work}', name: 'app_work_delete')]
    public function delete(Request $request, Work $work, EntityManagerInterface $em): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($this->isCsrfTokenValid('delete'.$work->getId(), $request->request->get('_token'))) {
            $em->remove($work);
            $em->flush();

            $this->addFlash('success', 'Heure supprimée avec succès !');
        }

        return $this->redirectToRoute('app_work', ['id' => $this->getUser()->getId()]);
    }
}
