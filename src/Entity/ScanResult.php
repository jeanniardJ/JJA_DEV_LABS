<?php

namespace App\Entity;

use App\Repository\ScanResultRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScanResultRepository::class)]
class ScanResult
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $scanId = null;

    #[ORM\Column(length: 255)]
    private ?string $url = null;

    #[ORM\Column(length: 50)]
    private string $status = 'pending';

    /** @var array<int, array<string, mixed>>|null */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $rawOutput = null;

    #[ORM\Column(nullable: true)]
    private ?int $duration = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $errorMessage = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScanId(): ?string
    {
        return $this->scanId;
    }

    public function setScanId(string $scanId): static
    {
        $this->scanId = $scanId;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    /** @return array<int, array<string, mixed>>|null */
    public function getRawOutput(): ?array
    {
        return $this->rawOutput;
    }

    /** @param array<int, array<string, mixed>>|null $rawOutput */
    public function setRawOutput(?array $rawOutput): static
    {
        $this->rawOutput = $rawOutput;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): static
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getMaxSeverity(): string
    {
        if (empty($this->rawOutput)) {
            return 'none';
        }

        $levels = [
            'critical' => 5,
            'high' => 4,
            'medium' => 3,
            'low' => 2,
            'info' => 1,
        ];

        $maxLevel = 0;
        $maxSeverity = 'info';

        foreach ($this->rawOutput as $finding) {
            $severity = strtolower($finding['info']['severity'] ?? 'info');
            $level = $levels[$severity] ?? 0;

            if ($level > $maxLevel) {
                $maxLevel = $level;
                $maxSeverity = $severity;
            }
        }

        return $maxLevel > 0 ? $maxSeverity : 'none';
    }
}
