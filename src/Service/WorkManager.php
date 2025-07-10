<?php

namespace App\Service;

use App\Entity\Work;
use DateTime;

class WorkManager
{
    /**
     * Applique la logique métier sur un objet Work (jours fériés, congés, etc.)
     */
    public function applyBusinessRules(Work $work): void
    {
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
    }
}
