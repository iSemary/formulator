<?php

namespace App\Service;

use App\Entity\Form;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class FormService {
    private $security;
    private $entityManager;
    private $userId;

    public function __construct(
        Security $security,
        EntityManagerInterface $entityManager,
    ) {
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->userId = $this->security->getUser()->getUserIdentifier();
    }

    /**
     * @return int The `getTotalForms()` method is returning an integer value, which represents the total
     * number of forms associated with the user ID.
     */
    public function getTotalForms(): int {
        $formRepository = $this->entityManager->getRepository(Form::class);
        return $formRepository->getTotalForms($this->userId);
    }
}
