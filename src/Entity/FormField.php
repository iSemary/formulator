<?php

namespace App\Entity;

use App\Repository\FormFieldRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormFieldRepository::class)]
class FormField {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $form_id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 1024)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $type = null;

    #[ORM\Column]
    private ?int $required = null;

    #[ORM\Column]
    private ?int $order_number = null;

    #[ORM\Column]
    private ?int $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    public function getId(): ?int {
        return $this->id;
    }

    public function getFormId(): ?int {
        return $this->form_id;
    }

    public function setFormId(int $form_id): static {
        $this->form_id = $form_id;

        return $this;
    }

    public function getTitle(): ?string {
        return $this->title;
    }

    public function setTitle(string $title): static {
        $this->title = $title;

        return $this;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function setName(string $name): static {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(string $description): static {
        $this->description = $description;

        return $this;
    }

    public function getType(): ?int {
        return $this->type;
    }

    public function setType(int $type): static {
        $this->type = $type;

        return $this;
    }

    public function getRequired(): ?int {
        return $this->required;
    }

    public function setRequired(int $required): static {
        $this->required = $required;

        return $this;
    }

    public function getOrderNumber(): ?int {
        return $this->order_number;
    }

    public function setOrderNumber(int $order_number): static {
        $this->order_number = $order_number;

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
