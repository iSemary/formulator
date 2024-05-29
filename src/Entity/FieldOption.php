<?php

namespace App\Entity;

use App\Repository\FieldOptionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FieldOptionRepository::class)]
class FieldOption {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $field_id = null;

    #[ORM\Column(length: 255)]
    private ?string $option_value = null;

    #[ORM\Column]
    private ?int $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    public function getId(): ?int {
        return $this->id;
    }

    public function getFieldId(): ?int {
        return $this->field_id;
    }

    public function setFieldId(int $field_id): static {
        $this->field_id = $field_id;

        return $this;
    }

    public function getOptionValue(): ?string {
        return $this->option_value;
    }

    public function setOptionValue(string $option_value): static {
        $this->option_value = $option_value;

        return $this;
    }

    public function getStatus(): ?int {
        return $this->status;
    }

    public function setStatus(int $status): static {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static {
        $this->updated_at = $updated_at;

        return $this;
    }
}
