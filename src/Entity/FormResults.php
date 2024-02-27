<?php

namespace App\Entity;

use App\Repository\FormResultsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormResultsRepository::class)]
class FormResults {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $form_id = null;

    #[ORM\Column]
    private ?int $form_field_id = null;

    #[ORM\Column]
    private ?int $session_id = null;

    #[ORM\Column(length: 4000)]
    private ?string $field_value = null;

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

    public function getFormFieldId(): ?int {
        return $this->form_field_id;
    }

    public function setFormFieldId(int $form_field_id): static {
        $this->form_field_id = $form_field_id;

        return $this;
    }

    public function getSessionId(): ?int {
        return $this->session_id;
    }

    public function setSessionId(int $session_id): static {
        $this->session_id = $session_id;

        return $this;
    }

    public function getFieldValue(): ?string {
        return $this->field_value;
    }

    public function setFieldValue(string $field_value): static {
        $this->field_value = $field_value;

        return $this;
    }
}
