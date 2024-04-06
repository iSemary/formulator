<?php

namespace App\Service;

use App\Entity\Form;
use Symfony\Component\HttpFoundation\JsonResponse;

interface FormFieldManagerInterface {
    public function create(int $formId, array $fields): JsonResponse;
    public function update(int $formId, array $fields): JsonResponse;
    public function delete(Form $form): JsonResponse;
}
