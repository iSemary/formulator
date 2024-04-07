<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;

interface FormFieldManagerInterface {
    public function create(int $formId, array $fields): JsonResponse;
    public function update(int $formId, array $fields): JsonResponse;
    public function delete(int $formId, int $fieldId): JsonResponse;
}
