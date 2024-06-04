<?php

namespace App\Service;

use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Component\HttpFoundation\Request;

interface ResultManagerInterface {
    public function getAllResults(Request $request, DataTableFactory $dataTableFactory);
    public function getSessionResults(int $sessionId): array;
}
