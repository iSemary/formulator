<?php

namespace App\Controller;

use App\Service\FormService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController {
    private $formService;
    public function __construct(FormService $formService) {
        $this->formService = $formService;
    }


    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response {
        $totalForms = $this->formService->getTotalForms();

        return $this->render('dashboard/index.html.twig', [
            'totalForms' => $totalForms,
            'leads' => 'DashboardController',
        ]);
    }
}
