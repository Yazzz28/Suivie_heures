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
use App\Service\WorkManager;

final class WorkController extends AbstractController
{
    #[Route('/', name: 'app_work_index')]
    public function redirectUser(): Response
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            return $this->redirectToRoute('app_login');
        }
        return $this->redirectToRoute('app_work', ['id' => $user->getId()]);
    }

    #[Route('/{id}', name: 'app_work')]
    public function index(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            return $this->redirectToRoute('app_login');
        }
        $works = $em->getRepository(Work::class)->findBy(
            ['user' => $user],
            ['date' => 'ASC']
        );
        return $this->render('work/index.html.twig', [
            'user' => $user,
            'works' => $works,
        ]);
    }

    #[Route('/{id}/add', name: 'app_work_add')]
    public function add(Request $request, EntityManagerInterface $em, WorkManager $workManager): Response
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            return $this->redirectToRoute('app_login');
        }
        $this->denyAccessUnlessGranted('ROLE_USER');

        $work = new Work();
        $form = $this->createForm(WorkType::class, $work);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $work->setUser($user);
            $workManager->applyBusinessRules($work);
            $em->persist($work);
            $em->flush();

            $this->addFlash('success', 'Heure enregistrée avec succès !');
            return $this->redirectToRoute('app_work', ['id' => $user->getId()]);
        }

        return $this->render('work/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit/{work}', name: 'app_work_edit')]
    public function edit(Request $request, Work $work, EntityManagerInterface $em, WorkManager $workManager): Response
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            return $this->redirectToRoute('app_login');
        }
        $this->denyAccessUnlessGranted('ROLE_USER');

        $form = $this->createForm(WorkType::class, $work);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $workManager->applyBusinessRules($work);
            $em->flush();

            $this->addFlash('success', 'Heure modifiée avec succès !');
            return $this->redirectToRoute('app_work', ['id' => $user->getId()]);
        }

        return $this->render('work/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete/{work}', name: 'app_work_delete')]
    public function delete(Request $request, Work $work, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            return $this->redirectToRoute('app_login');
        }
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($this->isCsrfTokenValid('delete' . $work->getId(), $request->request->get('_token'))) {
            $em->remove($work);
            $em->flush();

            $this->addFlash('success', 'Heure supprimée avec succès !');
        }

        return $this->redirectToRoute('app_work', ['id' => $user->getId()]);
    }
}
