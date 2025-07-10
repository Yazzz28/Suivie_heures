<?php

namespace App\Tests\Service;

use App\Entity\Work;
use App\Service\WorkManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class WorkManagerIntegrationTest extends KernelTestCase
{
    public function testApplyBusinessRulesPersistsWork()
    {
        self::bootKernel();
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $manager = $container->get(WorkManager::class);

        $user = new \App\Entity\User();
        $user->setEmail('work-integration@example.com');
        $user->setFirstName('Work');
        $user->setLastName('Integration');
        $user->setPassword('dummy');
        $em->persist($user);
        $em->flush();

        $work = new Work();
        $work->setDayOf(true);
        $work->setDate(new \DateTime());
        $work->setUser($user);
        $manager->applyBusinessRules($work);
        $em->persist($work);
        $em->flush();

        $found = $em->getRepository(Work::class)->find($work->getId());
        $this->assertNotNull($found);
        $this->assertEquals('09:00', $found->getStartTime()?->format('H:i'));

        // Nettoyage
        $em->remove($found);
        $em->remove($user);
        $em->flush();
    }
}
