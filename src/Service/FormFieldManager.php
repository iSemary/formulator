<?php

namespace App\Service;

use App\Entity\Form;
use App\Entity\FormField;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

use function Symfony\Component\Clock\now;

class FormFieldManager implements FormFieldManagerInterface {
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    public function create(int $formId, array $fields): JsonResponse {
        $orderNumber = 0;
        foreach ($fields as $key => $field) {
            $formField = new FormField();
            $formField->setFormId($formId);
            $formField->setTitle($field['title']);
            $formField->setDescription($field['description'] ?? "");
            $formField->setType($field['type']);
            $formField->setName("field_" . $key);
            $formField->setRequired($field['required'] ? 1 : 0);
            $formField->setOrderNumber($orderNumber);
            $formField->setCreatedAt(now());
            $formField->setUpdatedAt(now());
            $this->entityManager->persist($formField);
            $this->entityManager->flush();
            $orderNumber++;
        }
        return new JsonResponse(['success' => true], 200);
    }

    public function update(int $formId, array $fields): JsonResponse {

        return new JsonResponse(['success' => true], 200);
    }
    public function delete(Form $form): JsonResponse {
        return new JsonResponse(['success' => true], 200);
    }
}
