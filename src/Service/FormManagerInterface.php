<?php

namespace App\Service;

use App\Entity\Form;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

interface FormManagerInterface {
    public function createForm(Request $request): Form;
    public function updateForm(int $id, Request $request): Form;
    public function deleteForm(int $id): JsonResponse;
    public function restoreForm(int $id): JsonResponse;
    public function getAllForms(Request $request, DataTableFactory $dataTableFactory);
}
