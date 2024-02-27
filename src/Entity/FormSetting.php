<?php

namespace App\Entity;

use App\Repository\FormSettingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormSettingRepository::class)]
class FormSetting {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $form_id = null;

    #[ORM\Column(length: 255)]
    private ?string $setting_key = null;

    #[ORM\Column(length: 255)]
    private ?string $setting_value = null;

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

    public function getSettingKey(): ?string {
        return $this->setting_key;
    }

    public function setSettingKey(string $setting_key): static {
        $this->setting_key = $setting_key;

        return $this;
    }

    public function getSettingValue(): ?string {
        return $this->setting_value;
    }

    public function setSettingValue(string $setting_value): static {
        $this->setting_value = $setting_value;

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
