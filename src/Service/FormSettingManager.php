<?php

namespace App\Service;

use App\Entity\Form;
use App\Entity\FormSetting;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

use function Symfony\Component\Clock\now;

class FormSettingManager implements FormSettingManagerInterface {
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    /**
     * The function creates form settings for a given form ID based on the provided settings array.
     * 
     * @param int formId The `formId` parameter is an integer that represents the ID of the form for which
     * you want to create settings.
     * @param array settings The `create` function takes in two parameters: `` of type integer and
     * `` which is an array. The function loops through each setting in the `` array,
     * creates a new `FormSetting` entity, sets its properties using the values from the array, and then
     * 
     * @return JsonResponse A `JsonResponse` object with a success message indicating that the settings
     * have been successfully created.
     */
    public function create(int $formId, array $settings): JsonResponse {
        foreach ($settings as $key => $setting) {
            if (!empty($setting)) {
                $formSetting = new FormSetting();
                $formSetting->setFormId($formId);
                $formSetting->setSettingKey($key);
                $formSetting->setSettingValue($setting);
                $formSetting->setCreatedAt(now());
                $formSetting->setUpdatedAt(now());
                $this->entityManager->persist($formSetting);
                $this->entityManager->flush();
            }
        }
        return new JsonResponse(['success' => true], 200);
    }

    public function update(int $formId, array $settings): JsonResponse {
        return new JsonResponse(['success' => true], 200);
    }

    public function delete(Form $form): JsonResponse {
        return new JsonResponse(['success' => true], 200);
    }
}
