<?php

namespace App\Entity;

use App\Repository\SessionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
class Session {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $ip = null;

    #[ORM\Column]
    private ?int $formId = null;

    #[ORM\Column(length: 255)]
    private ?string $agent = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    public function getId(): ?int {
        return $this->id;
    }

    public function getFormId(): ?int {
        return $this->formId;
    }

    public function setFormId(int $formId): static {
        $this->formId = $formId;

        return $this;
    }

    public function getIp(): ?string {
        return $this->ip;
    }

    public function setIp(string $ip): static {
        $this->ip = $ip;

        return $this;
    }

    public function getAgent(): ?string {
        return $this->agent;
    }

    public function setAgent(string $agent): static {
        $this->agent = $agent;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static {
        $this->created_at = $created_at;

        return $this;
    }
}
