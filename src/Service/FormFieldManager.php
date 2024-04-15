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

    public function create(int $formId, array $fields, int $orderNumber = 0): JsonResponse {
        foreach ($fields as $key => $field) {
            $formField = new FormField();
            $formField->setFormId($formId);
            $formField->setTitle($field['title']);
            $formField->setDescription($field['description'] ?? "");
            $formField->setType($field['type']);
            $formField->setName("field_" . $key);
            $formField->setRequired($field['required'] ? 1 : 0);
            $formField->setOrderNumber($orderNumber);
            $formField->setStatus(1);
            $formField->setCreatedAt(now());
            $formField->setUpdatedAt(now());
            $this->entityManager->persist($formField);
            $this->entityManager->flush();
            $orderNumber++;
        }
        return new JsonResponse(['success' => true], 200);
    }

    public function update(int $formId, array $fields): JsonResponse {
        $orderNumber = 0;
        foreach ($fields as $key => $field) {
            if (!$field['deleted']) {
                // update or create fields
                $existingField = $this->entityManager->getRepository(FormField::class)->findOneByFormIdAndId($formId, $field['id']);
                if ($existingField) {
                    $existingField->setTitle($field['title']);
                    $existingField->setDescription($field['description'] ?? "");
                    $existingField->setRequired($field['required'] ? 1 : 0);
                    $existingField->setOrderNumber($orderNumber);
                    $existingField->setUpdatedAt(now());
                    $this->entityManager->persist($existingField);
                    $this->entityManager->flush();
                } else {
                    $this->create($formId, [$field], $orderNumber);
                }
                $orderNumber++;
            } else {
                // delete fields
                $this->delete($formId, $field['id']);
            }
        }
        return new JsonResponse(['success' => true], 200);
    }

    public function delete(int $formId, int $fieldId): JsonResponse {
        $formField = $this->entityManager->getRepository(FormField::class)->findOneById($fieldId);
        if ($formField) {
            $formField->setStatus(0);
            $this->entityManager->persist($formField);
            $this->entityManager->flush();
            return new JsonResponse(['success' => true], 200);
        }
        return new JsonResponse(['success' => true], 200);
    }
}
