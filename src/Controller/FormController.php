<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FormController extends AbstractController {
    #[Route('dashboard/forms', name: 'app_forms')]
    public function index(): Response {
        return $this->render('dashboard/forms/index.html.twig', []);
    }

    #[Route('dashboard/forms/create', name: 'app_forms_create')]
    public function create(): Response {
        return $this->render('dashboard/forms/create.html.twig', []);
    }

    #[Route('dashboard/forms/edit/{id}', name: 'app_forms_edit')]
    public function edit($id): Response {
        return $this->render('dashboard/forms/edit.html.twig', []);
    }


    #[Route('forms/{id}', name: 'app_forms_show')]
    public function show($id): Response {
        return $this->render('forms/index.html.twig', []);
    }
}
