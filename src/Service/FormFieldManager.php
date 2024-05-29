<?php

namespace App\Service;

use App\Entity\FieldOption;
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

            // Add field options for single options element and multiple options element
            if (in_array($field['type'], [3, 4])) {
                $this->createFieldOptions($formField->getId(), $field['options']);
            }

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
                    // Add field options for single options element and multiple options element
                    if (in_array($field['type'], [3, 4])) {
                        $this->createFieldOptions($existingField->getId(), $field['options']);
                    }
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

    public function createFieldOptions(int $fieldId, array $options) {
        // Clear existing options for the field
        $existingOptions = $this->entityManager->getRepository(FieldOption::class)->findBy(['field_id' => $fieldId]);
        foreach ($existingOptions as $existingOption) {
            $existingOption->setStatus(0); // Mark as inactive
            $existingOption->setUpdatedAt(now());
            $this->entityManager->persist($existingOption);
        }
        $this->entityManager->flush();

        // Add new options if they don't exist in the existing options
        foreach ($options as $option) {
            // Check if the option already exists
            $existingOption = $this->entityManager->getRepository(FieldOption::class)->findOneBy(['field_id' => $fieldId, 'option_value' => $option]);
            if (!$existingOption) {
                // If the option doesn't exist, create a new one
                $fieldOption = new FieldOption();
                $fieldOption->setFieldId($fieldId);
                $fieldOption->setOptionValue($option);
                $fieldOption->setStatus(1); // Mark as active
                $fieldOption->setCreatedAt(now());
                $fieldOption->setUpdatedAt(now());
                $this->entityManager->persist($fieldOption);
            } else {
                // If the option already exists, just activate it
                $existingOption->setStatus(1); // Mark as active
                $existingOption->setUpdatedAt(now());
                $this->entityManager->persist($existingOption);
            }
        }

        $this->entityManager->flush(); // Commit changes to the database
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
