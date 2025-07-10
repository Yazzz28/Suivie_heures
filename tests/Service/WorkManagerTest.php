<?php

namespace App\Tests\Service;

use App\Entity\Work;
use App\Service\WorkManager;
use PHPUnit\Framework\TestCase;

class WorkManagerTest extends TestCase
{
    public function testApplyBusinessRulesForDayOff()
    {
        $work = new Work();
        $work->setDayOf(true);
        $manager = new WorkManager();
        $manager->applyBusinessRules($work);
        $this->assertEquals('09:00', $work->getStartTime()?->format('H:i'));
        $this->assertEquals('17:00', $work->getEndTime()?->format('H:i'));
        $this->assertEquals('12:00', $work->getPauseStart()?->format('H:i'));
        $this->assertEquals('13:00', $work->getPauseEnd()?->format('H:i'));
    }

    public function testApplyBusinessRulesForDayOfWhitoutSolde()
    {
        $work = new Work();
        $work->setDayOfWhitoutSolde(true);
        $manager = new WorkManager();
        $manager->applyBusinessRules($work);
        $this->assertNull($work->getStartTime());
        $this->assertNull($work->getEndTime());
        $this->assertNull($work->getPauseStart());
        $this->assertNull($work->getPauseEnd());
        $this->assertEquals('00:00:00', $work->getDuree()?->format('H:i:s'));
    }
}
