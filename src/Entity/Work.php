<?php

namespace App\Entity;

use App\Repository\WorkRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WorkRepository::class)]
class Work
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'works')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: 'time', nullable: true)]
    private ?\DateTimeInterface $startTime = null;

    #[ORM\Column(type: 'time', nullable: true)]
    private ?\DateTimeInterface $endTime = null;

    #[ORM\Column(type: 'time', nullable: true)]
    private ?\DateTimeInterface $pauseStart = null;

    #[ORM\Column(type: 'time', nullable: true)]
    private ?\DateTimeInterface $pauseEnd = null;

    #[ORM\Column(type: 'time', nullable: true)]
    private ?DateTimeInterface $duree = null;

    #[ORM\Column( nullable: true)]
    private ?int $numberOfTransport = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $dayOf = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $dayOfWhitoutSolde = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $isPublicHolidays = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $comment = null;

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }

    public function getDate(): ?\DateTimeInterface { return $this->date; }
    public function setDate(\DateTimeInterface $date): static { $this->date = $date; return $this; }

    public function getStartTime(): ?\DateTimeInterface { return $this->startTime; }
    public function setStartTime(?\DateTimeInterface $startTime): static
    { $this->startTime = $startTime; return $this; }

    public function getEndTime(): ?\DateTimeInterface { return $this->endTime; }
    public function setEndTime(?\DateTimeInterface $endTime): static
    { $this->endTime = $endTime; return $this; }

    public function getPauseStart(): ?\DateTimeInterface { return $this->pauseStart; }
    public function setPauseStart(?\DateTimeInterface $pauseStart): static
    { $this->pauseStart = $pauseStart; return $this; }

    public function getPauseEnd(): ?\DateTimeInterface { return $this->pauseEnd; }
    public function setPauseEnd(?\DateTimeInterface $pauseEnd): static { $this->pauseEnd = $pauseEnd; return $this; }

    public function getNumberOfTransport(): ?int { return $this->numberOfTransport; }
    public function setNumberOfTransport(?int $numberOfTransport): static
    {
        $this->numberOfTransport = $numberOfTransport;
        return $this;
    }

    public function getDayOf(): ?bool
    {
        return $this->dayOf;
    }

    public function setDayOf(?bool $dayOf): void
    {
        $this->dayOf = $dayOf;
    }

    public function getDayOfWhitoutSolde(): ?bool
    {
        return $this->dayOfWhitoutSolde;
    }

    public function setDayOfWhitoutSolde(?bool $dayOfWhitoutSolde): void
    {
        $this->dayOfWhitoutSolde = $dayOfWhitoutSolde;
    }

    public function getIsPublicHolidays(): ?bool
    {
        return $this->isPublicHolidays;
    }

    public function setIsPublicHolidays(?bool $isPublicHolidays): void
    {
        $this->isPublicHolidays = $isPublicHolidays;
    }

    public function getComment(): ?string { return $this->comment; }
    public function setComment(?string $comment): static { $this->comment = $comment; return $this; }

    public function getDuree(): DateTimeInterface
    {
        return $this->duree;
    }

    public function setDuree(DateTimeInterface $duree): void
    {
        $this->duree = $duree;
    }
}
