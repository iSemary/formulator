<?php

namespace App\Controller;

use App\Entity\Form;
use Doctrine\ORM\EntityManagerInterface;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FormController extends AbstractController {
    private $entityManager;
    private $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security) {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    #[Route('dashboard/forms', name: 'app_forms')]
    public function index(Request $request, DataTableFactory $dataTableFactory): Response {
        if ($request->isXmlHttpRequest()) {
            // return forms as dataTable
            $userId = $this->security->getUser()->getUserIdentifier();
            $forms = $this->entityManager->getRepository(Form::class)->findByUserId($userId);
            $table = $dataTableFactory->create()
                ->add('id', TextColumn::class)
                ->add('title', TextColumn::class)
                ->add('created_at', TextColumn::class)
                ->createAdapter(ORMAdapter::class, [
                    'entity' => Form::class,
                ])
                ->handleRequest($request);

            if ($table->isCallback()) {
                return $table->getResponse();
            }
        }
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
