<?php

namespace App\Service;

use App\Entity\FormResults;
use App\Entity\Session;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;

class ResultManager implements ResultManagerInterface {
    private $security;
    private $userId;
    private $entityManager;
    private $formSubmission;

    public function __construct(EntityManagerInterface $entityManager, Security $security,  FormSubmission $formSubmission) {
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->formSubmission = $formSubmission;
        $this->userId = $this->security->getUser() ? $this->security->getUser()->getUserIdentifier() : null;
    }


    public function getAllResults(Request $request, DataTableFactory $dataTableFactory, $formId = null) {
        $table = $dataTableFactory->create()
            ->add('id', TextColumn::class, ['label' => "ID"])
            ->add('title', TextColumn::class, ['label' => "Form Title", 'render' => function ($value, $row) {
                return $row['title'];
            }])
            ->add('created_at', DateTimeColumn::class, ['label' => "Created At"])
            ->add('actions', TextColumn::class, ['label' => "Actions", 'render' => function ($value, $row) {
                $buttons = sprintf('<button type="button" data-url="/dashboard/results/%u" class="btn btn-sm btn-primary me-1 view-details">Details</button>', $row['id']);
                return $buttons;
            }])
            ->createAdapter(ORMAdapter::class, [
                'hydrate' => \Doctrine\ORM\Query::HYDRATE_ARRAY,
                'entity' => Session::class,
                'query' => function (QueryBuilder $builder) use ($formId) {
                    $builder
                        ->select('session.id, session.created_at, form.title')
                        ->from(Session::class, 'session')
                        ->join('App\Entity\Form', 'form', 'WITH', 'session.formId = form.id')
                        ->where("form.userId = :userId")
                        ->setParameter("userId", $this->userId);

                    if ($formId != null) {
                        $builder
                            ->andWhere('session.formId = :formId')
                            ->setParameter("formId", $formId);
                    }

                    $builder
                        ->groupBy('session.id')
                        ->orderBy('session.id', 'DESC');
                },
            ])
            ->handleRequest($request);

        return $table;
    }

    public function getSessionResults(int $sessionId): array {
        $results = $this->entityManager->getRepository(FormResults::class)->findAllBySessionId($sessionId);
        foreach ($results as $key => $result) {
            if ($result['question_type'] == 5) {
                $results[$key]['answer'] = '<a href="' . $this->formSubmission->urlPath . '/' . $result['answer'] . '" target="_blank" download>Download File</a>';
            }
        }
        return $results;
    }
}
