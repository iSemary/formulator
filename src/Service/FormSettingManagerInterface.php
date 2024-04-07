<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;

interface FormSettingManagerInterface {
    public function create(int $formId, array $settings): JsonResponse;
    public function update(int $formId, array $settings): JsonResponse;
    public function delete(int $formId, string $key): JsonResponse;
}
