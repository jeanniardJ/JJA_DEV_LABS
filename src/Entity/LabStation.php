<?php

namespace App\Entity;

use App\Enum\StationStatus;
use App\Repository\LabStationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LabStationRepository::class)]
class LabStation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    private ?string $icon = null; // Lucide icon name

    #[ORM\Column(length: 20, enumType: StationStatus::class)]
    private StationStatus $status = StationStatus::NOMINAL;

    #[ORM\Column(length: 7)]
    private ?string $borderColor = '#00c6ff'; // Hex color

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $uptime = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $metricLabel = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $metricValue = null;

    #[ORM\Column]
    private int $position = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): static
    {
        $this->icon = $icon;
        return $this;
    }

    public function getStatus(): StationStatus
    {
        return $this->status;
    }

    public function setStatus(StationStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getBorderColor(): ?string
    {
        return $this->borderColor;
    }

    public function setBorderColor(string $borderColor): static
    {
        $this->borderColor = $borderColor;
        return $this;
    }

    public function getUptime(): ?string
    {
        return $this->uptime;
    }

    public function setUptime(?string $uptime): static
    {
        $this->uptime = $uptime;
        return $this;
    }

    public function getMetricLabel(): ?string
    {
        return $this->metricLabel;
    }

    public function setMetricLabel(?string $metricLabel): static
    {
        $this->metricLabel = $metricLabel;
        return $this;
    }

    public function getMetricValue(): ?string
    {
        return $this->metricValue;
    }

    public function setMetricValue(?string $metricValue): static
    {
        $this->metricValue = $metricValue;
        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;
        return $this;
    }
}
